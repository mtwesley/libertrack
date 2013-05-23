<?php

class Controller_Exports extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('exports')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['exports'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    Session::instance()->write();
  }

  private function generate_exp_preview($document, $data_ids) {
    if (!($data_ids = $data_ids ?: $document->get_data())) {
      Notify::msg('No data found. Unable to generate document.', 'warning');
      return FALSE;
    }

    $total_quantity = DB::select(array(DB::expr('sum(volume)'), 'sum'))
      ->from('specs_data')
      ->where('id', 'IN', (array) $data_ids)
      ->execute()
      ->get('sum');

    $total_fob = DB::select(array(DB::expr('sum(volume * fob_price)'), 'sum'))
      ->from('specs_data')
      ->join('species')
      ->on('specs_data.species_id', '=', 'species.id')
      ->where('specs_data.id', 'IN', (array) $data_ids)
      ->execute()
      ->get('sum');

    return View::factory('documents/exp_summary')
      ->set('document', $document)
      ->set('total_quantity', $total_quantity)
      ->set('total_fob', $total_fob)
      ->render();
  }

  private function generate_exp_document($document, $data_ids) {
    if (!($data_ids = $data_ids ?: $document->get_data())) {
      Notify::msg('No data found. Unable to generate document.', 'warning');
      return FALSE;
    }

    $total_quantity = DB::select(array(DB::expr('sum(volume)'), 'sum'))
      ->from('specs_data')
      ->where('id', 'IN', (array) $data_ids)
      ->execute()
      ->get('sum');

    $total_fob = DB::select(array(DB::expr('sum(volume * fob_price)'), 'sum'))
      ->from('specs_data')
      ->join('species')
      ->on('specs_data.species_id', '=', 'species.id')
      ->where('specs_data.id', 'IN', (array) $data_ids)
      ->execute()
      ->get('sum');

    $total_items = count($data_ids);

    $qr_array = array(
      'EP NUMBER'    => $document->number,
      'SPEC BARCODE' => $document->values['specs_barcode'],
      'EXPORTER'     => $document->operator->name,
      'BUYER'        => $document->values['buyer'],
      'ORIGIN'       => $document->values['origin'],
      'DESTINATION'  => $document->values['destination'],
      'VESSEL'       => $document->values['vessel'],
      'QUANTITY'     => SGS::quantitify($total_quantity).'m3',
      'FOB'          => '$'.SGS::amountify($total_fob),
    );

    $hash   = $document->get_hash();
    $secret = strtoupper(substr($hash, mt_rand(0, strlen($hash) - 16), 16));

    $disclaimer = "
FOR VALIDATION, PLEASE CONTACT SGS-LIBERFOR:
PHONE: +231886410110
EMAIL: MYERS.TUWEH@SGS.COM

VALIDATION: $secret";

    foreach ($qr_array as $key => $value) $qr_text .= "$key: $value\n";
    $qr_text .= $disclaimer;

    $tempname = tempnam(sys_get_temp_dir(), 'qr_').'.png';
    try {
      QRcode::png($qr_text, $tempname, QR_ECLEVEL_L, 2, 1);
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to generate validation image. Please try again.', 'error');
    }

    if ($document->is_draft === FALSE) try {
      $qr = ORM::factory('qrcode');
      $qr->qrcode = $hash;
      $qr->save();
      $document->qrcode = $qr;
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err), 'error', TRUE);
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to create validation code. Please try again.', 'error');
    }

    $html .= View::factory('documents/exp')
      ->set('options', array(
        'info'    => TRUE,
        'styles'  => TRUE,
        'break'   => FALSE
      ))
      ->set('qr_image', $tempname)
      ->set('document', $document)
      ->set('total_quantity', $total_quantity)
      ->set('total_fob', $total_fob)
      ->set('total_items', $total_items)
      ->render();

    // generate pdf
    set_time_limit(600);

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array('exp'));

    if ($document->is_draft) $newname = 'EXP_DRAFT_'.SGS::date('now', 'Y_m_d').'.'.$ext;
    else $newname = 'EXP_'.$document->number.'.'.$ext;

    $version = 0;
    $testname = $newname;
    while (file_exists(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname)) {
      $newname = substr($testname, 0, strrpos($testname, '.'.$ext)).'_'.($version++).'.'.$ext;
    }

    if (!is_dir(DOCPATH.$newdir) and !mkdir(DOCPATH.$newdir, 0777, TRUE)) {
      Notify::msg('Sorry, cannot access documents folder. Check file access capabilities with the site administrator and try again.', 'error');
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
        'disable-smart-shrinking' => TRUE,
        'footer-html' => View::factory('documents/exp')
          ->set('options', array(
            'header' => FALSE,
            'footer' => TRUE,
            'break'  => FALSE))
          ->render()
      ));
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to generate document. If this problem continues, contact the system administrator.', 'error');
      return FALSE;
    }

    try {
      $file = ORM::factory('file');
      $file->name = $newname;
      $file->type = 'application/pdf';
      $file->size = filesize($fullname);
      $file->operation      = 'D';
      $file->operation_type = 'DOC';
      $file->content_md5    = md5_file($fullname);
      $file->path = DIRECTORY_SEPARATOR.str_replace(DOCROOT, '', DOCPATH).$newdir.DIRECTORY_SEPARATOR.$newname;
      $file->save();
      return $file->id;
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err).' ('.$file->name.')', 'error');
      return FALSE;
    }
  }

  private function generate_specs_preview($document, $data_ids) {
    if (!($data_ids = $data_ids ?: $document->get_data())) {
      Notify::msg('No data found. Unable to generate document.', 'warning');
      return FALSE;
    }

    $total  = DB::select(array(DB::expr('sum(volume)'), 'sum'))
      ->from('specs_data')
      ->where('id', 'IN', (array) $data_ids)
      ->execute()
      ->get('sum');

    return View::factory('documents/specs_summary')
      ->set('document', $document)
      ->set('total', $total)
      ->render();
  }

  private function generate_specs_document($document, $data_ids) {
    if (!($data_ids = $data_ids ?: $document->get_data())) {
      Notify::msg('No data found. Unable to generate document.', 'warning');
      return FALSE;
    }

    $records = ORM::factory('SPECS')
      ->where('specs.id', 'IN', (array) $data_ids)
      ->join('barcodes')
      ->on('barcode_id', '=', 'barcodes.id')
      ->order_by('barcode', 'ASC')
      ->find_all()
      ->as_array();

    $page_max = 28;
    $total = DB::select(array(DB::expr('sum(volume)'), 'sum'))
      ->from('specs_data')
      ->where('id', 'IN', (array) $data_ids)
      ->execute()
      ->get('sum');

    $qr_array = array(
      'SPEC NUMBER'  => $document->number,
      'SPEC BARCODE' => $document->values['specs_barcode'],
      'EXPORTER'     => $document->operator->name,
      'ORIGIN'       => $document->values['origin'],
      'DESTINATION'  => $document->values['destination'],
      'QUANTITY'     => SGS::quantitify($total).'m3',
    );

    $hash   = $document->get_hash();
    $secret = strtoupper(substr($hash, mt_rand(0, strlen($hash) - 16), 16));

    $disclaimer = "
FOR VALIDATION, PLEASE CONTACT SGS-LIBERFOR:
PHONE: +231886410110
EMAIL: MYERS.TUWEH@SGS.COM

VALIDATION: $secret";

    foreach ($qr_array as $key => $value) $qr_text .= "$key: $value\n";
    $qr_text .= $disclaimer;

    $tempname = tempnam(sys_get_temp_dir(), 'qr_').'.png';
    try {
      QRcode::png($qr_text, $tempname, QR_ECLEVEL_L, 2, 1);
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to generate validation image. Please try again.', 'error');
    }

    if ($document->is_draft === FALSE) try {
      $qr = ORM::factory('qrcode');
      $qr->qrcode = $hash;
      $qr->save();
      $document->qrcode = $qr;
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err), 'error', TRUE);
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to create validation code. Please try again.', 'error');
    }

    $cntr   = 0;
    $styles = TRUE;
    while ($cntr < count($data_ids)) {
      $last = count($data_ids) > ($cntr + $max);
      $max  = $page_max;

      $set  = array_slice($records, $cntr, $max);
      $html .= View::factory('documents/specs')
        ->set('data', $set)
        ->set('options', array(
          'info'    => TRUE,
          'details' => TRUE,
          'styles'  => $styles ? TRUE : FALSE,
          'total'   => $last ? FALSE : TRUE
        ))
        ->set('qr_image', $tempname)
        ->set('document', $document)
        ->set('cntr', $cntr)
        ->set('total', $total)
        ->render();

      $cntr += $max;
      $styles = FALSE;
    }

    // generate pdf
    set_time_limit(600);

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array(
      'specs',
    ));

    if ($document->is_draft) $newname = 'SPECS_DRAFT_'.SGS::date('now', 'Y_m_d').'.'.$ext;
    else $newname = 'SPECS_'.$document->number.'.'.$ext;

    $version = 0;
    $testname = $newname;
    while (file_exists(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname)) {
      $newname = substr($testname, 0, strrpos($testname, '.'.$ext)).'_'.($version++).'.'.$ext;
    }

    if (!is_dir(DOCPATH.$newdir) and !mkdir(DOCPATH.$newdir, 0777, TRUE)) {
      Notify::msg('Sorry, cannot access documents folder. Check file access capabilities with the site administrator and try again.', 'error');
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
        'disable-smart-shrinking' => TRUE,
        'footer-html' => View::factory('documents/specs')
          ->set('options', array(
            'header' => FALSE,
            'footer' => TRUE,
            'break'  => FALSE))
          ->render()
      ));
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to generate document. If this problem continues, contact the system administrator.', 'error');
      return FALSE;
    }

    try {
      $file = ORM::factory('file');
      $file->name = $newname;
      $file->type = 'application/pdf';
      $file->size = filesize($fullname);
      $file->operation      = 'D';
      $file->operation_type = 'DOC';
      $file->content_md5    = md5_file($fullname);
      $file->path = DIRECTORY_SEPARATOR.str_replace(DOCROOT, '', DOCPATH).$newdir.DIRECTORY_SEPARATOR.$newname;
      $file->save();
      return $file->id;
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err).' ('.$file->name.')', 'error');
      return FALSE;
    }
  }

  private function handle_document_create($document_type) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.exports.document.data');

    $operator_ids = DB::select('id', 'name')
      ->from('operators')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $form = Formo::form()
      ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'specs_operatoropts specs_barcode exp_operatoropts')));

    switch ($document_type) {
      case 'EXP':
        $form->add_group('specs_barcode', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts specs_specsinputs')));
        $form->add('origin', 'input', NULL, array('required' => TRUE, 'label' => 'Origin', 'attr' => array('class' => 'origininput')));
        $form->add('destination', 'input', NULL, array('required' => TRUE, 'label' => 'Destination', 'attr' => array('class' => 'destinationinput')));
        $form->add('product_type', 'input', NULL, array('required' => TRUE, 'label' => 'Product Type', 'attr' => array('class' => 'product_typeinput')));
        $form->add('product_description', 'textarea', NULL, array('required' => TRUE, 'label' => 'Product Description', 'attr' => array('class' => 'product_descriptioninput')));
        $form->add('eta_date', 'input', NULL, array('required' => TRUE, 'label' => 'ETA', 'attr' => array('class' => 'dpicker eta_dateinput', 'id' => 'eta-dpicker')));
        $form->add('inspection_date', 'input', NULL, array('label' => 'Inspection Date', 'attr' => array('class' => 'dpicker inspection_dateinput', 'id' => 'inspection-dpicker')));
        $form->add('inspection_location', 'input', NULL, array('label' => 'Inspection Location', 'attr' => array('class' => 'inspection_locationinput')));
        $form->add('vessel', 'input', NULL, array('required' => TRUE, 'label' => 'Vessel', 'attr' => array('class' => 'vesselinput')));
        $form->add('buyer', 'input', NULL, array('required' => TRUE, 'label' => 'Buyer', 'attr' => array('class' => 'buyerinput')));
        $form->add('buyer_contact', 'input', NULL, array('label' => 'Buyer Contact', 'attr' => array('class' => 'buyer_contactinput')));
        $form->add('buyer_address', 'textarea', NULL, array('required' => TRUE, 'label' => 'Buyer Address', 'attr' => array('class' => 'buyer_addressinput')));
        $form->add('buyer_email', 'input', NULL, array('label' => 'Buyer Email', 'attr' => array('class' => 'buyer_emailinput')));
        $form->add('buyer_phone', 'input', NULL, array('label' => 'Buyer Phone', 'attr' => array('class' => 'buyer_phoneinput')));
        $form->add('fob_price_notes', 'textarea', 'In line with international market prices for similar goods.', array('label' => 'FOB Price Verification'));
        $form->add('notes', 'textarea', NULL, array('label' => 'Notes'));
        break;

      case 'SPECS':
        $form->add_group('specs_barcode', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts specs_specsinputs')));
        $form->add('origin', 'input', NULL, array('required' => TRUE, 'label' => 'Origin', 'attr' => array('class' => 'origininput')));
        $form->add('destination', 'input', NULL, array('required' => TRUE, 'label' => 'Destination', 'attr' => array('class' => 'destinationinput')));
        $form->add('buyer', 'input', NULL, array('required' => TRUE, 'label' => 'Buyer', 'attr' => array('class' => 'buyerinput')));
        $form->add('loading_date', 'input', NULL, array('required' => TRUE, 'label' => 'Expected Loading Date', 'attr' => array('class' => 'dpicker loading_date-dpicker loading_dateinput')));
        $form->add('contract_number', 'input', NULL, array('label' => 'Contract Number', 'attr' => array('class' => 'contract_numberinput')));
        $form->add('submitted_by', 'input', NULL, array('required' => TRUE, 'label' => 'Submitted By', 'attr' => array('class' => 'submitted_byinput')));
        break;
    }

    $form->add('created', 'input', SGS::date('now', SGS::US_DATE_FORMAT), array('label' => 'Date Created', 'required' => TRUE, 'attr' => array('class' => 'dpicker', 'id' => 'created-dpicker')));
    $form->add('format', 'radios', 'preview', array(
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
      Session::instance()->delete('pagination.exports.document.data');
      $format      = $form->format->val();
      $created     = $form->created->val();
      $operator_id = $form->operator_id->val();

      switch ($document_type) {
        case 'EXP':
          $specs_barcode = $form->specs_barcode->val();
          $values = array(
            'specs_barcode'   => $specs_barcode,
            'origin'          => $form->origin->val(),
            'destination'     => $form->destination->val(),
            'product_type'    => $form->product_type->val(),
            'product_description' => $form->product_description->val(),
            'eta_date'        => $form->eta_date->val(),
            'inspection_date' => $form->inspection_date->val(),
            'inspection_location' => $form->inspection_location->val(),
            'vessel'          => $form->vessel->val(),
            'buyer'           => $form->buyer->val(),
            'buyer_contact'   => $form->buyer_contact->val(),
            'buyer_address'   => $form->buyer_address->val(),
            'buyer_email'     => $form->buyer_email->val(),
            'buyer_phone'     => $form->buyer_phone->val(),
            'notes'           => $form->notes->val(),
            'fob_price_notes' => $form->fob_price_notes->val(),
          );
          break;

        case 'SPECS':
          $specs_barcode = $form->specs_barcode->val();
          $values = array(
            'specs_barcode'   => $specs_barcode,
            'origin'          => $form->origin->val(),
            'destination'     => $form->destination->val(),
            'buyer'           => $form->buyer->val(),
            'loading_date'    => $form->loading_date->val(),
            'contract_number' => $form->contract_number->val(),
            'submitted_by'    => $form->submitted_by->val(),
          );
          break;
      }

      Session::instance()->set('pagination.exports.document.data', array(
        'operator_id'   => $operator_id,
        'specs_barcode' => $specs_barcode,
        // 'exp_number'    => $exp_number,
        'format'        => $format,
        'created'       => $created,
        'values'        => $values
      ));
    }
    else if ($settings = Session::instance()->get('pagination.exports.document.data')) {
      $form->operator_id->val($operator_id = $settings['operator_id']);
      switch ($document_type) {
        case 'EXP':
          $form->specs_barcode->val($specs_barcode = $settings['specs_barcode']);
          $form->origin->val($values['origin'] = $settings['values']['origin']);
          $form->destination->val($values['destination'] = $settings['values']['destination']);
          $form->product_type->val($values['product_type'] = $settings['values']['product_type']);
          $form->product_description->val($values['product_description'] = $settings['values']['product_description']);
          $form->eta_date->val($values['eta_date'] = $settings['values']['eta_date']);
          $form->inspection_date->val($values['inspection_date'] = $settings['values']['inspection_date']);
          $form->inspection_location->val($values['inspection_location'] = $settings['values']['inspection_location']);
          $form->vessel->val($values['vessel'] = $settings['values']['vessel']);
          $form->buyer->val($values['buyer'] = $settings['values']['buyer']);
          $form->buyer_contact->val($values['buyer_contact'] = $settings['values']['buyer_contact']);
          $form->buyer_address->val($values['buyer_address'] = $settings['values']['buyer_address']);
          $form->buyer_email->val($values['buyer_email'] = $settings['values']['buyer_email']);
          $form->buyer_phone->val($values['buyer_phone'] = $settings['values']['buyer_phone']);
          $form->notes->val($values['notes'] = $settings['values']['notes']);
          $form->fob_price_notes->val($values['fob_price_notes'] = $settings['values']['fob_price_notes']);
          break;

        case 'SPECS':
          $form->specs_barcode->val($specs_barcode = $settings['specs_barcode']);
          $form->origin->val($values['origin'] = $settings['values']['origin']);
          $form->destination->val($values['destination'] = $settings['values']['destination']);
          $form->buyer->val($values['buyer'] = $settings['values']['buyer']);
          $form->loading_date->val($values['loading_date'] = $settings['values']['loading_date']);
          $form->contract_number->val($values['contract_number'] = $settings['values']['contract_number']);
          $form->submitted_by->val($values['submitted_by'] = $settings['values']['submitted_by']);
          break;
      }

      $form->format->val($format = $settings['format']);
      $form->created->val($created = $settings['created']);
    }

    if ($format) {
      switch ($document_type) {
        case 'EXP':
          $form_type = 'SPECS';
          $ids = DB::select('specs_data.id','barcodes.barcode')
            ->distinct(TRUE)
            ->from('specs_data')
            ->join('document_data', 'LEFT OUTER')
            ->on('specs_data.id', '=', 'document_data.form_data_id')
            ->on('document_data.form_type', '=', DB::expr("'SPECS'"))
            ->join('documents', 'LEFT OUTER')
            ->on('document_data.document_id', '=', 'documents.id')
            ->join('barcode_activity', 'LEFT OUTER')
            ->on('specs_data.barcode_id', '=', 'barcode_activity.barcode_id')
            ->join('invoice_data')
            ->on('specs_data.id', '=', 'invoice_data.form_data_id')
            ->on('invoice_data.form_type', '=', DB::expr("'SPECS'"))
            ->join('invoices')
            ->on('invoice_data.invoice_id', '=', 'invoices.id')
            ->join('barcodes')
            ->on('specs_data.barcode_id', '=', 'barcodes.id')
            ->where('specs_data.operator_id', '=', $operator_id)
            ->and_where('specs_data.status', '=', 'A')
            ->and_where('specs_data.specs_barcode_id', '=', SGS::lookup_barcode($specs_barcode, NULL, TRUE))
            ->and_where_open()
              ->where('barcode_activity.activity', 'NOT IN', array('E', 'H', 'Y', 'A', 'L', 'S'))
              ->or_where('barcode_activity.activity', '=', NULL)
            ->and_where_close()
            ->and_where_open()
              ->where('documents.type', '<>', 'EXP')
              ->or_where('documents.id', '=', NULL)
            ->and_where_close()
            ->and_where('invoices.type', '=', 'EXF')
            ->and_where('invoices.is_paid', '=', TRUE)
            ->order_by('barcode')
            ->execute()
            ->as_array(NULL, 'id');
          break;

        case 'SPECS':
          $form_type = 'SPECS';
          $ids = DB::select('specs_data.id','barcodes.barcode')
            ->distinct(TRUE)
            ->from('specs_data')
            ->join('document_data', 'LEFT OUTER')
            ->on('specs_data.id', '=', 'document_data.form_data_id')
            ->on('document_data.form_type', '=', DB::expr("'SPECS'"))
            ->join('documents', 'LEFT OUTER')
            ->on('document_data.document_id', '=', 'documents.id')
            ->join('barcode_activity', 'LEFT OUTER')
            ->on('specs_data.barcode_id', '=', 'barcode_activity.barcode_id')
            ->join('barcodes')
            ->on('specs_data.barcode_id', '=', 'barcodes.id')
            ->where('specs_data.operator_id', '=', $operator_id)
            ->and_where('specs_data.status', '=', 'A')
            ->and_where('specs_data.specs_barcode_id', '=', SGS::lookup_barcode($specs_barcode, NULL, TRUE))
            ->and_where_open()
              ->where('barcode_activity.activity', 'NOT IN', array('E', 'H', 'Y', 'A', 'L', 'S'))
              ->or_where('barcode_activity.activity', '=', NULL)
            ->and_where_close()
            ->and_where_open()
              ->where('documents.type', '<>', 'SPECS')
              ->or_where('documents.id', '=', NULL)
            ->and_where_close()
            ->order_by('barcode')
            ->execute()
            ->as_array(NULL, 'id');
          break;
      }

      if ($form_type and $ids) {
        $operator = ORM::factory('operator', $operator_id);

        $document = ORM::factory('document');
        $document->operator = $operator;
        $document->type     = $document_type;
        $document->is_draft = $is_draft ? TRUE : FALSE;
        $document->number   = $is_draft ? NULL : $document::create_document_number($document_type);
        $document->values   = (array) $values;
        $document->created_date = SGS::date($created, SGS::PGSQL_DATE_FORMAT, TRUE);

        switch ($format) {
          case 'preview':
            $data = ORM::factory($form_type)
              ->where(strtolower($form_type).'.id', 'IN', (array) $ids)
              ->join('barcodes')
              ->on(strtolower($form_type).'.barcode_id', '=', 'barcodes.id')
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

            $func = strtolower('generate_'.$document_type.'_preview');
            $summary = self::$func($document, (array) $ids);

            unset($info);
            if ($specs_barcode) {
              $sample = reset($data);
              $info['specs'] = array(
                'number'  => $sample->specs_number,
                'barcode' => $sample->specs_barcode->barcode
              );
            }

            $header = View::factory('data')
              ->set('form_type', $form_type)
              ->set('data', $data)
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
            $document->is_draft = $is_draft ? TRUE : FALSE;
            $document->number   = $is_draft ? NULL : $document::create_document_number($document_type);

            $func = strtolower('generate_'.$document_type.'_document');
            $document->file_id = self::$func($document, $ids);

            if ($document->file_id) Notify::msg('Document file successfully generated.', NULL, TRUE);
            else Notify::msg('Sorry, document file failed to be generated. Please try again.', 'error');

            try {
              $document->save();
              foreach ($ids as $id) $document->set_data($form_type, $id);

              Notify::msg(($document->is_draft ? 'Draft document' : 'Document') . ' created.', 'success', TRUE);
              $this->request->redirect('exports/documents/'.$document->id);
            } catch (Exception $e) {
              Notify::msg('Sorry, unable to create document. Please try again.', 'error');
            }
            break;

        }
      } else Notify::msg('No data found. Skipping document.', 'warning');
    }

    if ($form) $content .= $form;

    $content .= $header;
    $content .= $summary;
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_document_list($id = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.exports.documents.list');
    if ($id) {
      Session::instance()->delete('pagination.exports.documents.list');

      $document  = ORM::factory('document', $id);
      $documents = array($document);

      if ($document->loaded()) {
        $ids  = $document->get_data();
        $func = strtolower('generate_'.$document->type.'_preview');
        $summary = self::$func($document, (array) $ids);

        switch ($document->type) {
          case 'EXP': $form_type = 'SPECS'; break;
          case 'SPECS': $form_type = 'SPECS'; break;
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
          ->set('operator', $document->operator->loaded() ? $document->operator : NULL)
          ->set('site', $document->site->loaded() ? $document->site : NULL)
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
          ->set('operator', $document->operator->loaded() ? $document->operator : NULL)
          ->set('site', $document->site->loaded() ? $document->site : NULL)
          ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
          ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
          ->set('options', array(
            'links'  => FALSE,
            'header' => FALSE,
            'hide_header_info' => TRUE
          ))
          ->render();
      } else $this->request->redirect('exports/documents');
    }
    else {
      $documents = ORM::factory('document');

      $operator_ids = DB::select('id', 'name')
        ->from('operators')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('type', 'checkboxes', array('SPECS' => SGS::$document_type['SPECS'], 'EXP' => SGS::$document_type['EXP']), NULL, array('label' => 'Type'))
        ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'specs_operatoropts specs_barcode exp_operatoropts exp_barcode')))
        ->add_group('specs_barcode', 'select', array(), NULL, array('label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')))
        // ->add_group('exp_barcode', 'select', array(), NULL, array('label' => 'Export Permit', 'attr' => array('class' => 'expopts')))
        ->add('submit', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.exports.documents.list');
        $type          = $form->type->val();
        $operator_id   = $form->operator_id->val();
        $specs_barcode = $form->specs_barcode->val();
        // $exp_barcode   = $form->exp_barcode->val();

        Session::instance()->set('pagination.exports.documents.list', array(
          'type'          => $type,
          'operator_id'   => $operator_id,
          'specs_barcode' => $specs_barcode,
          // 'exp_barcode'   => $exp_barcode
        ));

        if ($type)        $documents->and_where('type', 'IN', (array) $type);
        if ($operator_id) $documents->and_where('operator_id', 'IN', (array) $operator_id);

        if (Valid::is_barcode($specs_barcode)) $documents->and_where('values', 'LIKE', '%"specs_barcode";s:'.strlen($specs_barcode).':"'.$specs_barcode.'"%');
        // if (Valid::numeric($exp_number)) $documents->and_where('values', 'LIKE', '%"exp_number";s:'.strlen($exp_number).':"'.$exp_number.'"%');
      }
      else if ($settings = Session::instance()->get('pagination.exports.documents.list')) {
        $form->type->val($type = $settings['type']);
        $form->operator_id->val($operator_id = $settings['operator_id']);
        $form->specs_barcode->val($specs_barcode = $settings['specs_barcode']);
        // $form->exp_barcode->val($exp_barcode = $settings['exp_barcode']);

        if ($type)    $documents->and_where('type', 'IN', (array) $type);
        if ($site_id) $documents->and_where('site_id', 'IN', (array) $site_id);

        if (Valid::is_barcode($specs_barcode)) $documents->and_where('values', 'LIKE', '%"specs_barcode";s:'.strlen($specs_barcode).':"'.$specs_barcode.'"%');
        // if (Valid::numeric($exp_number)) $documents->and_where('values', 'LIKE', '%"exp_number";s:'.strlen($exp_number).':"'.$exp_number.'"%');
      }

      if ($documents) {
        $clone = clone($documents);
        $pagination = Pagination::factory(array(
          'items_per_page' => 20,
          'total_items' => $clone->find_all()->count()));

        $documents = $documents
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $documents->order_by($sort);
        $documents = $documents->order_by('number', 'DESC')
          ->find_all()
          ->as_array();
      }
    }

    if ($documents) {
      $table = View::factory('documents')
        ->set('mode', 'exports')
        ->set('classes', array('has-pagination'))
        ->set('documents', $documents)
        ->render();
      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' document found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' documents found');
    }
    else Notify::msg('No documents found');

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

  private function handle_document_validate() {
    if (!Request::$current->query()) Session::instance()->delete('pagination.exports.documents.list');
    $form = Formo::form()
      ->add('reference', 'input', NULL, array('label' => 'Reference Number', 'required' => TRUE))
      ->add('validation', 'input', NULL, array('label' => 'Validation Code', 'required' => TRUE))
      ->add('submit', 'submit', 'Validate');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) { do {
      Session::instance()->delete('pagination.exports.documents.list');

      $reference  = $form->reference->val();
      $validation = strtolower($form->validation->val());

      $parts  = explode(' ', $reference);
      $type   = $parts[0];
      $number = $parts[1];

      if (!in_array($length = strlen($validation), array(16, 17, 64))) break;
      if (!in_array($type, array('EP', 'SPEC'))) break;
      if (strlen($number) != 6) break;

      $query = DB::select('documents.id')
        ->from('documents')
        ->join('qrcodes')
        ->on('documents.qrcode_id', '=', 'qrcodes.id');
      switch ($length) {
        case 16: $query->where('qrcode', 'LIKE', '%'.$validation.'%'); break;
        case 17: $query->where(DB::expr('position(\''.substr($validation, 1).'\' in qrcode)'), '=', substr($validation, 1, 1)); break;
        case 64: $query->where('qrcode', '=', $validation); break;
        default: break 2;
      }

      $id = $query
        ->where('number', '=', (int) $number)
        ->execute()
        ->get('id');

      $document = ORM::factory('document', $id);
      if ($document->loaded()) {
        $documents = array($document);
        $valid     = TRUE;

        $data_ids = $document->get_data();
        $func     = strtolower('generate_'.$document->type.'_preview');
        $summary  = self::$func($document, (array) $data_ids);

        switch ($document->type) {
          case 'EXP': $form_type = 'SPECS'; break;
          case 'SPECS': $form_type = 'SPECS'; break;
        }

        $summary_data = ORM::factory($form_type)
          ->where(strtolower($form_type).'.id', 'IN', (array) $data_ids)
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
          ->set('operator', $document->operator->loaded() ? $document->operator : NULL)
          ->set('site', $document->site->loaded() ? $document->site : NULL)
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
          ->set('operator', $document->operator->loaded() ? $document->operator : NULL)
          ->set('site', $document->site->loaded() ? $document->site : NULL)
          ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
          ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
          ->set('options', array(
            'links'  => FALSE,
            'header' => FALSE,
            'hide_header_info' => TRUE
          ))
          ->render();
      } else break; } while (false);

      if ($valid) Notify::msg('Valid match found.', 'success');
      else Notify::msg('Invalid match. Not found.', 'error');

    }

    if ($documents) $table = View::factory('documents')
      ->set('mode', 'exports')
      ->set('classes', array('has-pagination'))
      ->set('documents', $documents)
      ->render();

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

  private function handle_create() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    switch ($command) {
      case 'specs': return self::handle_document_create('SPECS');
      case 'exp': return self::handle_document_create('EXP');
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_document_asycuda($id) {
    $document = ORM::factory('document', $id);

    if (!($data_ids = $data_ids ?: $document->get_data())) {
      Notify::msg('No data found. Unable to generate document.', 'warning');
      return FALSE;
    }

    if (!$document->loaded()) {
      Notify::msg('No document found.', 'warning', TRUE);
      $this->request->redirect('documents');
    }

    if ($document->type !== 'EXP') {
      Notify::msg('Document must be of correct type before creating ASYCUDA text file.', 'warning', TRUE);
      $this->request->redirect('documents/'.$id);
    }

    if ($document->is_draft) {
      Notify::msg('Document must be finalized before creating ASYCUDA text file.', 'warning', TRUE);
      $this->request->redirect('documents/'.$id);
    }

    $text = '';

    // general info

    $format   = '%3.3s%17.17s%10.10s%10.10s%9.9s%20.20s%3.3s %1.1s%2.2s%3.3s';

    $flag_sgs = 'EB1';
    $num_sgs  = 'LRBUC110079760011'; // TODO: what is this number?
    $date_sgs = SGS::date($document->created_date, SGS::US_DATE_CORRECT_FORMAT);
    $dec_cod  = '';
    $exp_cod  = '400065004'; // SAMPLE
    $bol_number   = ''; // SAMPLE
    $cod_currency = 'USD';
    $mot_cod  = 'S';
    $cty_exp  = 'US'; // SAMPLE
    $tel_del  = 'FOB';

    $text .= sprintf($format, $flag_sgs, $num_sgs, $date_sgs, $dec_cod, $exp_cod, $bol_number, $cod_currency, $mot_cod, $cty_exp, $tel_del);

    // summary info

    $summary_info  = array();
    $species_order = array();

    $summary_info  = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price', array('sum("volume")', 'volume'), array('count("specs_data"."id")', 'count'))
      ->from('specs_data')
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where('specs_data.id', 'IN', (array) $data_ids)
      ->group_by('species_code', 'species_class', 'fob_price')
      ->execute()
      ->as_array();

    $format = '%1.1s%17.17s%04u%011u%08u%2.2s%-15.15s%15.15s%15.15s%2.2s%4.4s%1.1s%12.12s';

    $flag_item   = 'I';
    $num_sgs     = 'LRBUC110079760011'; // TODO: what is this number?
    $line_number = 0;

    foreach ($summary_info as $info) {
      $line_number++;
      $hs_code = '70511000000'; // SAMPLE
      $quantity = str_pad($info['count'], 8, '0', STR_PAD_LEFT);
      $sta_unit = str_pad('', 2, '0', STR_PAD_LEFT);
      $fob_value = str_pad(number_format($info['fob_price'], 2, ',', ''), 15, '0', STR_PAD_LEFT);
      $freight_value = str_pad(number_format(0, 2, ',', ''), 15, '0', STR_PAD_LEFT);
      $unit_price = str_pad(number_format(0, 2, ',', ''), 15, '0', STR_PAD_LEFT);
      $cty_origine_code = 'LR'; // SAMPLE
      $tax_rat = str_pad('', 3, '0', STR_PAD_LEFT);
      $species_class = $info['species_class'];
      $supplementary_unit_value = str_pad(number_format($info['volume'], 3, ',', ''), 12, '0', STR_PAD_LEFT);

      $species_order[] = $info['species_code'];
      $text .= "\n".sprintf($format, $flag_item, $num_sgs, $line_number, $hs_code, $quantity, $sta_unit, $fob_value, $freight_value, $unit_price, $cty_origine_code, $tax_rat, $species_class, $supplementary_unit_value);
    }

    // details info

    $details_info = array();
    foreach(DB::select('barcode', array('create_date', 'scan_date'), array('code', 'species_code'), array('class', 'species_class'), array('botanic_name', 'species_botanic_name'), 'top_min', 'top_max', 'bottom_min', 'bottom_max', array(DB::expr('((top_min + top_max + bottom_min + bottom_max) / 4)'), 'diameter'), 'length', 'volume', 'grade')
      ->from('specs_data')
      ->join('barcodes')
      ->on('barcode_id', '=', 'barcodes.id')
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where('specs_data.id', 'IN', (array) $data_ids)
      ->order_by('barcode')
      ->execute() as $result) $details_info[$result['species_code']][] = $result;

    $format = '%1.1s%05u%12.12s%-4.4s%-3.3s%-3.3s%-3.3s%-3.3s%-6.6s%-4.4s%7.7s';

    $flag_log = 'L';
    $num_log  = 0;

    foreach ($species_order as $species_order_code)
    foreach ($details_info[$species_order_code] as $info) {
      $num_log++;
      $log_id = str_pad($info['barcode'], 12, '0', STR_PAD_LEFT);
      $species_code = $info['species_code'];
      $d1 = str_pad($info['bottom_max'], 3, '0', STR_PAD_LEFT);
      $d2 = str_pad($info['bottom_min'], 3, '0', STR_PAD_LEFT);
      $d3 = str_pad($info['top_max'], 3, '0', STR_PAD_LEFT);
      $d4 = str_pad($info['top_min'], 3, '0', STR_PAD_LEFT);
      $length = str_pad(number_format($info['length'], 2, ',', ''), 6, '0', STR_PAD_LEFT);
      $atibt = '4444'; // SAMPLE
      $volume = str_pad(number_format($info['volume'], 3, ',', ''), 7, '0', STR_PAD_LEFT);

      $text .= "\n".sprintf($format, $flag_log, $num_log, $log_id, $species_code, $d1,$d2, $d3, $d4, $length, $atibt, $volume);
    }

    try {
      $tempname = tempnam(sys_get_temp_dir(), 'ASYCUDA_').'.txt';
      $fullname .= 'sgsasycu_ep_'.$document->number.'.txt';

      file_put_contents($tempname, $text);

      $this->response->send_file($tempname, $fullname, array('mime_type' => 'text/plain', 'delete' => TRUE));
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to download ASYCUDA text file. Please try again.', 'error');
    }
  }

  private function handle_document_finalize($id) {
    $document = ORM::factory('document', $id);

    if (!$document->loaded()) {
      Notify::msg('No document found.', 'warning', TRUE);
      $this->request->redirect('documents');
    }

    if (!$document->is_draft) {
      Notify::msg('Document already finalized.', 'warning', TRUE);
      $this->request->redirect('documents/'.$id);
    }

    $form = Formo::form()
      ->add('confirm', 'text', 'Finalizing a document will make it permanent. Are you sure you want to finalize this draft document?')
      ->add('delete', 'submit', 'Finalize');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $document->is_draft = FALSE;
      $document->number = $document::create_document_number($document->type);

      switch ($document->type) {
        case 'SPECS': $document->file_id = self::generate_specs_document($document, $document->get_data()); break;
        case 'EXP':   $document->file_id = self::generate_exp_document($document, $document->get_data()); break;
      }

      if ($document->file_id) Notify::msg('Document file successfully generated.', NULL, TRUE);
      else Notify::msg('Sorry, document file failed to be generated. Please try again.', 'error', TRUE);

      try {
        $document->save();
        Notify::msg('Document finalized.', 'success', TRUE);
        $this->request->redirect('exports/documents/'.$document->id);
      } catch (Exception $e) {
        Notify::msg('Sorry, unable to create document. Please try again.', 'error', TRUE);
        $this->request->redirect('exports/documents/'.$document->id);
      }
    }

    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_document_delete($id) {
    $document = ORM::factory('document', $id);

    if (!$document->loaded()) {
      Notify::msg('No document found.', 'warning', TRUE);
      $this->request->redirect('exports/documents');
    }

    if (!$document->is_draft) {
      Notify::msg('Sorry, cannot delete final documents.', 'warning', TRUE);
      $this->request->redirect('exports/documents/'.$document->id);
    }

    $form = Formo::form()
      ->add('confirm', 'text', 'Are you sure you want to delete this draft document?')
      ->add('delete', 'submit', 'Delete');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $document->delete();
        if ($document->loaded()) throw new Exception();
        Notify::msg('Draft document successfully deleted.', 'success', TRUE);
      } catch (Exception $e) {
        Notify::msg('Draft document failed to be deleted.', 'error', TRUE);
      }

      $this->request->redirect('exports/documents');
    }

    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_documents() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    if (!is_numeric($id)) {
      $command = $id;
      $id      = NULL;
    }

    switch ($command) {
      case 'create': return self::handle_create();
      case 'validate': return self::handle_document_validate();
      case 'finalize': return self::handle_document_finalize($id);
      case 'asycuda': return self::handle_document_asycuda($id);
      case 'delete': return self::handle_document_delete($id);
      case 'list':
      default: return self::handle_document_list($id);
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}