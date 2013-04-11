<?php

class Controller_Barcodes extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('barcodes')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['barcodes'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }
  }

  public function action_index() {
    $id = $this->request->param('id');
    $command = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $id      = NULL;
      $command = $id;
    }

    if ($id) {
      Session::instance()->delete('pagination.barcode.list');

      $barcode = ORM::factory('barcode', $id);
      $barcodes = array($barcode);

      if ($command == 'edit') {
        $barcode = ORM::factory('barcode', $id);
        $form = Formo::form()
          ->orm('load', $barcode)
          ->add('save', 'submit', 'Update Print Job');

        if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
          try {
            $barcode->save();
            Notify::msg('Print job successfully updated.', 'success', TRUE);
            $this->request->redirect('barcodes/'.$barcode->id);
          } catch (Database_Exception $e) {
            Notify::msg('Sorry, unable to save print job due to incorrect or missing input. Please try again.', 'error');
          } catch (Exception $e) {
            Notify::msg('Sorry, print job failed to be saved. Please try again.', 'error');
          }
        }
      } else {
        $data = array();

        foreach (array('SSF', 'TDF', 'LDF', 'SPECS') as $key) {
          $search = ORM::factory($key);
          foreach (array_keys($search->table_columns()) as $field) if (strpos($field, 'barcode') !== FALSE) $search->or_where($field, '=', $barcode->id);
          foreach ($search = $search->find_all() as $item) $data[$key][] = $item;
        }

        foreach ($data as $type => $items) switch ($type) {
          case 'SSF':
          case 'TDF':
          case 'LDF':
          case 'SPECS':
            $data_table .= View::factory('data')
              ->set('classes', array('has-section'))
              ->set('form_type', $type)
              ->set('data', $items)
              ->set('options', array('header' => FALSE, 'hide_header_info' => TRUE))
              ->render();
        }
      }
    }
    else {
      if (!Request::$current->query()) Session::instance()->delete('pagination.barcode.list');

      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add_group('type', 'checkboxes', SGS::$barcode_type, NULL, array('label' => 'Type'))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.barcode.list');
        $site_id = $form->site_id->val();
        $type    = $form->type->val();

        $barcodes = ORM::factory('barcode');
        if ($site_id) {
          $barcodes = $barcodes
            ->join('printjobs')
            ->on('printjob_id', '=', 'printjobs.id')
            ->and_where('printjobs.site_id', 'IN', (array) $site_id);
        }
        if ($type) $barcodes->where('type', 'IN', (array) $type);

        Session::instance()->set('pagination.barcode.list', array(
          'site_id' => $site_id,
          'type'    => $type
        ));

      }
      else {
        if ($settings = Session::instance()->get('pagination.barcode.list')) {
          $form->site_id->val($site_id = $settings['site_id']);
          $form->type->val($type = $settings['type']);
        }

        $barcodes = ORM::factory('barcode');
        if ($site_id) {
          $barcodes = $barcodes
            ->join('printjobs')
            ->on('printjob_id', '=', 'printjobs.id')
            ->and_where('printjobs.site_id', 'IN', (array) $site_id);
        }
        if ($type) $barcodes->where('type', 'IN', (array) $type);
      }

      if ($barcodes) {
        $clone = clone($barcodes);
        $pagination = Pagination::factory(array(
          'items_per_page' => 50,
          'total_items' => $clone->find_all()->count()));

        $barcodes = $barcodes
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $barcodes->order_by($sort);
        $barcodes = $barcodes->order_by('barcode')
          ->find_all()
          ->as_array();

        if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' barcodes found');
        elseif ($pagination->total_items) Notify::msg($pagination->total_items.' barcodes found');
        else Notify::msg('No barcodes found');
      }
    }

    if ($barcodes) $table .= View::factory('barcodes')
      ->set('classes', array('has-pagination'))
      ->set('barcodes', $barcodes);

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $data_table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  function action_list() {
    return $this->action_index();
  }

  public function action_query() {
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

    $form = Formo::form(array('attr' => array('class' => 'search')))
      ->add('barcode', 'input', array('label' => 'Barcode', 'rules' => array(array('min_length', array(':value', 3))), 'attr' => array('class' => 'barcode-text autocomplete-barcode-barcode')))
      ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'site_operatoropts')))
      ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')))
      ->add_group('similarity', 'select', array('exact' => 'Exact', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'), 'medium', array('label' => 'Similarity'))
      ->add('submit', 'submit', 'Query');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $barcode     = trim(strtoupper($form->barcode->val()));
      $similarity  = $form->similarity->val();
      $operator_id = $form->operator_id->val();
      $site_id     = $form->site_id->val();

      if ($site_id)     $args['sites.id'] = $site_id;
      if ($operator_id) $args['operators.id'] = $operator_id;

      switch ($similarity) {
        case 'exact': $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', TRUE, 2, 0.8, 1, 25, 0); break;
        case 'high': $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', TRUE, 2, 0.5, 5, 25, 0); break;
        case 'low': $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', TRUE, 2, 0.1, 10, 25, 0); break;

        case 'medium':
        default: $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', TRUE, 2, 0.3, 7, 25, 0); break;
      }

      if ($suggestions) {
        Notify::msg(count($suggestions).' results found.', 'success');

        $barcodes = ORM::factory('barcode')
          ->where('id', 'IN', (array) $suggestions)
          ->find_all()
          ->as_array();
      } else Notify::msg('No results found.');

      if ($barcodes) $table = View::factory('barcodes')
        ->set('classes', array('has-pagination'))
        ->set('barcodes', $barcodes);
    }

    $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')
      ->set('content', $content)
      ->set('left', $left);

    $this->response->body($view);
  }

}
