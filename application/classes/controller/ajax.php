<?php

class Controller_Ajax extends Controller {

  public function action_csv() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars    = explode('-', $this->request->post('id'));
    $model   = $vars[0];
    $id      = $vars[1];
    $key     = trim($vars[2]);
    $value   = trim($this->request->post('data'));

    $csv = ORM::factory('CSV', $id);

    if ((!(in_array($csv->form_type, array('SSF', 'SSFV')) and Auth::instance()->get_user()->id == 10)) and // FIXME: give better permissions for albert to edit SSF
        (strpos($key, 'barcode') === FALSE) and
        (strpos($key, 'species') === FALSE) and
        (strpos($key, 'operator') === FALSE) and
        (strpos($key, 'site') === FALSE) and !Auth::instance()->logged_in('management')) return $this->response->status(401);

    if (!$csv->loaded()) return $this->response->status(403);

    try {
      $data = $csv->values;
      $data[$key] = $value;

      $csv->values = $data;
      $csv->status = 'P';

      $csv->save();
      $csv->process();
    } catch (Exception $e) {
      return;
    }

    $this->response->body($value);
  }

  public function action_data() {
    if (!Auth::instance()->logged_in('management')) return $this->response->status(401);

    $vars    = explode('-', $this->request->post('id'));
    $model   = $vars[0];
    $id      = $vars[1];
    $key     = trim($vars[2]);
    $value   = trim($this->request->post('data'));

    $data = ORM::factory($model, $id);
    if (!$data->loaded()) return $this->response->status(403);

    try {
      switch ($key) {
        case 'create_date':
        case 'entered_date':
        case 'checked_date':
        case 'loading_date':
          $data->$key = SGS::date($value, SGS::PGSQL_DATE_FORMAT); break;

        default:
          $data->$key = $value; break;
      }
      $data->status = 'P';
      $data->save();
      $data->run_checks();
    } catch (Exception $e) {
      return;
    }

    $this->response->body($value);
  }

  public function action_details() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $id = $this->request->post('id');

    $csv   = ORM::factory('CSV', $id);
    $model = ORM::factory($csv->form_type, $csv->form_data_id);

    $fields = SGS_Form_ORM::get_fields($csv->form_type) + $model->labels();

    $view = View::factory('details')
      ->set('csv', $csv)
      ->set('errors', $csv->get_errors())
      ->set('fields', $fields);

    $this->response->body($view);
  }

  public function action_details2() {}

  public function action_update() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $id      = $this->request->post('id');
    $actions = $this->request->post('actions');
    $details = $this->request->post('details');
    $header  = $this->request->post('header');
    $hide_header_info = $this->request->post('hide_header_info');
    $hide_upload_info = $this->request->post('hide_upload_info');

    $csv   = ORM::factory('CSV', $id);
    $model = ORM::factory($csv->form_type, $csv->form_data_id);

    $fields = SGS_Form_ORM::get_fields($csv->form_type);

    $this->response->body(View::factory('csvs')
      ->set('csvs', array($csv))
      ->set('fields', $fields)
      ->set('options', array(
        'table'   => FALSE,
        'details' => $details ? TRUE: FALSE,
        'header'  => FALSE,
        'actions' => $actions ? TRUE : FALSE,
        'hide_header_info' => ($header or $hide_header_info) ? TRUE : FALSE,
        'hide_upload_info' => $hide_upload_info ? TRUE : FALSE,
      ))
      ->render());
  }

  public function action_updatedata() {
    if (!Auth::instance()->logged_in('analysis')) return $this->response->status(401);

    $id        = $this->request->post('id');
    $form_type = $this->request->post('type');
    $actions   = $this->request->post('actions');
    $details   = $this->request->post('details');
    $header    = $this->request->post('header');
    $hide_header_info = $this->request->post('hide_header_info');
    $hide_upload_info = $this->request->post('hide_upload_info');

    $data = ORM::factory($form_type, $id);

    $this->response->body(View::factory('data')
      ->set('classes', array('has-pagination'))
      ->set('form_type', $form_type)
      ->set('data', array($data))
      ->set('options', array(
        'table' => FALSE,
        'header'  => FALSE,
        'details' => $details ? TRUE : FALSE,
        'actions' => $actions ? TRUE : FALSE,
        'hide_header_info' => ($header or $hide_header_info) ? TRUE : FALSE,
        'hide_upload_info' => $hide_upload_info ? TRUE : FALSE,
      ))
      ->render());
  }

  public function action_paid() {
    if (!Auth::instance()->logged_in('management')) return $this->response->status(401);

    $vars    = explode('-', $_REQUEST['id']);
    $model   = $vars[0];
    $id      = $vars[1];

    $invoice = ORM::factory('invoice', $id);
    if (!$invoice->loaded()) return $this->response->status(403);

    $form = Formo::form(array('attr' => array('class' => 'ajax-form', 'action' => '/ajax/paid?id='.$_REQUEST['id'])))
      ->add_group('paid', 'forceselect', array('NO', 'YES'), NULL, array('required' => TRUE, 'label' => 'Paid'))
      ->add('update', 'submit', 'Update');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $paid = trim($form->paid->val());

      try {
        $invoice->is_paid = SGS::booleanify($paid);
        $invoice->save();
        return $this->response->status(200);
      } catch (Exception $e) {
        return $this->response->status(403);
      }
    }

    $content = $form->render();
    return $this->response->body($content);
  }

  public function action_blockstatus() {
    if (!Auth::instance()->logged_in('admin')) return $this->response->status(401);

    $vars = explode('-', $_REQUEST['id']);
    $id   = $vars[1];

    $block = ORM::factory('block', $id);
    if (!$block->loaded()) return $this->response->status(403);

    $form = Formo::form(array('attr' => array('class' => 'ajax-form', 'action' => '/ajax/blockstatus?id='.$_REQUEST['id'])))
      ->add_group('status', 'radios', SGS::$block_status, $block->status, array('required' => TRUE, 'label' => 'Status'))
      ->add('update', 'submit', 'Update');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $status = trim($form->status->val());

      try {
        switch ($status) {
          case 'A':
          case 'I':
          case 'R':
            $block->status = $status;
        }
        $block->save();
        return $this->response->status(200);
      } catch (Exception $e) {
        return $this->response->status(403);
      }
    }

    $content = $form->render();
    return $this->response->body($content);
  }

  public function action_status() {
    if (!Auth::instance()->logged_in('management')) return $this->response->status(401);

    $vars    = explode('-', $_REQUEST['id']);
    $model   = $vars[0];
    $id      = $vars[1];

    $data = ORM::factory($model, $id);
    if (!$data->loaded()) return $this->response->status(403);

    $form = Formo::form(array('attr' => array('class' => 'ajax-data-form', 'action' => '/ajax/status?id='.$_REQUEST['id'])))
      ->add_group('status', 'radios', SGS::$data_status, NULL, array('required' => TRUE, 'label' => 'Status'))
      ->add('comment', 'textarea', array('required' => TRUE, 'label' => 'Comment'))
      ->add('update', 'submit', 'Update');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $status  = trim($form->status->val());
      $comment = trim($form->comment->val());

      try {
        switch ($status) {
          case 'A':
          case 'R':
            $data->status = $status;
        }
        $data->status($status, $comment);
        $data->save();
        return $this->response->status(200);
      } catch (Exception $e) {
        return $this->response->status(403);
      }
    }

    $content = $form->render();
    return $this->response->body($content);
  }

  public function action_activity() {
    if (!Auth::instance()->logged_in('management')) return $this->response->status(401);

    $vars    = explode('-', $_REQUEST['id']);
    $model   = $vars[0];
    $id      = $vars[1];

    $data = ORM::factory($model, $id);
    if (!$data->loaded()) return $this->response->status(403);

    $barcode_activity = array(
//      'P' => 'Pending',
//      'I' => 'In Progress',
      'H' => 'On Hold / Investigation',
      'T' => 'Stumpage Invoiced',
      'X' => 'Export Fee Invoiced',
//      'D' => 'Declared for Export',
//      'N' => 'Inspected',
      'E' => 'Exported',
      'S' => 'Short-Shipped',
      'Y' => 'Sold Locally',
      'A' => 'Abandoned',
      'L' => 'Lost',
      'Z' => 'Seized',
      'C' => 'Commented'
    );

    $form = Formo::form(array('attr' => array('class' => 'ajax-data-form', 'action' => '/ajax/activity?id='.$_REQUEST['id'])))
      ->add_group('activity', 'select', $barcode_activity, NULL, array('required' => TRUE, 'label' => 'Activity'))
      ->add('comment', 'textarea', array('required' => TRUE, 'label' => 'Comment'))
      ->add('update', 'submit', 'Update');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $activity = trim($form->activity->val());
      $comment  = trim($form->comment->val());

      $barcode = ORM::factory('barcode', $data->barcode->id);
      if (!$barcode->loaded()) return $this->response->status(403);

      try {
        $barcode->set_activity($activity, $comment, 'admin');
        $barcode->save();
        return $this->response->status(200);
      } catch (Exception $e) {
        return $this->response->status(403);
      }
    }

    $content = $form->render();
    return $this->response->body($content);
  }

  public function action_tips() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars  = explode('-', $this->request->post('id'));

    $model = $vars[0];
    $id    = $vars[1];
    $field = $vars[2];
    $error = $vars[3];

    $csv    = ORM::factory('CSV', $id);
    $model  = ORM::factory($csv->form_type, $csv->form_data_id);
    $fields = SGS_Form_ORM::get_fields($csv->form_type) + $model->labels();

    $tips = array_filter(array($field => SGS::decode_tip($field, $error)));

    $content .= View::factory('tips')
      ->set('tips', $tips)
      ->set('csv', $csv)
      ->set('fields', $fields)
      ->render();

    $this->response->body($content);
  }

  public function action_suggestions() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars  = explode('-', $this->request->post('id'));

    $model = $vars[0];
    $id    = $vars[1];
    $field = $vars[2];
    $error = $vars[3];

    $csv    = ORM::factory('CSV', $id);
    $model  = ORM::factory($csv->form_type, $csv->form_data_id);
    $fields = SGS_Form_ORM::get_fields($csv->form_type) + $model->labels();

    $suggestions = array_filter($model->make_suggestions($csv->values, array($field => array(
      'error' => $error,
    ))));

    $content .= View::factory('suggestions')
      ->set('suggestions', $suggestions)
      ->set('csv', $csv)
      ->set('fields', $fields)
      ->render();

    $this->response->body($content);
  }

  public function action_resolutions() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars  = explode('-', $this->request->post('id'));

    $model = $vars[0];
    $id    = $vars[1];
    $field = $vars[2];
    $error = $vars[3];

    $csv    = ORM::factory('CSV', $id);
    $model  = ORM::factory($csv->form_type, $csv->form_data_id);
    $fields = SGS_Form_ORM::get_fields($csv->form_type) + $model->labels();

    $csv_duplicates = SGS::flattenify($csv->get_duplicates());
    $duplicates = array_merge(array($csv), $csv_duplicates ? ORM::factory('CSV')
      ->where('form_type', '=', $csv->form_type)
      ->and_where('id', 'IN', $csv_duplicates)
      ->find_all()
      ->as_array() : array());

    $content .= View::factory('resolutions')
      ->set('duplicates', $duplicates)
      ->set('csv', $csv)
      ->set('fields', $fields)
      ->render();

    $this->response->body($content);
  }

  public function action_process() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars  = explode('-', $this->request->post('id'));

    $model = $vars[0];
    $id    = $vars[1];

    $csv = ORM::factory('CSV', $id);
    $csv->process();

    $this->response->body($csv->status);
  }

  public function action_check() {
    if (!Auth::instance()->logged_in('analysis')) return $this->response->status(401);

    $vars  = explode('-', $this->request->post('id'));

    $model = $vars[0];
    $id    = $vars[1];

    $data = ORM::factory($model, $id);

    try {
      $data->run_checks();
    } catch (Exception $e) {
      return;
    }

    $this->response->body($data->status);
  }

  public function action_resolve() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars  = explode('-', $this->request->post('id'));

    $model  = $vars[0];
    $id     = $vars[1];
    $new_id = $vars[3];

    $csv = ORM::factory('CSV', $id);
    try {
      $csv->resolve($new_id);
    } catch (Exception $e) {
      return;
    }
  }

  public function action_siteopts() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $hide_all    = $this->request->post('hide_all') ? TRUE : FALSE;
    $operator_id = $this->request->post('operator_id');

    if ($hide_all and !$operator_id) return;

    $query = DB::select('id', 'name')
      ->from('sites')
      ->order_by('name');
    if ($operator_id) $query->where('operator_id', '=', $operator_id);

    foreach (array('' => '') + $query
      ->execute()
      ->as_array('id', 'name') as $id => $name) $output .= '<option value="'.$id.'">'.$name.'</option>';

    $this->response->body($output);
  }

  public function action_blockopts() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $site_id = $this->request->post('site_id');

    if ($site_id) foreach (array('' => '') + DB::select('id', 'name')
      ->from('blocks')
      ->where('site_id', '=', $site_id)
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name') as $id => $name) $output .= '<option value="'.$id.'">'.$name.'</option>';

    $this->response->body($output);
  }

  public function action_docarray() {
    if (!Auth::instance()->logged_in('exports')) return $this->response->status(401);

    $type  = $this->request->post('type');
    $value = $this->request->post('value');

    if (!$type or !$value) return;

    $query = DB::select()
      ->from('specs_data')
      ->join('barcode_activity', 'LEFT OUTER')
      ->on('specs_data.barcode_id', '=', 'barcode_activity.barcode_id')
      ->where('specs_data.status', '=', 'A')
      ->and_where_open()
        ->where('barcode_activity.activity', 'NOT IN', array('E', 'O', 'H', 'Y', 'A', 'L', 'S'))
        ->or_where('barcode_activity.activity', '=', NULL)
      ->and_where_close();

    switch ($type) {
      case 'specs_barcode':
        $query = $query
          ->join('document_data', 'LEFT OUTER')
          ->on('specs_data.id', '=', 'document_data.form_data_id')
          ->on('document_data.form_type', '=', DB::expr("'SPECS'"))
          ->where('specs_barcode_id', '=', SGS::lookup_barcode($value, NULL, TRUE));
        break;

      case 'specs_number':
        $query = $query->join('document_data')
          ->on('specs_data.id', '=', 'document_data.form_data_id')
          ->on('document_data.form_type', '=', DB::expr("'SPECS'"))
          ->join('documents')
          ->on('document_data.document_id', '=', 'documents.id')
          ->where('documents.id', '=', SGS::lookup_document('SPECS', $value, TRUE));
        break;

      case 'exp_barcode':
        $query = $query
          ->join('document_data', 'LEFT OUTER')
          ->on('specs_data.id', '=', 'document_data.form_data_id')
          ->on('document_data.form_type', '=', DB::expr("'SPECS'"))
          ->where('specs_barcode_id', '=', SGS::lookup_barcode($value, NULL, TRUE));
        break;

      case 'exp_number':
        $query = $query->join('document_data')
          ->on('specs_data.id', '=', 'document_data.form_data_id')
          ->on('document_data.form_type', '=', DB::expr("'SPECS'"))
          ->join('documents')
          ->on('document_data.document_id', '=', 'documents.id')
          ->where('documents.id', '=', SGS::lookup_document('EXP', $value, TRUE));
        break;
    }

    $clone = clone($query);
    $arr = array_filter($query
      ->select('origin', 'destination', 'submitted_by', 'buyer', 'loading_date')
      ->limit(1)
      ->execute()
      ->as_array());
    $specs = reset($arr);

    if ($specs) {
      $ids = (array) $clone
        ->distinct(TRUE)
        ->select('specs_data.id')
        ->execute()
        ->as_array(NULL, 'id');

      foreach (DB::select('species.code', array('sum("volume")', 'volume'))
        ->from('specs_data')
        ->join('species')
        ->on('specs_data.species_id', '=', 'species.id')
        ->where('specs_data.id', 'IN', (array) $ids)
        ->group_by('species.code')
        ->execute()
        ->as_array('code', 'volume') as $code => $volume) $prod_desc[] = $code.': '.SGS::quantitify($volume).'m3';
      $specs['product_description'] = implode(', ', $prod_desc);
      $specs['product_type'] = 'Logs';
    }

    if ($specs['origin']) $specs['origin'] = SGS::locationify($specs['origin']);
    if ($specs['destination']) $specs['destination'] = SGS::locationify($specs['destination']);

    print json_encode($specs);
  }

  public function action_specsopts() {
    if (!Auth::instance()->logged_in('analysis')) return $this->response->status(401);

    $operator_id   = $this->request->post('operator_id');
    $specs_number  = $this->request->post('specs_number');
    $specs_barcode = $this->request->post('specs_barcode');

    if ($operator_id) {
      $output = '<optgroup label=""><option value=""></option>';

      if ($specs_number) {
        $sql = "SELECT distinct number
                FROM documents
                WHERE operator_id = $operator_id AND type = 'SPECS'
                ORDER BY number";

        if ($numbers = array_filter(DB::query(Database::SELECT, $sql)
          ->execute()
          ->as_array(NULL, 'number'))) {
          $output .= '<optgroup label="Shipment Specification Number">';
          foreach ($numbers as $number) $output .= '<option value="'.$number.'">SPEC '.SGS::numberify($number).'</option>';
          $output .= '</optgroup>';
        }
      }

      if ($specs_barcode) {
        $sql = "SELECT distinct barcode
                FROM barcodes
                JOIN specs_data ON specs_data.specs_barcode_id = barcodes.id
                WHERE specs_data.operator_id = $operator_id
                ORDER BY barcode";

        if ($barcodes = array_filter(DB::query(Database::SELECT, $sql)
          ->execute()
          ->as_array(NULL, 'barcode'))) {
          $output .= '<optgroup label="Shipment Specification Barcode">';
          foreach ($barcodes as $barcode) $output .= '<option value="'.$barcode.'">'.$barcode.'</option>';
          $output .= '</optgroup>';
        }
      }

      $this->response->body($output);
    }
  }

