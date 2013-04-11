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

  private function generate_specs_preview($data_ids) {
    $sample = ORM::factory('SPECS', reset($data_ids));
    $total  = DB::select(array(DB::expr('sum(volume)'), 'sum'))
      ->from('specs_data')
      ->where('id', 'IN', (array) $data_ids)
      ->execute()
      ->get('sum');

    $info = array(
      'specs_barcode' => $sample->specs_barcode->barcode,
      'exp_barcode'   => $sample->exp_barcode->barcode,
      'operator_tin'  => $sample->operator->tin,
      'operator_name' => $sample->operator->name,
      'origin'        => $sample->origin,
      'destination'   => $sample->destination,
      'loading_date'  => $sample->loading_date,
      'buyer'         => $sample->buyer,
      'submitted_by'  => $sample->submitted_by,
      'create_date'   => $sample->create_date,
    );

    return View::factory('documents/specs_summary')
      ->set('info', $info)
      ->set('total', $total)
      ->render();
  }

  private function generate_specs_document($document, $data_ids) {
    if (!($data_ids ?: $document->get_data())) {
      Notify::msg('No data found. Unable to generate document.', 'warning');
      return FALSE;
    }

    $item = ORM::factory('SPECS', reset($data_ids));

    $records = ORM::factory('SPECS')
      ->where('specs.id', 'IN', (array) $data_ids)
      ->join('barcodes')
      ->on('barcode_id', '=', 'barcodes.id')
      ->order_by('barcode', 'ASC')
      ->find_all()
      ->as_array();

    $page_count = 0;
    $page_max   = 28;

    $total = DB::select(array(DB::expr('sum(volume)'), 'sum'))
      ->from('specs_data')
      ->where('id', 'IN', (array) $data_ids)
      ->execute()
      ->get('sum');

    $cntr   = 0;
    $styles = TRUE;
    while ($cntr < count($data_ids)) {
      $max = $page_max;
      $set = array_slice($records, $cntr, $max);
      $html .= View::factory('documents/specs')
        ->set('data', $set)
        ->set('options', array(
          'info'    => TRUE,
          'details' => TRUE,
          'styles'  => $styles ? TRUE : FALSE,
          'total'   => count($data_ids) > ($cntr + $max) ? FALSE : TRUE
        ))
        ->set('info', array(
          'is_draft'      => $document->is_draft,
          'specs_barcode' => $item->specs_barcode->barcode,
          'specs_number'  => $document->number,
          'exp_barcode'   => $item->exp_barcode->barcode,
          'exp_number'    => $item->exp_number,
          'operator_tin'  => $item->operator->tin,
          'operator_name' => $item->operator->name,
          'origin'        => $item->origin,
          'destination'   => $item->destination,
          'loading_date'  => $item->loading_date,
          'buyer'         => $item->buyer,
          'submitted_by'  => $item->submitted_by,
          'create_date'   => $item->create_date,
        ))
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
    else $newname = 'SPECS_'.$specs_number.'.'.$ext;

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
          ->set('page', $page)
          ->set('page_count', $page_count)
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
      $file->operation      = 'A';
      $file->operation_type = 'SPECS';
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
      ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'specs_operatoropts exp_operatoropts')));

    switch ($document_type) {
      case 'EXP':
        $form->add_group('specs_barcode', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts specs_specsopts')));
        $form->add('origin', 'input', NULL, array('label' => 'Origin', 'attr' => array('class' => 'origininput')));
        $form->add('destination', 'input', NULL, array('label' => 'Destination', 'attr' => array('class' => 'destinationinput')));
        $form->add('product_type', 'input', NULL, array('label' => 'Product Type', 'attr' => array('class' => 'product_typeinput')));
        $form->add('product_description', 'input', NULL, array('label' => 'Product Description', 'attr' => array('class' => 'product_descriptioninput')));
        $form->add('eta_date', 'input', NULL, array('label' => 'ETA', 'attr' => array('class' => 'dpicker eta_dateinput')));
        $form->add('inspection_date', 'input', NULL, array('label' => 'Inspection Date', 'attr' => array('class' => 'dpicker inspection_dateinput')));
        $form->add('vessel', 'input', NULL, array('label' => 'Vessel', 'attr' => array('class' => 'vesselinput')));
        $form->add('buyer_name', 'input', NULL, array('label' => 'Buyer', 'attr' => array('class' => 'buyer_nameinput')));
        $form->add('buyer_contact', 'input', NULL, array('label' => 'Buyer Contact', 'attr' => array('class' => 'buyer_contactinput')));
        $form->add('buyer_address', 'input', NULL, array('label' => 'Buyer Address', 'attr' => array('class' => 'buyer_addressinput')));
        $form->add('buyer_email', 'input', NULL, array('label' => 'Buyer Email', 'attr' => array('class' => 'buyer_emailinput')));
        $form->add('buyer_phone', 'input', NULL, array('label' => 'Buyer Phone', 'attr' => array('class' => 'buyer_phoneinput')));
        break;

      case 'SPECS':
        $form->add_group('exp_number', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Export Permit', 'attr' => array('class' => 'expopts exp_expopts')));
        $form->add('origin', 'input', NULL, array('label' => 'Origin', 'attr' => array('class' => 'origininput')));
        $form->add('destination', 'input', NULL, array('label' => 'Destination', 'attr' => array('class' => 'destinationinput')));
        $form->add('buyer_name', 'input', NULL, array('label' => 'Buyer', 'attr' => array('class' => 'buyer_nameinput')));
        $form->add('loading_date', 'input', NULL, array('label' => 'Expected Loading Date', 'attr' => array('class' => 'dpicker loading_dateinput')));
        $form->add('contract_number', 'input', NULL, array('label' => 'Contract Number', 'attr' => array('class' => 'contract_numberinput')));
        $form->add('submitted_by', 'input', NULL, array('label' => 'Submitted By', 'attr' => array('class' => 'submitted_byinput')));
        break;
    }

    $form->add('created', 'input', SGS::date('now', SGS::US_DATE_FORMAT), array('label' => 'Date Created', 'required' => TRUE, 'attr' => array('class' => 'dpicker', 'id' => 'created-dpicker')));
    $form->add('format', 'radios', 'preview', array(
        'options' => array(
          'preview' => 'Preview',
          'draft'   => 'Draft Copy',
          'final'   => 'Final Copy'),
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
            'origin'          => $form->origin->val(),
            'destination'     => $form->destination->val(),
            'product_type'    => $form->product_type->val(),
            'product_description' => $form->product_description->val(),
            'eta_date'        => $form->eta_date->val(),
            'inspection_date' => $form->inspection_date->val(),
            'vessel'          => $form->vessel->val(),
            'buyer_name'      => $form->buyer_name->val(),
            'buyer_contact'   => $form->buyer_contact->val(),
            'buyer_address'   => $form->buyer_address->val(),
            'buyer_email'     => $form->buyer_email->val(),
            'buyer_phone'     => $form->buyer_phone->val(),
          );
          break;

        case 'SPECS':
          $exp_number = $form->exp_number->val();
          $values = array(
            'origin' => $form->origin->val(),
            'destination' => $form->destination->val(),
            'buyer_name' => $form->buyer_name->val(),
            'loading_date' => $form->loading_date->val(),
            'contract_number' => $form->contract_number->val(),
            'submitted_by' => $form->submitted_by->val(),
          );
          break;
      }

      Session::instance()->set('pagination.exports.document.data', array(
        'operator_id'   => $operator_id,
        'specs_barcode' => $specs_barcode,
        'exp_number'    => $exp_number,
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
          $form->vessel->val($values['vessel'] = $settings['values']['vessel']);
          $form->buyer_name->val($values['buyer_name'] = $settings['values']['buyer_name']);
          $form->buyer_contact->val($values['buyer_contact'] = $settings['values']['buyer_contact']);
          $form->buyer_address->val($values['buyer_address'] = $settings['values']['buyer_address']);
          $form->buyer_email->val($values['buyer_email'] = $settings['values']['buyer_email']);
          $form->buyer_phone->val($values['buyer_phone'] = $settings['values']['buyer_phone']);
          break;

        case 'SPECS':
          $form->exp_number->val($exp_number = $settings['exp_number']);
          $form->origin->val($values['origin'] = $settings['values']['origin']);
          $form->destination->val($values['destination'] = $settings['values']['destination']);
          $form->buyer_name->val($values['buyer_name'] = $settings['values']['buyer_name']);
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
          $ids = DB::select('specs_data.id')
            ->from('specs_data')
            ->join('document_data', 'LEFT OUTER')
            ->on('specs_data.id', '=', 'document_data.form_data_id')
            ->on('document_data.form_type', '=', DB::expr("'SPECS'"))
            ->where('specs_data.operator_id', '=', $operator_id)
            ->and_where('document_data.form_data_id', '=', NULL)
            ->where('specs_data.status', '=', 'A')
            ->where('specs_barcode_id', '=', SGS::lookup_barcode($specs_barcode, NULL, TRUE))
            ->join('barcodes')
            ->on('specs_data.barcode_id', '=', 'barcodes.id')
            ->order_by('barcode')
            ->execute()
            ->as_array(NULL, 'id');
          break;

        case 'SPECS':
          $form_type = 'SPECS';
          $ids = DB::select('document_data.form_data_id')
            ->from('document_data.document_data')
            ->join(DB::expr('"document_data" AS "documented_data"'), 'LEFT OUTER')
            ->on('document_data.form_data_id', '=', 'documented_data.form_data_id')
            ->on('documented_data.form_type', '=', DB::expr("'SPECS'"))
            ->join('specs_data')
            ->on('document_data.form_data_id', '=', 'specs_data.id')
            ->join('barcodes')
            ->on('specs_data.barcode_id', '=', 'barcodes.id')
            ->where('document_data.documents_id', '=', SGS::lookup_document('SPECS', $exp_number))
            ->where('document_data.form_type', '=', 'SPECS')
            ->where('documented_data.form_data_id', '=', NULL)
            ->order_by('barcode');
          break;
      }

      if ($form_type and $ids) {
        $operator = ORM::factory('operator', $operator_id);

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

            $func = strtolower('generate_'.$document_type.'_preview');
            $summary = self::$func((array) $ids);

            unset($info);
            if ($specs_barcode) {
              $sample = reset($data);
              $info['specs'] = array(
                'number'  => $sample->specs_number,
                'barcode' => $sample->specs_barcode->barcode
              );
              if (Valid::numeric($specs_barcode)) $info['exp'] = array(
                'number'  => $sample->exp_number,
                'barcode' => $sample->exp_barcode->barcode
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
            $document = ORM::factory('document');

            if ($operator->loaded()) $document->operator = $operator;

            $document->type = $document_type;
            $document->is_draft = $is_draft ? TRUE : FALSE;
            $document->number = $is_draft ? NULL : $document::create_document_number($document_type);
            $document->created_date = SGS::date($created, SGS::PGSQL_DATE_FORMAT, TRUE);

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
        $summary = self::$func((array) $ids);

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
          if (Valid::numeric($specs_number)) $info['exp'] = array(
            'number'  => $sample->exp_number,
            'barcode' => $sample->exp_barcode->barcode
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
          ->set('operator', $operator_id ? $operator : NULL)
          ->set('site', $document->site->loaded() ? $document->site : NULL)
          ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
          ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
          ->set('options', array(
            'links'  => FALSE,
            'header' => FALSE,
            'hide_header_info' => TRUE
          ))
          ->render();
      } else $this->request->redirect('export/documents');
    }
    else {
      $documents = ORM::factory('document');

      $operator_ids = DB::select('id', 'name')
        ->from('operators')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('type', 'checkboxes', array('SPECS' => SGS::$document_type['SPECS'], 'EXP' => SGS::$document_type['EXP']), NULL, array('label' => 'Type', 'attr' => array('SPECS' => 'specs_operatoropts exp_operatoropts')))
        ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'specs_operatoropts exp_operatoropts')))
        ->add_group('specs_barcode', 'select', array(), NULL, array('label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')))
        ->add_group('exp_barcode', 'select', array(), NULL, array('label' => 'Export Permit', 'attr' => array('class' => 'expopts')))
        ->add('submit', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.exports.documents.list');
        $type          = $form->type->val();
        $operator_id   = $form->operator_id->val();
        $specs_barcode = $form->specs_barcode->val();
        $exp_barcode   = $form->exp_barcode->val();

        Session::instance()->set('pagination.exports.documents.list', array(
          'type'          => $type,
          'operator_id'   => $operator_id,
          'specs_barcode' => $specs_barcode,
          'exp_barcode'   => $exp_barcode
        ));
      }
      else if ($settings = Session::instance()->get('pagination.exports.documents.list')) {
        $form->type->val($type = $settings['type']);
        $form->operator_id->val($operator_id = $settings['operator_id']);
        $form->specs_barcode->val($specs_barcode = $settings['specs_barcode']);
        $form->exp_barcode->val($exp_barcode = $settings['exp_barcode']);

        if ($type)    $documents->and_where('type', 'IN', (array) $type);
        if ($site_id) $documents->and_where('site_id', 'IN', (array) $site_id);
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

  private function handle_document_finalize($id) {
    $document = ORM::factory('document', $id);
    if (!($document->loaded() and $document->is_draft)) {
      Notify::msg('Document already finalized.', 'warning', TRUE);
      $this->request->redirect('exports/documents/'.$id);
    }

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
      Notify::msg('Sorry, unable to create document. Please try again.', 'error');
      $this->request->redirect('exports/documents/'.$document->id);
    }
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
      case 'finalize': return self::handle_document_finalize($id);
      case 'delete': return self::handle_document_delete($id);
      case 'list':
      default: return self::handle_document_list($id);
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}