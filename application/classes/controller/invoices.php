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

    $has_site_id    = (bool) (in_array($invoice_type, array('ST', 'TAG')));
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
    if ($has_site_id) {
      $form = $form->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'required' => TRUE));
      if ($invoice_type == 'TAG') {
        $tag_quantities = array();
        foreach (range(1000, 2000, 1000) as $x) $tag_quantities[$x] = $x;
        if ($invoice_type == 'TAG') $form = $form->add_group('tag_quantity', 'select', $tag_quantities, NULL, array('label' => 'Quantity', 'required' => TRUE));
      } else $form = $form
          ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
          ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')));
    } else if ($has_specs_info) {
      $form = $form
        ->add_group('operator_id', 'select', $operator_ids, NULL, array_merge(array('label' => 'Operator', ), $has_specs_info ? array('attr' => array('class' => 'specs_operatoropts specs_number')) : array()))
        ->add_group('specs_number', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')));
    }
    $form = $form
      ->add('created', 'input', SGS::date('now', SGS::US_DATE_FORMAT), array('label' => 'Date Created', 'required' => TRUE, 'attr' => array('class' => 'dpicker', 'id' => 'created-dpicker')))
      ->add('due', 'input', SGS::date('now + 30 days', SGS::US_DATE_FORMAT), array('label' => 'Date Due', 'required' => TRUE, 'attr' => array('class' => 'dpicker', 'id' => 'due-dpicker')))
      ->add('format', 'radios', 'preview', array(
        'options' => array(
          'preview' => 'Preview',
          'draft'   => 'Draft Copy',
//          'final'   => 'Final Copy'
        ),
        'label' => '&nbsp;',
        'required' => TRUE,
        ))
      ->add('submit', 'submit', 'Generate');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.invoice.data');
      $format     = $form->format->val();
      if ($has_site_id) {
        $site_id  = $form->site_id->val();
        if ($invoice_type == 'TAG') $tag_quantity = $form->tag_quantity->val();
        else {
          $from     = $form->from->val();
          $to       = $form->to->val();
        }
      }
      if ($has_specs_info) {
        $operator_id  = $form->operator_id->val();
        $specs_number = $form->specs_number->val();
      }
      $created   = $form->created->val();
      $due       = $form->due->val();

      Session::instance()->set('pagination.invoice.data', array(
        'format'       => $format,
        'operator_id'  => $operator_id,
        'site_id'      => $site_id,
        'specs_number' => $specs_number,
        'tag_quantity' => $tag_quantity,
        'from'         => $from,
        'to'           => $to,
        'created'      => $created,
        'due'          => $due
      ));
    }
    else if ($settings = Session::instance()->get('pagination.invoice.data')) {
      if ($has_site_id) {
        $form->site_id->val($site_id = $settings['site_id']);
        if ($invoice_type == 'TAG') $form->tag_quanity->val($tag_quantity = $settings['tag_quantity']);
        else {
          $form->from->val($from = $settings['from']);
          $form->to->val($to = $settings['to']);
        }
      } else if ($has_specs_info) {
        $form->operator_id->val($operator_id = $settings['operator_id']);
        $form->specs_number->val($specs_number = $settings['specs_number']);
      }
      $form->format->val($format = $settings['format']);
      $form->created->val($created = $settings['created']);
      $form->due->val($due = $settings['due']);
    }

    if ($format) {
      switch ($invoice_type) {
        case 'TAG':
          if (DB::select('barcodes.id')
            ->from('barcodes')
            ->join('printjobs')
            ->on('barcodes.printjob_id', '=', 'printjobs.id')
            ->on('printjobs.is_monitored', '=', DB::expr('true'))
            ->where('barcodes.type', '=', 'P')
            ->execute()->count() > 1000) $ids = FALSE;
          else $ids = TRUE;
          break;
        
        case 'ST':
          $form_type = 'TDF';
          $ids = array_filter(DB::select('barcodes.barcode', 'tdf_data.id')
            ->distinct(TRUE)

            ->from('tdf_data')
            ->join('barcodes')
            ->on('tdf_data.barcode_id', '=', 'barcodes.id')

            ->join(DB::expr('"barcode_hops" as "child_barcodes"'), 'LEFT OUTER')
            ->on('tdf_data.barcode_id', '=', 'child_barcodes.parent_id')

            ->join('ldf_data', 'LEFT OUTER')
            ->on('child_barcodes.barcode_id', '=', 'ldf_data.barcode_id')

            ->join(DB::expr('"invoice_data" as "tdf_invoice_data"'), 'LEFT OUTER')
            ->on('tdf_data.id', '=', 'tdf_invoice_data.form_data_id')
            ->on('tdf_invoice_data.form_type', '=', DB::expr("'TDF'"))

            ->join(DB::expr('"invoice_data" as "ldf_invoice_data"'), 'LEFT OUTER')
            ->on('ldf_data.id', '=', 'ldf_invoice_data.form_data_id')
            ->on('ldf_invoice_data.form_type', '=', DB::expr("'LDF'"))

            ->join(DB::expr('"invoices" as "tdf_invoices"'), 'LEFT OUTER')
            ->on('tdf_invoice_data.invoice_id', '=', 'tdf_invoices.id')
            ->on('tdf_invoices.type', '=', DB::expr("'ST'"))

            ->join(DB::expr('"invoices" as "ldf_invoices"'), 'LEFT OUTER')
            ->on('ldf_invoice_data.invoice_id', '=', 'ldf_invoices.id')
            ->on('ldf_invoices.type', '=', DB::expr("'ST'"))

            ->join(DB::expr('"barcode_activity" as "tdf_barcode_activity"'), 'LEFT OUTER')
            ->on('tdf_data.barcode_id', '=', 'tdf_barcode_activity.barcode_id')

            ->join(DB::expr('"barcode_activity" as "ldf_barcode_activity"'), 'LEFT OUTER')
            ->on('ldf_data.barcode_id', '=', 'ldf_barcode_activity.barcode_id')

            ->where('tdf_data.site_id', '=', $site_id)
            ->and_where('tdf_data.create_date', 'BETWEEN', SGS::db_range($from, $to))

            ->group_by('barcodes.barcode')
            ->group_by('tdf_data.id')

            ->having(DB::expr('NOT coalesce(array_agg(distinct "tdf_barcode_activity"."activity"::text), \'{}\')'), '@>', DB::expr("array['T']"))
            ->and_having(DB::expr('NOT coalesce(array_agg(distinct "ldf_barcode_activity"."activity"::text), \'{}\')'), '@>', DB::expr("array['T']"))

            ->and_having(DB::expr('array_agg(distinct "tdf_invoices"."id"::text)'), '=', NULL)
            ->and_having(DB::expr('array_agg(distinct "ldf_invoices"."id"::text)'), '=', NULL)

            ->order_by('barcodes.barcode')
            ->execute()
            ->as_array(NULL, 'id'));
          break;

        case 'EXF':
          $form_type = 'SPECS';
          $ids = array_filter(DB::select('barcodes.barcode', 'specs_data.id')
            ->distinct(TRUE)
            ->from('specs_data')

            ->join('document_data')
            ->on('specs_data.id', '=', 'document_data.form_data_id')
            ->on('document_data.form_type', '=', DB::expr("'SPECS'"))

            ->join('documents')
            ->on('document_data.document_id', '=', 'documents.id')

            ->join('barcodes')
            ->on('specs_data.barcode_id', '=', 'barcodes.id')

            ->join(DB::expr('"specs_data" as "related_specs_data"'), 'LEFT OUTER')
            ->on('specs_data.barcode_id', '=', 'related_specs_data.barcode_id')
            ->on('specs_data.id', '<>', 'related_specs_data.id')

            ->join('invoice_data', 'LEFT OUTER')
            ->on('specs_data.id', '=', 'invoice_data.form_data_id')
            ->on('invoice_data.form_type', '=', DB::expr("'SPECS'"))

            ->join(DB::expr('"invoice_data" as "related_invoice_data"'), 'LEFT OUTER')
            ->on('related_specs_data.id', '=', 'related_invoice_data.form_data_id')
            ->on('related_invoice_data.form_type', '=', DB::expr("'SPECS'"))

            ->join('invoices', 'LEFT OUTER')
            ->on('invoice_data.invoice_id', '=', 'invoices.id')
            ->on('invoices.type', '=', DB::expr("'EXF'"))

            ->join(DB::expr('"invoices" as "related_invoices"'), 'LEFT OUTER')
            ->on('related_invoice_data.invoice_id', '=', 'related_invoices.id')
            ->on('related_invoices.type', '=', DB::expr("'EXF'"))

            ->join('barcode_activity', 'LEFT OUTER')
            ->on('specs_data.barcode_id', '=', 'barcode_activity.barcode_id')

            ->where('specs_data.status', '=', 'A')
            ->and_where('documents.number', '=', $specs_number, TRUE)
            ->and_where('documents.is_draft', '=', FALSE)

            ->group_by('barcodes.barcode')
            ->group_by('specs_data.id')

            // ->having(DB::expr('coalesce(array_agg(distinct "barcode_activity"."activity"::text), \'{}\')'), '@>', DB::expr("array['D']"))
            ->having(DB::expr('NOT coalesce(array_agg(distinct "barcode_activity"."activity"::text), \'{}\')'), '&&', DB::expr("array['S','E','O','H','Y','A','L','X','Z']"))

            ->and_having(DB::expr('array_agg(distinct "invoices"."id"::text)'), '=', NULL)
            ->and_having(DB::expr('array_agg(distinct "related_invoices"."id"::text)'), '=', NULL)

            ->order_by('barcodes.barcode')
            ->execute()
            ->as_array(NULL, 'id'));
          break;
      }

      if ($ids) {
        $site     = ORM::factory('site', $site_id);
        $operator = ORM::factory('operator', $operator_id ?: $site->operator->id);

        if ($invoice_type == 'EXF') {
          $_site_id = DB::select('sites.id')
            ->distinct(TRUE)
            ->from('specs_data')
            ->join('ldf_data')
            ->on('specs_data.barcode_id', '=', 'ldf_data.barcode_id')
            ->join('sites')
            ->on('ldf_data.site_id', '=', 'sites.id')
            ->where('specs_data.id', 'IN', (array) $ids)
            ->execute()
            ->as_array(NULL, 'id');
          if (count($_site_id) == 1) $invoice->site = ORM::factory('site', reset($_site_id));
        }
        
        if ($invoice_type == 'TAG') $values['tag_quantity'] = $tag_quantity;

        $invoice = ORM::factory('invoice');
        $invoice->type = $invoice_type;
        $invoice->created_date = SGS::date($created, SGS::PGSQL_DATE_FORMAT, TRUE);
        $invoice->due_date = SGS::date($due, SGS::PGSQL_DATE_FORMAT, TRUE);
        $invoice->values = (array) $values;

        if ($from) $invoice->from_date = SGS::date($from, SGS::PGSQL_DATE_FORMAT, TRUE);
        if ($to) $invoice->to_date = SGS::date($to, SGS::PGSQL_DATE_FORMAT, TRUE);
        if ($operator->loaded()) $invoice->operator = $operator;
        if ($site->loaded()) {
          $invoice->site = $site;
          if (!($invoice->operator and $invoice->operator->loaded())) $invoice->operator = $site->operator;
        }

        switch ($format) {
          case 'preview':
            if ($form_type) {
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
            }

            $func = strtolower('generate_'.$invoice_type.'_preview');
            $summary = self::$func($invoice, (array) $ids);

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
            
            if ($form_type) {
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
            }
            break;

          case 'draft':
            $is_draft = TRUE;

          case 'final':
            set_time_limit(1800);
            $invoice->is_draft = $is_draft ? TRUE : FALSE;
            $invoice->number = $is_draft ? NULL : $invoice::create_invoice_number($invoice_type);

            $func = strtolower('generate_'.$invoice_type.'_invoice');
            $invoice->file_id = self::$func($invoice, $ids);

            if ($invoice->file_id) Notify::msg('Invoice file successfully generated.', NULL, TRUE);
            else Notify::msg('Sorry, invoice file failed to be generated. Please try again.', 'error');

            try {
              $invoice->save();
              if (is_array($ids)) foreach ($ids as $id) $invoice->set_data($form_type, $id);

              Notify::msg(($invoice->is_draft ? 'Draft invoice' : 'Invoice') . ' created.', 'success', TRUE);
              $this->request->redirect('invoices/'.$invoice->id);
            } catch (Exception $e) {
              Notify::msg('Sorry, unable to create invoice. Please try again.', 'error');
            }
            break;
        }
      } else if ($invoice_type == 'TAG') Notify::msg('No barcodes to be allocated. Skipping invoice.', 'warning');
        else Notify::msg('No data found. Skipping invoice.', 'warning');
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

    if (!$invoice->loaded()) {
      Notify::msg('No invoice found.', 'warning', TRUE);
      $this->request->redirect('invoices');
    }

    if (!$invoice->is_draft) {
      Notify::msg('Invoice already finalized.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    $form = Formo::form()
      ->add('confirm', 'text', 'Finalizing an invoice will make it permanent. Are you sure you want to finalize this draft invoice?')
      ->add('invnumber', 'input', NULL, array('required' => TRUE, 'label' => 'Invoice Number'))
      ->add('delete', 'centersubmit', 'Finalize');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $invoice->is_draft = FALSE;
      $invoice->number = $invoice::create_invoice_number($invoice->type);
      $invoice->invnumber = trim($form->invnumber->val());

      switch ($invoice->type) {
        case 'ST': $invoice->file_id = self::generate_st_invoice($invoice); break;
        case 'EXF': $invoice->file_id = self::generate_exf_invoice($invoice); break;
      }

      if ($invoice->file_id) Notify::msg('Invoice file successfully generated.', NULL, TRUE);
      else Notify::msg('Sorry, invoice file failed to be generated. Please try again.', 'error', TRUE);

      try {
        $invoice->save();
        Notify::msg('Invoice finalized.', 'success', TRUE);
        $this->request->redirect('invoices/'.$invoice->id);
      } catch (Exception $e) {
        Notify::msg('Sorry, unable to finalize invoice. Please try again.', 'error', TRUE);
        $this->request->redirect('invoices/'.$invoice->id);
      }
    }

    $table = View::factory('invoices')
      ->set('invoices', array($invoice))
      ->render();

    $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_invoice_refinalize($id) {
    if (!Auth::instance()->logged_in('management')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['management'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    $invoice = ORM::factory('invoice', $id);

    if (!$invoice->loaded()) {
      Notify::msg('No invoice found.', 'warning', TRUE);
      $this->request->redirect('invoices');
    }

    if ($invoice->is_draft) {
      Notify::msg('Invoice not yet finalized.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    if ($invoice->is_paid) {
      Notify::msg('Invoice already paid.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    $form = Formo::form()
      ->add('confirm', 'text', 'Re-finalizing an invoice may change its information. Are you sure you want to re-finalize this invoice?')
      ->add('delete', 'centersubmit', 'Re-finalize');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $invoice->is_draft = FALSE;

      switch ($invoice->type) {
        case 'ST': $invoice->file_id = self::generate_st_invoice($invoice); break;
        case 'EXF': $invoice->file_id = self::generate_exf_invoice($invoice); break;
      }

      if ($invoice->file_id) Notify::msg('Invoice file successfully generated.', NULL, TRUE);
      else Notify::msg('Sorry, invoice file failed to be generated. Please try again.', 'error', TRUE);

      try {
        $invoice->save();
        Notify::msg('Invoice re-finalized.', 'success', TRUE);
        $this->request->redirect('invoices/'.$invoice->id);
      } catch (Exception $e) {
        Notify::msg('Sorry, unable to re-finalize invoice. Please try again.', 'error', TRUE);
        $this->request->redirect('invoices/'.$invoice->id);
      }
    }

    $table = View::factory('invoices')
      ->set('invoices', array($invoice))
      ->render();

    $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_invoice_payment($id) {
    $invoice  = ORM::factory('invoice', $id);
    $payments = $invoice->payments->find_all()->as_array();

    $form = Formo::form(array('attr' => array('style' => 'display: none;')))
      ->add('number', 'input', array('label' => 'Payment Number'))
      ->add('amount', 'input', array('label' => 'Amount'))
      ->add('save', 'submit', array('label' => 'Add Payment'));

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $payment = ORM::factory('payment');
        $payment->invoice = $invoice;
        $payment->number  = $form->number->val();
        $payment->amount  = $form->amount->val();
        $payment->save();
        Notify::msg('Payment successfully added.', 'success', TRUE);
        $this->request->redirect('invoices/'.$invoice->id.'/payment');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to add invoice payment due to incorrect or missing input. Please try again.', 'error', TRUE);
      } catch (Exception $e) {
        Notify::msg('Sorry, invoice payment failed to be saved. Please try again.', 'error', TRUE);
      }
      $this->request->redirect('invoices/'.$invoice->id.'/payment');
    }

    $table = View::factory('invoices')
      ->set('invoices', array($invoice))
      ->render();

    if ($payments) $table .= View::factory('payments')
      ->set('classes', array('has-section-full'))
      ->set('payments', $payments);

    $count = count($payments);
    if ($count == 1) Notify::msg($count.' payment found');
    elseif ($count) Notify::msg($count.' payments found');
    else Notify::msg('No invoice payments found');

    $content .= SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_invoice_clearpayment($id) {
    if (!Auth::instance()->logged_in('management')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['management'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    $invoice = ORM::factory('invoice', $id);

    if (!$invoice->loaded()) {
      Notify::msg('No invoice found.', 'warning', TRUE);
      $this->request->redirect('invoices');
    }

    if ($invoice->is_draft) {
      Notify::msg('Invoice not finalized.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    if (!$payments = $invoice->payments->find_all()->as_array()) {
      Notify::msg('No payments found.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    $form = Formo::form()
      ->add('confirm', 'text', 'Clearing invoice payments will make it unpaid. Are you sure you want to clear these invoice payments?')
      ->add('delete', 'centersubmit', 'Clear');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        foreach ($payments as $payment) $payment->delete();
        $invoice->is_paid = FALSE;
        $invoice->save();
        Notify::msg('Invoice payments cleared.', 'success', TRUE);
        $this->request->redirect('invoices/'.$invoice->id);
      } catch (Exception $e) {
        Notify::msg('Sorry, unable to clear invoice payments. Please try again.', 'error', TRUE);
        $this->request->redirect('invoices/'.$invoice->id);
      }
    }

    $table = View::factory('invoices')
      ->set('invoices', array($invoice))
      ->render();

    $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_invoice_check($id) {
    $invoice = ORM::factory('invoice', $id);

    if (!$invoice->loaded()) {
      Notify::msg('No invoice found.', 'warning', TRUE);
      $this->request->redirect('invoices');
    }

    if ($invoice->is_draft) {
      Notify::msg('Invoice must be finalized to check payment.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    if (!$invoice->invnumber) {
      Notify::msg('Invoice must have an invoice number to check payment.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    try {
      $ledger  = Database::instance('ledger');
    } catch (Database_Exception $e) {
      Notify::msg('Sorry, unable to connect to invoice database. Please try again.', 'error', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    $account = DB::select('amount', 'netamount', 'paid')
      ->from('ar')
      ->where('invnumber', '=', $invoice->invnumber)
      ->execute($ledger)
      ->as_array();
    
    if ($account) extract(reset($account));
    if (!(($amount and $netamount and $paid) and ($amount == $netamount) and ($amount <= $paid))) {
      Notify::msg('Invoice has not yet been paid.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    $ledger_payments = DB::select(array('acc_trans.amount', 'amount'), 'memo')
      ->from('ar')
      ->join('acc_trans')
      ->on('ar.id', '=', 'acc_trans.trans_id')
      ->where('invnumber', '=', $invoice->invnumber)
      ->and_where('acc_trans.id', '!=', NULL)
      ->and_where('memo', '!=', NULL)
      ->execute($ledger)
      ->as_array('memo', 'amount');

    $default_payments = $invoice->payments->find_all()->as_array('number', 'amount');

    $no_payment = array();
    foreach ($default_payments as $key => $value)
      if (!$ledger_payments[$key]) $no_payment[] = $key;
      else if (SGS::amountify(abs($ledger_payments[$key])) !== SGS::amountify(abs($default_payments[$key]))) $bad_payment = $key;

//    foreach ($ledger_payments as $key => $value)
//      if (!$default_payments[$key]) $no_payment[] = $key;
//      else if (SGS::amountify(abs($default_payments[$key])) !== SGS::amountify(abs($ledger_payments[$key]))) $bad_payment = $key;

//    foreach ($default_payments as $amt) $default_amount += abs($amt);
    foreach ($ledger_payments as $amt) $ledger_amount += abs($amt);

    $diff = abs(floatval($ledger_amount) - floatval($amount));
    if (/* ($default_amount != $amount) or */ ($diff and ($diff > 0.01)) /* or
        (count($default_payments) != count($ledger_payments)) */) $no_payment[] = TRUE;

    if ($no_payment) Notify::msg('Missing payment information.', 'error', TRUE);
    if ($bad_payment) Notify::msg('Invalid payment information.', 'error', TRUE);

    if ($no_payment or $bad_payment) Notify::msg('Unable to confirm payment status.', 'warning', TRUE);
    else {
      try {
        $invoice->is_paid = TRUE;
        $invoice->save();
        Notify::msg('Invoice has successfully been paid.', 'success', TRUE);
      } catch (Exception $e) {
        Notify::msg('Sorry, unable to update invoice paid status.', 'error', TRUE);
      }
    }

    $this->request->redirect('invoices/'.$id);
  }

  private function handle_invoice_list($id = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.invoice.list');
    if ($id) {
      Session::instance()->delete('pagination.invoice.list');

      $invoice = ORM::factory('invoice', $id);
      $invoices = array($invoice);

      if ($invoice->loaded()) {
        $ids  = $invoice->get_data();

        if ($ids) {
          $func = strtolower('generate_'.$invoice->type.'_preview');
          $summary = self::$func($invoice, $ids);

          switch ($invoice->type) {
            case 'ST':
              $form_type = DB::select('form_type')
                ->from('invoice_data')
                ->where('invoice_id', '=', $invoice->id)
                ->limit(1)
                ->execute()
                ->get('form_type'); break;
            case 'EXF':
              $form_type = 'SPECS'; break;
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

          $payments = $invoice->payments->find_all()->as_array();

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

          $summary_table .= View::factory('data')
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
        }
      } else $this->request->redirect('invoices');
    }
    else {
      $operator_ids = DB::select('id', 'name')
        ->from('operators')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('type', 'checkboxes', SGS::$invoice_type, NULL, array('label' => 'Type'))
        ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'site_operatoropts')))
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')))
        ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
        ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.invoice.list');

        $type        = $form->type->val();
        $operator_id = $form->operator_id->val();
        $site_id     = $form->site_id->val();
        $from        = $form->from->val();
        $to          = $form->to->val();

        $invoices = ORM::factory('invoice');

        if ($type)        $invoices->and_where('type', 'IN', (array) $type);
        if ($operator_id) $invoices->and_where('operator_id', 'IN', (array) $operator_id);
        if ($site_id)     $invoices->and_where('site_id', 'IN', (array) $site_id);
        if ($from or $to) $invoices->and_where('created_date', 'BETWEEN', SGS::db_range($from, $to));

        Session::instance()->set('pagination.invoice.list', array(
          'type'        => $type,
          'operator_id' => $operator_id,
          'site_id'     => $site_id,
          'from'        => $from,
          'to'          => $to
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.invoice.list')) {
          $form->type->val($type = $settings['type']);
          $form->operator_id->val($operator_id = $settings['operator_id']);
          $form->site_id->val($site_id = $settings['site_id']);
          $form->from->val($from = $settings['from']);
          $form->to->val($to = $settings['to']);
        }

        $invoices = ORM::factory('invoice');

        if ($type)        $invoices->and_where('type', 'IN', (array) $type);
        if ($operator_id) $invoices->and_where('operator_id', 'IN', (array) $operator_id);
        if ($site_id)     $invoices->and_where('site_id', 'IN', (array) $site_id);
        if ($from or $to) $invoices->and_where('created_date', 'BETWEEN', SGS::db_range($from, $to));
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

    if ($payments) $table .= View::factory('payments')
      ->set('payments', $payments);

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

    if (!Auth::instance()->logged_in('management') and !$invoice->is_draft) {
      Notify::msg('Sorry, cannot delete final invoices.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$invoice->id);
    }

    $form = Formo::form()
      ->add('confirm', 'text', 'Are you sure you want to delete this '.($invoice->is_draft ? 'draft ' : '').'invoice?')
      ->add('delete', 'centersubmit', 'Delete');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $invoice->delete();
        if ($invoice->loaded()) throw new Exception();
        Notify::msg(($document->is_draft ? 'Draft invoice' : 'Invoice').' successfully deleted.', 'success', TRUE);
      } catch (Exception $e) {
        Notify::msg(($document->is_draft ? 'Draft invoice' : 'Invoice').' invoice failed to be deleted.', 'error', TRUE);
      }

      $this->request->redirect('invoices');
    }

    $table = View::factory('invoices')
      ->set('invoices', array($invoice))
      ->render();

    $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_index() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    switch ($command) {
      case 'download': return self::handle_invoice_download($id);
      case 'finalize': return self::handle_invoice_finalize($id);
      case 'refinalize': return self::handle_invoice_refinalize($id);
      case 'check': return self::handle_invoice_check($id);
      case 'delete': return self::handle_invoice_delete($id);
      case 'payment': return self::handle_invoice_payment($id);
      case 'clearpayment': return self::handle_invoice_clearpayment($id);
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
      case 'tag': return self::handle_invoice_create('TAG');
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function generate_st_preview($invoice, $data_ids = array()) {
    $data_ids = $data_ids ?: $invoice->get_data();

    switch (DB::select('form_type')
      ->from('invoice_data')
      ->where('invoice_id', '=', $invoice->id)
      ->limit(1)
      ->execute()
      ->get('form_type')) {
      case 'LDF':
        $table = 'ldf_data'; break;

      case 'TDF':
      default:
        $table = 'tdf_data'; break;
    }

    if ($data_ids) $data = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price', array(DB::expr('sum(volume)'), 'volume'))
      ->from($table)
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where($table.'.id', 'IN', (array) $data_ids)
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
    if (!($data_ids = $data_ids ?: $invoice->get_data())) {
      Notify::msg('No data found. Unable to generate invoice.', 'warning');
      return FALSE;
    }

    switch (DB::select('form_type')
      ->from('invoice_data')
      ->where('invoice_id', '=', $invoice->id)
      ->limit(1)
      ->execute()
      ->get('form_type')) {
      case 'LDF':
        $table = 'ldf_data'; break;

      case 'TDF':
      default:
        $table = 'tdf_data'; break;
    }

    $summary_data = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price', array(DB::expr('sum(volume)'), 'volume'))
      ->from($table)
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where($table.'.id', 'IN', (array) $data_ids)
      ->group_by('species_code', 'species_class', 'fob_price')
      ->execute()
      ->as_array();
    
    foreach(DB::select('barcode', array('create_date', 'scan_date'), array('code', 'species_code'), array('class', 'species_class'), 'diameter', 'length', 'volume')
      ->from($table)
      ->join('barcodes')
      ->on('barcode_id', '=', 'barcodes.id')
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where($table.'.id', 'IN', (array) $data_ids)
      ->order_by('barcode')
      ->execute() as $result) $details_data[$result['species_code']][] = $result;

    $summary_signature_page_max = 4;
    $summary_one_page_max       = 6;
    $summary_first_page_max     = 9;
    $summary_last_page_max      = 10;
    $summary_normal_page_max    = 13;

    $summary_count = count($summary_data);
    $summary_total['count'] = count($data_ids);
    foreach ($summary_data as $record) {
      foreach ($record as $key => $value) $summary_total[$key] += $value;
      $summary_total['total'] += $record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']];
    }

    $details_page_max = 40;
    foreach ($details_data as $code => $records) {
      foreach ($records as $record) foreach ($record as $key => $value) $details_total[$code][$key] += $value;
    }

    $cntr  = 0;
    $signature_remaining = TRUE;
    $total_remaining = TRUE;
    while ($cntr < $summary_count) {
      $options = array();

      $max   = NULL;
      $first = FALSE;
      $last  = FALSE;
      $one   = FALSE;
      $sign  = FALSE;

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

      if ($options['total']) $total_remaining = FALSE;

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
    }

    if ($signature_remaining) {
      $html .= View::factory('invoices/st')
        ->set('invoice', $invoice)
        ->set('options', array(
          'signature' => TRUE,
          'summary'   => $total_remaining ? TRUE : FALSE,
          'total'     => $total_remaining ? TRUE : FALSE
        ))
        ->set('total', $total_remaining ? array('summary' => $summary_total) : NULL)
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
      $file->operation      = 'D';
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

  private function generate_exf_preview($invoice, $data_ids) {
    $data_ids = $data_ids ?: $invoice->get_data();

    if ($data_ids) $data = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price', array(DB::expr('sum(volume)'), 'volume'))
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
    if (!($data_ids = $data_ids ?: $invoice->get_data())) {
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

    foreach(DB::select('barcode', array('create_date', 'scan_date'), array('code', 'species_code'), array('class', 'species_class'), array('botanic_name', 'species_botanic_name'), 'diameter', 'length', 'volume', 'grade')
      ->from('specs_data')
      ->join('barcodes')
      ->on('barcode_id', '=', 'barcodes.id')
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where('specs_data.id', 'IN', (array) $data_ids)
      ->order_by('barcode')
      ->execute() as $result) $details_data[$result['species_code']][] = $result;

    $summary_signature_page_max = 4;
    $summary_one_page_max       = 5;
    $summary_first_page_max     = 8;
    $summary_last_page_max      = 10;
    $summary_normal_page_max    = 13;

    $summary_count = count($summary_data);
    $summary_total['count'] = count($data_ids);
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
    $signature_remaining = TRUE;
    $total_remaining = TRUE;
    while ($cntr < $summary_count) {
      $options = array();

      $max   = NULL;
      $first = FALSE;
      $last  = FALSE;
      $one   = FALSE;
      $sign  = FALSE;

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

      if ($options['total']) $total_remaining = FALSE;

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
    }

    if ($signature_remaining) {
      $html .= View::factory('invoices/exf')
        ->set('invoice', $invoice)
        ->set('options', array(
          'signature' => TRUE,
          'summary'   => $total_remaining ? TRUE : FALSE,
          'total'     => $total_remaining ? TRUE : FALSE
        ))
        ->set('total', $total_remaining ? array('summary' => $summary_total) : NULL)
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
      $file->operation      = 'D';
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

  private function generate_tag_preview($invoice, $data_ids = NULL) {
    return View::factory('invoices/tag_summary')
      ->set('invoice', $invoice)
      ->set('total', array('summary' => $total))
      ->render();
  }

  private function generate_tag_invoice($invoice, $data_ids = NULL) {
    $html = View::factory('invoices/tag')
      ->set('invoice', $invoice)
      ->set('data', $set)
      ->set('operator', $invoice->operator)
      ->set('site', $invoice->site)
      ->set('options', array(
        'summary'   => TRUE,
        'break'  => FALSE,
        'styles' => TRUE,
        'info'   => TRUE,
        'fee'    => TRUE,
        'total'  => TRUE
      ));

    // generate pdf
    set_time_limit(600);

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array(
      'invoices',
      'tag',
      $invoice->operator->tin
    ));

    if ($invoice->is_draft) $newname = 'TAG_DRAFT_'.SGS::date($invoice->created_date, 'Y_m_d').'.'.$ext;
    else $newname = 'TAG_'.$invoice->number.'.'.$ext;

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
        'footer-html' => View::factory('invoices/tag')
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
      $file->operation      = 'D';
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