//  public function action_expopts() {
//    if (!Auth::instance()->logged_in('analysis')) return $this->response->status(401);
//
//    $operator_id   = $this->request->post('operator_id');
//    $exp_number  = $this->request->post('exp_number');
//    $exp_barcode = $this->request->post('exp_barcode');
//
//    if ($operator_id) {
//      $output = '<optgroup label=""><option value=""></option>';
//
//      if ($exp_number) {
//        $sql = "SELECT distinct number
//                FROM documents
//                WHERE operator_id = $operator_id AND type = 'EXP'
//                ORDER BY number";
//
//        if ($numbers = array_filter(DB::query(Database::SELECT, $sql)
//          ->execute()
//          ->as_array(NULL, 'number'))) {
//          $output .= '<optgroup label="Export Permit Number">';
//          foreach ($numbers as $number) $output .= '<option value="'.$number.'">EP '.SGS::numberify($number).'</option>';
//          $output .= '</optgroup>';
//        }
//      }
//
//      if ($exp_barcode) {
//        $sql = "SELECT distinct barcode
//                FROM barcodes
//                JOIN specs_data ON specs_data.exp_barcode_id = barcodes.id
//                WHERE specs_data.operator_id = $operator_id
//                ORDER BY barcode";
//
//        if ($barcodes = array_filter(DB::query(Database::SELECT, $sql)
//          ->execute()
//          ->as_array(NULL, 'barcode'))) {
//          $output .= '<optgroup label="Export Permit Barcode">';
//          foreach ($barcodes as $barcode) $output .= '<option value="'.$barcode.'">'.$barcode.'</option>';
//          $output .= '</optgroup>';
//        }
//      }
//
//      $this->response->body($output);
//    }
//  }

  public function action_expopts() {
    if (!Auth::instance()->logged_in('analysis')) return $this->response->status(401);

    $operator_id = $this->request->post('operator_id');
    $exp_number  = $this->request->post('exp_number');
    $exp_barcode = $this->request->post('exp_barcode');

    if ($operator_id) {
      $output = '<optgroup label=""><option value=""></option>';

      if ($exp_number) {
        $sql = "SELECT distinct number
                FROM documents
                WHERE operator_id = $operator_id AND type = 'EXP'
                ORDER BY number";

        if ($numbers = array_filter(DB::query(Database::SELECT, $sql)
          ->execute()
          ->as_array(NULL, 'number'))) {
          $output .= '<optgroup label="Export Permit Number">';
          foreach ($numbers as $number) $output .= '<option value="'.$number.'">EP '.SGS::numberify($number).'</option>';
          $output .= '</optgroup>';
        }
      }

      if ($exp_barcode) {
        $sql = "SELECT distinct barcode
                FROM barcodes
                JOIN specs_data ON specs_data.exp_barcode_id = barcodes.id
                WHERE specs_data.operator_id = $operator_id
                ORDER BY barcode";

        if ($barcodes = array_filter(DB::query(Database::SELECT, $sql)
          ->execute()
          ->as_array(NULL, 'barcode'))) {
          $output .= '<optgroup label="Export Permit Barcode">';
          foreach ($barcodes as $barcode) $output .= '<option value="'.$barcode.'">'.$barcode.'</option>';
          $output .= '</optgroup>';
        }
      }

      $this->response->body($output);
    }
  }

  public function action_wbopts() {
    if (!Auth::instance()->logged_in('analysis')) return $this->response->status(401);

    $operator_id = $this->request->post('operator_id');
    $wb_barcode  = $this->request->post('wb_barcode');

    if ($operator_id) {
      $output = '<optgroup label=""><option value=""></option>';

      if ($wb_barcode) {
        $sql = "SELECT distinct barcode
                FROM barcodes
                JOIN wb_data ON wb_data.wb_barcode_id = barcodes.id
                WHERE wb_data.operator_id = $operator_id
                ORDER BY barcode";

        if ($barcodes = array_filter(DB::query(Database::SELECT, $sql)
          ->execute()
          ->as_array(NULL, 'barcode'))) {
          $output .= '<optgroup label="Waybill Barcode">';
          foreach ($barcodes as $barcode) $output .= '<option value="'.$barcode.'">'.$barcode.'</option>';
          $output .= '</optgroup>';
        }
      }

      $this->response->body($output);
    }
  }

  public function action_autocompletebarcode() {
    $term = trim($this->request->post('term') ?: $this->request->query('term'));
    print json_encode(array_filter(array_unique(array_values(SGS::suggest_barcode(strtoupper($term), array(), 'barcode', FALSE, 2, 0, 6, 5, 0)))));
  }

}
