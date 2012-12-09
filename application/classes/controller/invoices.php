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

  private function handle_invoice_finalize($id) {
    $invoice = ORM::factory('invoice', $id);
    if (!($invoice->loaded() and $invoice->is_draft)) {
      Notify::msg('Invoice already finalized.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    $invoice->is_draft = FALSE;
    $invoice->reference_number = DB::query(Database::SELECT, "SELECT nextval('s_invoices_reference_number') reference_number")->execute()->get('reference_number');

    switch ($invoice->type) {
      case 'ST': $invoice->file_id = self::generate_st_invoice($invoice, array_keys($invoice->get_data()));
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

      $invoices = array(ORM::factory('invoice', $id));
      if (!$invoices) $this->request->redirect ('invoices');
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
          'form_type'   => $type,
          'site_id'     => $site_id,
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.invoice.list')) {
          $form->type->val($type = $settings['form_type']);
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
        $invoices = $invoices->order_by('created_date', 'DESC')
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
    $content .= $table;
    $content .= $pagination;

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
//      try {
        $invoice->delete();
        if ($invoice->loaded()) throw new Exception();
//        Notify::msg('Draft invoice successfully deleted.', 'success', TRUE);
//      } catch (Exception $e) {
//        Notify::msg('Draft invoice failed to be deleted.', 'error', TRUE);
//      }

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

  private function generate_st_invoice($invoice, $data_ids = array()) {
    if (!$data_ids) {
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
      ->execute() as $result) $details_data[$result['species_code']][] = $result;

    $summary_page_count      = 0;
    $summary_first_page_max  = 9;
    $summary_last_page_max   = 10;
    $summary_normal_page_max = 12;

    $summary_count = count($summary_data);
    $summary_page_count = ceil(($summary_count - $summary_first_page_max - $summary_last_page_max) / $summary_normal_page_max) + 2;

    foreach ($summary_data as $record) {
      foreach ($record as $key => $value) $summary_total[$key] += $value;
      $summary_total['total'] += $record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']];
    }

    $details_page_count = 0;
    $details_page_max   = 37;

    foreach ($details_data as $code => $records) {
      $details_page_count += ceil(count($records) / $details_page_max);
      foreach ($records as $record) foreach ($record as $key => $value) $details_total[$code][$key] += $value;
    }

    $signature_page_count = 1;
    $page_count = $summary_page_count + $signature_page_count + $details_page_count;

    $cntr = 0;
    for ($page = 1; $page <= $summary_page_count; $page++) {
      $options = array();
      if ($page == 1) {
        $max = $summary_first_page_max;
        $options = array(
          'break' => FALSE,
          'styles' => TRUE,
          'info' => TRUE
        );
      } else if ($page == $summary_page_count) {
        $max = $summary_last_page_max;
      } else {
        $max = $summary_normal_page_max;
      }

      if ($page == $summary_page_count) $options['total'] = TRUE;

      $set = array_filter(array_slice($summary_data, $cntr, $max));
      $html .= View::factory('invoices/st')
        ->set('invoice', $invoice)
        ->set('data', $set)
        ->set('from', $from)
        ->set('to', $to)
        ->set('site', $invoice->site)
        ->set('operator', $invoice->site->operator)
        ->set('options', array('summary' => TRUE) + (array) $options)
        ->set('page', $page)
        ->set('page_count', $page_count)
        ->set('total', array('summary' => $summary_total))
        ->render();

      $cntr += $max;
    }

    $html .= View::factory('invoices/st')
      ->set('invoice', $invoice)
      ->set('options', array('signature' => TRUE))
      ->set('page', $page)
      ->set('page_count', $page_count)
      ->render();
    $page++;

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
          ->set('page', $page)
          ->set('page_count', $page_count)
          ->set('total', array('details' => $details_total))
          ->render();
        $cntr += $max;
        $page++;
      }
    }

    // generate pdf
    set_time_limit(600);

//    try {
//      $tcpdf = new TCPDF();
//      $tcpdf->SetAutoPageBreak(TRUE);
//      $tcpdf->AddPage();
//      $tcpdf->writeHTML($html);
//      $tcpdf->lastPage();

//      if (!$output = $tcpdf->output(NULL, 'S')) throw new Exception();

//      $dompdf = new DOMPDF();
//      $dompdf->set_paper("A4");
//      $dompdf->load_html($html);
//      $dompdf->render();
//      if (!$output = $dompdf->output()) throw new Exception();
//    } catch (Exception $e) {
//      Notify::msg('Sorry, unable to generate invoice document. If this problem continues, contact the system administrator.', 'error');
//      return FALSE;
//    }

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array(
      'invoices',
      $invoice->site->name
    ));

    if ($invoice->is_draft) $newname = 'DRAFT_'.SGS::date($invoice->created_date, 'Y_m_d').'.'.$ext;
    else $newname = 'ST_'.$invoice->reference_number.'.'.$ext;

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
//    if (!(file_put_contents($fullname, $output) and chmod($fullname, 0777))) {
//      Notify::msg('Sorry, cannot create invoice. Check file operation capabilities with the site administrator and try again.', 'error');
//      return FALSE;
//    }

    try {
      $snappy = new \Knp\Snappy\Pdf();
      $snappy->generateFromHtml($html, $fullname, array(
        'margin-bottom' => 25,
        'margin-left' => 0,
        'margin-right' => 0,
        'margin-top' => 0,
        'lowquality' => TRUE,
        'disable-smart-shrinking' => TRUE,
        'footer-html' => View::factory('invoices/st')
          ->set('invoice', $invoice)
          ->set('options', array(
            'header' => FALSE,
            'footer' => TRUE,
            'break'  => FALSE))
          ->set('page', $page)
          ->set('page_count', $page_count)
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

  public function action_create() {
    if (!Request::$current->query()) Session::instance()->delete('pagination.invoice.data');
    $command = $this->request->param('command');

    $site_ids = DB::select('id', 'name')
      ->from('sites')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $form = Formo::form()
      ->add('type', 'select', array(
        'options' => array('ST' => 'Stumpage Invoice'),
        'label' => 'Invoice',
        'required' => TRUE,
        ))
      ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'required' => TRUE))
      ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
      ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')))
      ->add('created', 'input', SGS::date('now', SGS::US_DATE_FORMAT), array('label' => 'Date Created', 'required' => TRUE, 'attr' => array('class' => 'dpicker', 'id' => 'created-dpicker')))
      ->add('due', 'input', SGS::date('now + 30 days', SGS::US_DATE_FORMAT), array('label' => 'Date Due', 'required' => TRUE, 'attr' => array('class' => 'dpicker', 'id' => 'due-dpicker')))
      ->add('format', 'radios', array(
        'options' => array(
          'preview' => 'Preview',
          'draft'   => 'Draft Copy',
          'final'   => 'Final Copy'),
        'label' => '&nbsp;',
        'required' => TRUE,
        ))
      ->add('submit', 'submit', 'Generate');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.data');
      $format   = $form->format->val();
      $site_id  = $form->site_id->val();
      $type     = $form->type->val();
      $from     = $form->from->val();
      $to       = $form->to->val();
      $created  = $form->created->val();
      $due      = $form->due->val();

      Session::instance()->set('pagination.invoice.data', array(
        'site_id' => $site_id,
        'type'    => $type,
        'from'    => $from,
        'to'      => $to,
        'created' => $created,
        'due'     => $due
      ));

      $site = ORM::factory('site', $site_id);

      switch ($type) {
        case 'ST':
          $form_type = 'LDF';
          $ids = DB::select('ldf_data.id')
            ->from('ldf_data')
            ->join('barcodes')
            ->on('ldf_data.parent_barcode_id', '=', 'barcodes.id')
            ->join('invoice_data', 'LEFT OUTER')
            ->on('ldf_data.id', '=', 'invoice_data.form_data_id')
            ->on('invoice_data.form_type', '=', DB::expr("'LDF'"))
            ->where('ldf_data.site_id', '=', $site_id)
            ->and_where('ldf_data.create_date', 'BETWEEN', SGS::db_range($from, $to))
            ->and_where('barcodes.type', '=', 'F')
            ->and_where('invoice_data.form_data_id', '=', NULL)
            ->execute()
            ->as_array();
          break;
      }

      if ($form_type and $ids) {

        $model = ORM::factory($form_type);
        $sql   = "SELECT form_data_id
                  FROM invoice_data
                  JOIN invoices ON invoice_data.invoice_id = invoices.id
                  WHERE form_type = '$form_type' AND type = '$type'";

        switch ($format) {
          case 'preview':
            $data = $model
              ->where('id', 'IN', (array) $ids)
              ->order_by('create_date', 'ASC');

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

            $site  = ORM::factory('site', $site_id);
            $table = View::factory('data')
              ->set('classes', array('has-pagination'))
              ->set('form_type', $form_type)
              ->set('data', $data)
              ->set('site', $site)
              ->set('options', array('links' => FALSE))
              ->render();
            break;

          case 'draft':
            $is_draft = TRUE;
          case 'final':
            set_time_limit(600);
            $invoice = ORM::factory('invoice');
            $invoice->site = $site;
            $invoice->type = $type;
            $invoice->is_draft = $is_draft ? TRUE : FALSE;
            $invoice->reference_number = $is_draft ? NULL : DB::query(Database::SELECT, "SELECT nextval('s_invoices_reference_number') reference_number")->execute()->get('reference_number');
            if ($from) $invoice->from_date = SGS::date($from, SGS::PGSQL_DATE_FORMAT, TRUE);
            if ($to) $invoice->to_date = SGS::date($to, SGS::PGSQL_DATE_FORMAT, TRUE);
            $invoice->created_date = SGS::date($created, SGS::PGSQL_DATE_FORMAT, TRUE);
            $invoice->due_date = SGS::date($due, SGS::PGSQL_DATE_FORMAT, TRUE);

            $func = strtolower('generate_'.$type.'_invoice');
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
    else if ($settings = Session::instance()->get('pagination.invoice.data')) {
      $form->site_id->val($site_id = $settings['site_id']);
      $form->type->val($type = $settings['type']);
      $form->from->val($from = $settings['from']);
      $form->to->val($to = $settings['to']);
      $form->created->val($from = $settings['created']);
      $form->due->val($to = $settings['due']);
    }

    if ($form) $content .= $form;
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}