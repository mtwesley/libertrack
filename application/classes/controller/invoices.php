<?php

class Controller_Invoices extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('invoices')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['invoices'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    Session::instance()->write();
  }

  public function handle_invoice_create($invoice_type) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.invoice.data');

    $has_site_id    = (bool) (in_array($invoice_type, array('ST')));
    $has_specs_info = (bool) (in_array($invoice_type, array('EXF')));

    if ($has_site_id) $site_ids = DB::select('id', 'name')
      ->from('sites')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');
    else if ($has_specs_info) $operator_ids = DB::select('id', 'name')
      ->from('operators')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $form = Formo::form();
    if ($has_site_id) $form = $form
      ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'required' => TRUE))
      ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
      ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')));
    else if ($has_specs_info) {
      $form = $form
        ->add_group('operator_id', 'select', $operator_ids, NULL, array_merge(array('label' => 'Operator', ), $has_specs_info ? array('attr' => array('class' => 'specs_operatoropts specs-numbers')) : array()))
        ->add_group('specs_number', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')));
    }
    $form = $form
      ->add('created', 'input', SGS::date('now', SGS::US_DATE_FORMAT), array('label' => 'Date Created', 'required' => TRUE, 'attr' => array('class' => 'dpicker', 'id' => 'created-dpicker')))
      ->add('due', 'input', SGS::date('now + 30 days', SGS::US_DATE_FORMAT), array('label' => 'Date Due', 'required' => TRUE, 'attr' => array('class' => 'dpicker', 'id' => 'due-dpicker')))
      ->add('format', 'radios', 'preview', array(
        'options' => array(
          'preview' => 'Preview',
          'draft'   => 'Draft Copy',
          'final'   => 'Final Copy'),
        'label' => '&nbsp;',
        'required' => TRUE,
        ))
      ->add('submit', 'submit', 'Generate');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.invoice.data');
      $format     = $form->format->val();
      if ($has_site_id) {
        $site_id  = $form->site_id->val();
        $from     = $form->from->val();
        $to       = $form->to->val();
      }
      if ($has_specs_info) {
        $operator_id  = $form->operator_id->val();
        $specs_number = $form->specs_number->val();
      }
      $created  = $form->created->val();
      $due      = $form->due->val();

      Session::instance()->set('pagination.invoice.data', array(
        'format'       => $format,
        'operator_id'  => $operator_id,
        'site_id'      => $site_id,
        'specs_number' => $specs_number,
        'from'         => $from,
        'to'           => $to,
        'created'      => $created,
        'due'          => $due
      ));
    }
    else if ($settings = Session::instance()->get('pagination.invoice.data')) {
      if ($has_site_id) {
        $form->format->val($format = $settings['format']);
        $form->site_id->val($site_id = $settings['site_id']);
        $form->from->val($from = $settings['from']);
        $form->to->val($to = $settings['to']);
      } else if ($has_specs_info) {
        $form->operator_id->val($operator_id = $settings['operator_id']);
        $form->specs_number->val($specs_number = $settings['specs_number']);
      }
      $form->created->val($created = $settings['created']);
      $form->due->val($due = $settings['due']);
    }

    if ($format) {
      switch ($invoice_type) {
        case 'ST':
          $form_type = 'LDF';
          $ids = DB::select('ldf_data.id')
            ->from('ldf_data')
            ->join(DB::expr('"barcodes" as "parent_barcodes"'))
            ->on('ldf_data.parent_barcode_id', '=', 'parent_barcodes.id')
            ->join('barcode_hops_cached')
            ->on('ldf_data.barcode_id', '=', 'barcode_hops_cached.barcode_id')
            ->on('ldf_data.parent_barcode_id', '=', 'barcode_hops_cached.parent_id')
            ->join('invoice_data', 'LEFT OUTER')
            ->on('ldf_data.id', '=', 'invoice_data.form_data_id')
            ->on('invoice_data.form_type', '=', DB::expr("'LDF'"))
            ->join('invoices', 'LEFT OUTER')
            ->on('invoice_data.invoice_id', '=', 'invoices.id')
            ->where('ldf_data.site_id', '=', $site_id)
            ->and_where('ldf_data.create_date', 'BETWEEN', SGS::db_range($from, $to))
            ->and_where('parent_barcodes.type', '=', 'F')
            ->and_where('barcode_hops_cached.hops', '=', '1')
            ->and_where_open()
              ->where('invoices.type', '<>', 'EXF')
              ->or_where('invoice.type', '=', 'NULL')
            ->and_where_close()
            ->execute()
            ->as_array(NULL, 'id');
          break;

        case 'EXF':
          $form_type = 'SPECS';
          $ids = DB::select('specs_data.id')
            ->from('specs_data')
            ->join('document_data')
            ->on('specs_data.id', '=', 'document_data.form_data_id')
            ->on('document_data.form_type', '=', DB::expr("'SPECS'"))
            ->join('invoice_data', 'LEFT OUTER')
            ->on('specs_data.id', '=', 'invoice_data.form_data_id')
            ->on('invoice_data.form_type', '=', DB::expr("'SPECS'"))
            ->join('invoices', 'LEFT OUTER')
            ->on('invoice_data.invoice_id', '=', 'invoices.id')
            ->where('document_data.document_id', '=', SGS::lookup_document('SPECS', $specs_number, TRUE))
            ->and_where('invoice_data.form_data_id', '=', NULL)
            ->and_where_open()
              ->where('invoices.type', '<>', 'EXF')
              ->or_where('invoice.type', '=', 'NULL')
            ->and_where_close()
            ->execute()
            ->as_array(NULL, 'id');
          break;
      }

      if ($form_type and $ids) {
        $site     = ORM::factory('site', $site_id);
        $operator = ORM::factory('operator', $operator_id ?: $site->operator->id);

        switch ($format) {
          case 'preview':
            $data = ORM::factory($form_type)
              ->where(strtolower($form_type).'.id', 'IN', (array) $ids)
              ->join('barcodes')
              ->on('barcode_id', '=', 'barcodes.id')
              ->order_by('barcode', 'ASC');

            $clone = clone($data);
            $pagination = Pagination::factory(array(
              'items_per_page' => 50,
              'total_items' => $clone->find_all()->count()));

            $data = $data
              ->offset($pagination->offset)
              ->limit($pagination->items_per_page)
              ->find_all()
              ->as_array();

            if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' record found');
            elseif ($pagination->total_items) Notify::msg($pagination->total_items.' records found');
            else Notify::msg('No records found');

            $func = strtolower('generate_'.$invoice_type.'_preview');
            $summary = self::$func((array) $ids);

            unset($info);
            if ($specs_number) {
              $sample = reset($data);
              $info['specs'] = array(
                'number'  => $sample->specs_number,
                'barcode' => $sample->specs_barcode->barcode
              );
              if (Valid::numeric($specs_number)) $info['exp'] = array(
                'number'  => $sample->exp_number,
                'barcode' => $sample->exp_barcode->barcode
              );
            }

            $header = View::factory('data')
              ->set('form_type', $form_type)
              ->set('data', $data)
              ->set('site', $site_id ? $site : NULL)
              ->set('operator', $operator_id ? $operator : NULL)
              ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
              ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
              ->set('options', array(
                'table'   => FALSE,
                'rows'    => FALSE,
                'actions' => FALSE,
                'header'  => TRUE,
                'details' => FALSE,
                'links'   => FALSE
              ))
              ->render();

            $table = View::factory('data')
              ->set('classes', array('has-pagination'))
              ->set('form_type', $form_type)
              ->set('data', $data)
              ->set('site', $site_id ? $site : NULL)
              ->set('operator', $operator_id ? $operator : NULL)
              ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
              ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
              ->set('options', array(
                'links'  => FALSE,
                'header' => FALSE,
                'hide_header_info' => TRUE
              ))
              ->render();
            break;

          case 'draft':
            $is_draft = TRUE;

          case 'final':
            set_time_limit(1800);
            $invoice = ORM::factory('invoice');

            if ($operator->loaded()) $invoice->operator = $operator;
            if ($site->loaded())     $invoice->site = $site;

            $invoice->type = $invoice_type;
            $invoice->is_draft = $is_draft ? TRUE : FALSE;
            $invoice->number = $is_draft ? NULL : $invoice::create_invoice_number($invoice_type);

            if ($from) $invoice->from_date = SGS::date($from, SGS::PGSQL_DATE_FORMAT, TRUE);
            if ($to) $invoice->to_date = SGS::date($to, SGS::PGSQL_DATE_FORMAT, TRUE);

            $invoice->created_date = SGS::date($created, SGS::PGSQL_DATE_FORMAT, TRUE);
            $invoice->due_date = SGS::date($due, SGS::PGSQL_DATE_FORMAT, TRUE);

            $func = strtolower('generate_'.$invoice_type.'_invoice');
            $invoice->file_id = self::$func($invoice, $ids);

            if ($invoice->file_id) Notify::msg('Invoice file successfully generated.', NULL, TRUE);
            else Notify::msg('Sorry, invoice file failed to be generated. Please try again.', 'error');

            try {
              $invoice->save();
              foreach ($ids as $id) $invoice->set_data($form_type, $id);

              Notify::msg(($invoice->is_draft ? 'Draft invoice' : 'Invoice') . ' created.', 'success', TRUE);
              $this->request->redirect('invoices/'.$invoice->id);
            } catch (Exception $e) {
              Notify::msg('Sorry, unable to create invoice. Please try again.', 'error');
            }
            break;
        }
      } else Notify::msg('No data found. Skipping invoice.', 'warning');
    }

    if ($form) $content .= $form;

    $content .= $header;
    $content .= $summary;
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_invoice_finalize($id) {
    $invoice = ORM::factory('invoice', $id);
    if (!($invoice->loaded() and $invoice->is_draft)) {
      Notify::msg('Invoice already finalized.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    $invoice->is_draft = FALSE;
    $invoice->number = $invoice::create_invoice_number($invoice->type);

    switch ($invoice->type) {
      case 'ST': $invoice->file_id = self::generate_st_invoice($invoice, $invoice->get_data()); break;
      case 'EXF': $invoice->file_id = self::generate_exf_invoice($invoice, $invoice->get_data()); break;
    }

    if ($invoice->file_id) Notify::msg('Invoice file successfully generated.', NULL, TRUE);
    else Notify::msg('Sorry, invoice file failed to be generated. Please try again.', 'error', TRUE);

    try {
      $invoice->save();

      Notify::msg('Invoice finalized.', 'success', TRUE);
      $this->request->redirect('invoices/'.$invoice->id);
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to create invoice. Please try again.', 'error');
      $this->request->redirect('invoices/'.$invoice->id);
    }

  }

  private function handle_invoice_list($id = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.invoice.list');
    if ($id) {
      Session::instance()->delete('pagination.invoice.list');

      $invoice = ORM::factory('invoice', $id);
      $invoices = array($invoice);

      if ($invoice->loaded()) {
        $ids  = $invoice->get_data();
        $func = strtolower('generate_'.$invoice->type.'_preview');
        $summary = self::$func((array) $ids);

        switch ($invoice->type) {
          case 'ST': $form_type = 'LDF'; break;
          case 'EXF': $form_type = 'SPECS'; break;
        }

        $summary_data = ORM::factory($form_type)
          ->where(strtolower($form_type).'.id', 'IN', (array) $ids)
          ->join('barcodes')
          ->on('barcode_id', '=', 'barcodes.id')
          ->order_by('barcode', 'ASC');

        $summary_clone = clone($summary_data);
        $summary_pagination = Pagination::factory(array(
          'current_page' => array(
            'source' => 'query_string',
            'key' => 'summary_page',
          ),
          'items_per_page' => 50,
          'total_items' => $summary_clone->find_all()->count()));

        $summary_data = $summary_data
          ->offset($summary_pagination->offset)
          ->limit($summary_pagination->items_per_page)
          ->find_all()
          ->as_array();

        unset($info);
        if ($form_type == 'SPECS') {
          $sample = reset($summary_data);
          $info['specs'] = array(
            'number'  => $sample->specs_number,
            'barcode' => $sample->specs_barcode->barcode
          );
        }

        $summary_header = View::factory('data')
          ->set('form_type', $form_type)
          ->set('data', $summary_data)
          ->set('operator', $invoice->operator->loaded() ? $invoice->operator : NULL)
          ->set('site', $invoice->site->loaded() ? $invoice->site : NULL)
          ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
          ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
          ->set('options', array(
            'table'   => FALSE,
            'rows'    => FALSE,
            'actions' => FALSE,
            'header'  => TRUE,
            'details' => FALSE,
            'links'   => FALSE
          ))
          ->render();

        $summary_table = View::factory('data')
          ->set('classes', array('has-pagination'))
          ->set('form_type', $form_type)
          ->set('data', $summary_data)
          ->set('operator', $invoice->operator->loaded() ? $invoice->operator : NULL)
          ->set('site', $invoice->site->loaded() ? $invoice->site : NULL)
          ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
          ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
          ->set('options', array(
            'links'  => FALSE,
            'header' => FALSE,
            'hide_header_info' => TRUE
          ))
          ->render();
      } else $this->request->redirect('invoices');
    }
    else {
      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('type', 'checkboxes', SGS::$invoice_type, NULL, array('label' => 'Type'))
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.invoice.list');

        $type    = $form->type->val();
        $site_id = $form->site_id->val();

        $invoices = ORM::factory('invoice');

        if ($type)    $invoices->and_where('type', 'IN', (array) $type);
        if ($site_id) $invoices->and_where('site_id', 'IN', (array) $site_id);

        Session::instance()->set('pagination.invoice.list', array(
          'type'    => $type,
          'site_id' => $site_id,
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.invoice.list')) {
          $form->type->val($type = $settings['type']);
          $form->site_id->val($site_id = $settings['site_id']);
        }

        $invoices = ORM::factory('invoice');

        if ($type)    $invoices->and_where('type', 'IN', (array) $type);
        if ($site_id) $invoices->and_where('site_id', 'IN', (array) $site_id);
      }

      if ($invoices) {
        $clone = clone($invoices);
        $pagination = Pagination::factory(array(
          'items_per_page' => 20,
          'total_items' => $clone->find_all()->count()));

        $invoices = $invoices
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $invoices->order_by($sort);
        $invoices = $invoices->order_by('number', 'DESC')
          ->find_all()
          ->as_array();
      }
    }

    if ($invoices) {
      $table = View::factory('invoices')
        ->set('classes', array('has-pagination'))
        ->set('invoices', $invoices)
        ->render();
      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' invoice found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' invoices found');
    }
    else Notify::msg('No invoices found');

    if ($form) $content .= $form->render();

    $content .= $summary_header;
    $content .= $table;
    $content .= $pagination;
    $content .= $summary;
    $content .= $summary_table;
    $content .= $summary_pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_invoice_delete($id) {
    $invoice  = ORM::factory('invoice', $id);

    if (!$invoice->loaded()) {
      Notify::msg('No invoice found.', 'warning', TRUE);
      $this->request->redirect('invoices');
    }

    if (!$invoice->is_draft) {
      Notify::msg('Sorry, cannot delete final invoices.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$invoice->id);
    }

    $form = Formo::form()
      ->add('confirm', 'text', 'Are you sure you want to delete this draft invoice?')
      ->add('delete', 'submit', 'Delete');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $invoice->delete();
        if ($invoice->loaded()) throw new Exception();
        Notify::msg('Draft invoice successfully deleted.', 'success', TRUE);
      } catch (Exception $e) {
        Notify::msg('Draft invoice failed to be deleted.', 'error', TRUE);
      }

      $this->request->redirect('invoices');
    }

    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_index() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    switch ($command) {
      case 'download': return self::handle_invoice_download($id);
      case 'finalize': return self::handle_invoice_finalize($id);
      case 'delete': return self::handle_invoice_delete($id);
      case 'list': default: return self::handle_invoice_list($id);
    }
  }

  public function action_list() {
    $id = $this->request->param('id');

    return self::handle_invoice_list($id);
  }

  public function action_create() {
    $invoice_type = $this->request->param('id');

    switch ($invoice_type) {
      case 'st':  return self::handle_invoice_create('ST');
      case 'exf': return self::handle_invoice_create('EXF');
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }


  private function generate_st_preview($data_ids) {
    $data = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price', array(DB::expr('sum(volume)'), 'volume'))
      ->from('ldf_data')
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where('ldf_data.id', 'IN', (array) $data_ids)
      ->group_by('species_code', 'species_class', 'fob_price')
      ->execute()
      ->as_array();

    foreach ($data as $record) {
      foreach ($record as $key => $value) $total[$key] += $value;
      $total['total'] += $record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']];
      $total['fob_total'] += $record['volume'] * $record['fob_price'];
    }

    return View::factory('invoices/st_summary')
      ->set('data', $data)
      ->set('total', array('summary' => $total))
      ->render();
  }

  private function generate_st_invoice($invoice, $data_ids = array()) {
    if (!($data_ids ?: $invoice->get_data())) {
      Notify::msg('No data found. Unable to generate invoice.', 'warning');
      return FALSE;
    }

    $summary_data = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price', array(DB::expr('sum(volume)'), 'volume'))
      ->from('ldf_data')
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where('ldf_data.id', 'IN', (array) $data_ids)
      ->group_by('species_code', 'species_class', 'fob_price')
      ->execute()
      ->as_array();

    foreach(DB::select('barcode', array('create_date', 'scan_date'), array('code', 'species_code'), array('class', 'species_class'), array('botanic_name', 'species_botanic_name'), array(DB::expr('((top_min + top_max + bottom_min + bottom_max) / 4)'), 'diameter'), 'length', 'volume')
      ->from('ldf_data')
      ->join('barcodes')
      ->on('barcode_id', '=', 'barcodes.id')
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where('ldf_data.id', 'IN', (array) $data_ids)
      ->order_by('barcode')
      ->execute() as $result) $details_data[$result['species_code']][] = $result;

    $summary_signature_page_max = 4;
    $summary_one_page_max       = 8;
    $summary_first_page_max     = 10;
    $summary_last_page_max      = 11;
    $summary_normal_page_max    = 13;

    $summary_count = count($summary_data);
    foreach ($summary_data as $record) {
      foreach ($record as $key => $value) $summary_total[$key] += $value;
      $summary_total['total'] += $record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']];
    }

    $details_page_max = 40;
    foreach ($details_data as $code => $records) {
      foreach ($records as $record) foreach ($record as $key => $value) $details_total[$code][$key] += $value;
    }

    $cntr  = 0;
    $signature_remaining  = TRUE;
    while ($cntr < $summary_count) {
      $options = array();
      if ($cntr == 0) $first = TRUE;
      if (($summary_count - $cntr) <= $summary_last_page_max) $last = TRUE;
      if (($cntr == 0) and ($summary_count <= $summary_one_page_max)) $one = TRUE;
      if (($summary_count - $cntr) <= $summary_signature_page_max) $sign = TRUE;

      if ($first) {
        $max = $summary_first_page_max;
        $options = array(
          'break'  => FALSE,
          'styles' => TRUE,
          'info'   => TRUE
        );
      }

      if ($last and !$first) {
        $max = $summary_last_page_max;
        $options = array(
          'total' => TRUE
        );
      }

      if ($first and $last) {
        $max = $summary_first_page_max;
        if ($one) {
          $max = $summary_one_page_max;
          $options = array(
            'break'  => FALSE,
            'styles' => TRUE,
            'info'   => TRUE,
            'total'  => TRUE
          );
        }
      }

      $max = $max ?: $summary_normal_page_max;

      if ((!$first or $summary_count <= 1) and $last and $sign) {
        $signature_remaining  = FALSE;
        $options['signature'] = TRUE;
      }

      $set = array_filter(array_slice($summary_data, $cntr, $max));
      if ($set) $html .= View::factory('invoices/st')
        ->set('invoice', $invoice)
        ->set('data', $set)
        ->set('site', $invoice->site)
        ->set('operator', $invoice->site->operator)
        ->set('options', array('summary' => TRUE) + (array) $options)
        ->set('total', array('summary' => $summary_total))
        ->render();

      $options['signature'] = FALSE;
      if ($signature_remaining and $last and (($summary_count - $max) <= 0)) {
        $signature_remaining = FALSE;
        $html .= View::factory('invoices/st')
          ->set('invoice', $invoice)
          ->set('options', array(
              'summary'   => TRUE,
              'total'     => TRUE,
              'break'     => TRUE,
              'signature' => TRUE
            ))
          ->set('total', array('summary' => $summary_total))
          ->render();
      }

      $cntr += $max;
      $first = $last = FALSE;
    }

    if ($signature_remaining) {
      $html .= View::factory('invoices/st')
        ->set('invoice', $invoice)
        ->set('options', array('signature' => TRUE))
        ->render();
    }

    $max = $details_page_max;
    foreach ($details_data as $code => $records) {
      $cntr = 0;
      while ($cntr < count($records)) {
        $set = array_slice($records, $cntr, $max);
        $html .= View::factory('invoices/st')
          ->set('invoice', $invoice)
          ->set('data', $set)
          ->set('options', array(
            'details' => TRUE,
            'total'   => count($records) > ($cntr + $max) ? FALSE : TRUE
          ))
          ->set('total', array('details' => $details_total))
          ->render();
        $cntr += $max;
      }
    }

    // generate pdf
    set_time_limit(600);

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array(
      'invoices',
      'st',
      $invoice->site->name
    ));

    if ($invoice->is_draft) $newname = 'ST_DRAFT_'.SGS::date($invoice->created_date, 'Y_m_d').'.'.$ext;
    else $newname = 'ST_'.$invoice->number.'.'.$ext;

    $version = 0;
    $testname = $newname;
    while (file_exists(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname)) {
      $newname = substr($testname, 0, strrpos($testname, '.'.$ext)).'_'.($version++).'.'.$ext;
    }

    if (!is_dir(DOCPATH.$newdir) and !mkdir(DOCPATH.$newdir, 0777, TRUE)) {
      Notify::msg('Sorry, cannot access invoices folder. Check file access capabilities with the site administrator and try again.', 'error');
      return FALSE;
    }

    $fullname = DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname;

    try {
      $snappy = new \Knp\Snappy\Pdf();
      $snappy->generateFromHtml($html, $fullname, array(
        'load-error-handling' => 'ignore',
        'margin-bottom' => 22,
        'margin-left' => 0,
        'margin-right' => 0,
        'margin-top' => 0,
        'lowquality' => TRUE,
        'page-size'  => 'A4',
        'disable-smart-shrinking' => TRUE,
        'footer-html' => View::factory('invoices/st')
          ->set('invoice', $invoice)
          ->set('options', array(
            'header' => FALSE,
            'footer' => TRUE,
            'break'  => FALSE))
          ->render()
      ));
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to generate invoice document. If this problem continues, contact the system administrator.', 'error');
      return FALSE;
    }

    try {
      $file = ORM::factory('file');
      $file->name = $newname;
      $file->type = 'application/pdf';
      $file->size = filesize($fullname);
      $file->operation      = 'A';
      $file->operation_type = 'INV';
      $file->content_md5    = md5_file($fullname);
      $file->path = DIRECTORY_SEPARATOR.str_replace(DOCROOT, '', DOCPATH).$newdir.DIRECTORY_SEPARATOR.$newname;
      $file->save();
      return $file->id;
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err).' ('.$file->name.')', 'error');
      return FALSE;
    }
  }

  private function generate_exf_preview($data_ids) {
    $data = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price', array(DB::expr('sum(volume)'), 'volume'))
      ->from('specs_data')
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where('specs_data.id', 'IN', (array) $data_ids)
      ->group_by('species_code', 'species_class', 'fob_price')
      ->execute()
      ->as_array();

    foreach ($data as $record) {
      foreach ($record as $key => $value) $total[$key] += $value;
      $total['total'] += $record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']];
      $total['fob_total'] += $record['volume'] * $record['fob_price'];
    }

    return View::factory('invoices/exf_summary')
      ->set('data', $data)
      ->set('total', array('summary' => $total))
      ->render();
  }

  private function generate_exf_invoice($invoice, $data_ids = array()) {
    if (!($data_ids ?: $invoice->get_data())) {
      Notify::msg('No data found. Unable to generate invoice.', 'warning');
      return FALSE;
    }

    $sample = ORM::factory('SPECS', reset($data_ids));

    $summary_data = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price', array(DB::expr('sum(volume)'), 'volume'))
      ->from('specs_data')
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where('specs_data.id', 'IN', (array) $data_ids)
      ->group_by('species_code', 'species_class', 'fob_price')
      ->execute()
      ->as_array();

    foreach(DB::select('barcode', array('create_date', 'scan_date'), array('code', 'species_code'), array('class', 'species_class'), array('botanic_name', 'species_botanic_name'), array(DB::expr('((top_min + top_max + bottom_min + bottom_max) / 4)'), 'diameter'), 'length', 'volume', 'grade')
      ->from('specs_data')
      ->join('barcodes')
      ->on('barcode_id', '=', 'barcodes.id')
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where('specs_data.id', 'IN', (array) $data_ids)
      ->order_by('barcode')
      ->execute() as $result) $details_data[$result['species_code']][] = $result;

    $summary_signature_page_max = 4;
    $summary_one_page_max       = 6;
    $summary_first_page_max     = 8;
    $summary_last_page_max      = 11;
    $summary_normal_page_max    = 13;

    $summary_count = count($summary_data);
    foreach ($summary_data as $record) {
      foreach ($record as $key => $value) $summary_total[$key] += $value;
      $summary_total['total'] += $record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']];
      $summary_total['fob_total'] += $record['volume'] * $record['fob_price'];
    }

    $details_page_max = 40;
    foreach ($details_data as $code => $records) {
      foreach ($records as $record) foreach ($record as $key => $value) $details_total[$code][$key] += $value;
    }

    $cntr  = 0;
    $signature_remaining  = TRUE;
    while ($cntr < $summary_count) {
      $options = array();
      if ($cntr == 0) $first = TRUE;
      if (($summary_count - $cntr) <= $summary_last_page_max) $last = TRUE;
      if (($cntr == 0) and ($summary_count <= $summary_one_page_max)) $one = TRUE;
      if (($summary_count - $cntr) <= $summary_signature_page_max) $sign = TRUE;

      if ($first) {
        $max = $summary_first_page_max;
        $options = array(
          'break'  => FALSE,
          'styles' => TRUE,
          'info'   => TRUE,
          'fee'    => TRUE
        );
      }

      if ($last and !$first) {
        $max = $summary_last_page_max;
        $options = array(
          'total' => TRUE
        );
      }

      if ($first and $last) {
        $max = $summary_first_page_max;
        if ($one) {
          $max = $summary_one_page_max;
          $options = array(
            'break'  => FALSE,
            'styles' => TRUE,
            'info'   => TRUE,
            'fee'    => TRUE,
            'total'  => TRUE
          );
        }
      }

      $max = $max ?: $summary_normal_page_max;

      if (!$first and $last and $sign) {
        $signature_remaining  = FALSE;
        $options['signature'] = TRUE;
      }

      $set = array_filter(array_slice($summary_data, $cntr, $max));
      if ($set) $html .= View::factory('invoices/exf')
        ->set('invoice', $invoice)
        ->set('data', $set)
        ->set('operator', $invoice->operator)
        ->set('options', array('summary' => TRUE) + (array) $options)
        ->set('total', array('summary' => $summary_total))
        ->set('specs_barcode', $sample->specs_barcode->barcode)
        ->set('specs_number', $sample->specs_number)
        ->set('exp_barcode', $sample->exp_barcode->barcode)
        ->set('exp_number', $sample->exp_number)
        ->render();

      if ($signature_remaining and $last and (($summary_count - $max) <= 0)) {
        $signature_remaining = FALSE;
        $html .= View::factory('invoices/exf')
          ->set('invoice', $invoice)
          ->set('options', array(
              'summary'   => TRUE,
              'total'     => TRUE,
              'break'     => TRUE,
              'signature' => TRUE
            ))
          ->set('total', array('summary' => $summary_total))
          ->render();
      }

      $cntr += $max;
      $first = $last = FALSE;
    }

    if ($signature_remaining) {
      $html .= View::factory('invoices/exf')
        ->set('invoice', $invoice)
        ->set('options', array('signature' => TRUE))
        ->render();
    }

    $max = $details_page_max;
    foreach ($details_data as $code => $records) {
      $cntr = 0;
      while ($cntr < count($records)) {
        $set = array_slice($records, $cntr, $max);
        $html .= View::factory('invoices/exf')
          ->set('invoice', $invoice)
          ->set('data', $set)
          ->set('options', array(
            'details' => TRUE,
            'total'   => count($records) > ($cntr + $max) ? FALSE : TRUE
          ))
          ->set('total', array('details' => $details_total))
          ->render();
        $cntr += $max;
      }
    }

    // generate pdf
    set_time_limit(600);

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array(
      'invoices',
      'exf',
      $invoice->operator->tin
    ));

    if ($invoice->is_draft) $newname = 'EXF_DRAFT_'.SGS::date($invoice->created_date, 'Y_m_d').'.'.$ext;
    else $newname = 'EXF_'.$invoice->number.'.'.$ext;

    $version = 0;
    $testname = $newname;
    while (file_exists(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname)) {
      $newname = substr($testname, 0, strrpos($testname, '.'.$ext)).'_'.($version++).'.'.$ext;
    }

    if (!is_dir(DOCPATH.$newdir) and !mkdir(DOCPATH.$newdir, 0777, TRUE)) {
      Notify::msg('Sorry, cannot access invoices folder. Check file access capabilities with the site administrator and try again.', 'error');
      return FALSE;
    }

    $fullname = DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname;

    try {
      $snappy = new \Knp\Snappy\Pdf();
      $snappy->generateFromHtml($html, $fullname, array(
        'load-error-handling' => 'ignore',
        'margin-bottom' => 22,
        'margin-left' => 0,
        'margin-right' => 0,
        'margin-top' => 0,
        'lowquality' => TRUE,
        'page-size'  => 'A4',
        'disable-smart-shrinking' => TRUE,
        'footer-html' => View::factory('invoices/exf')
          ->set('invoice', $invoice)
          ->set('options', array(
            'header' => FALSE,
            'footer' => TRUE,
            'break'  => FALSE))
          ->render()
      ));
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to generate invoice document. If this problem continues, contact the system administrator.', 'error');
      return FALSE;
    }

    try {
      $file = ORM::factory('file');
      $file->name = $newname;
      $file->type = 'application/pdf';
      $file->size = filesize($fullname);
      $file->operation      = 'A';
      $file->operation_type = 'INV';
      $file->content_md5    = md5_file($fullname);
      $file->path = DIRECTORY_SEPARATOR.str_replace(DOCROOT, '', DOCPATH).$newdir.DIRECTORY_SEPARATOR.$newname;
      $file->save();
      return $file->id;
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err).' ('.$file->name.')', 'error');
      return FALSE;
    }
  }

}