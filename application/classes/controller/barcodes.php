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
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.barcode.list');
        $site_id = $form->site_id->val();

        $barcodes = ORM::factory('barcode');
        if ($site_id) {
          $barcodes = $barcodes
            ->join('printjobs')
            ->on('printjob_id', '=', 'printjobs.id')
            ->and_where('printjobs.site_id', 'IN', (array) $site_id);
        }

        Session::instance()->set('pagination.barcode.list', array(
          'site_id' => $site_id,
        ));

      }
      else {
        if ($settings = Session::instance()->get('pagination.barcode.list')) {
          $form->site_id->val($site_id = $settings['site_id']);
        }

        $barcodes = ORM::factory('barcode');
        if ($site_id) {
          $barcodes = $barcodes
            ->join('printjobs')
            ->on('printjob_id', '=', 'printjobs.id')
            ->and_where('printjobs.site_id', 'IN', (array) $site_id);
        }
      }

      if ($barcodes) {
        $clone = clone($barcodes);
        $pagination = Pagination::factory(array(
          'items_per_page' => 20,
          'total_items' => $clone->find_all()->count()));

        $barcodes = $barcodes
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $barcodes->order_by($sort);
        $barcodes = $barcodes->order_by('barcode')
          ->find_all()
          ->as_array();

        if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' print job found');
        elseif ($pagination->total_items) Notify::msg($pagination->total_items.' print jobs found');
        else Notify::msg('No print jobs found');
      }
    }

    if ($barcodes) $table .= View::factory('barcodes')
      ->set('classes', array('has-pagination'))
      ->set('barcodes', $barcodes);

    if ($form) $content .= $form->render();
    $content .= $table;
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
      ->add('submit', 'submit', 'Query');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $barcode     = trim(strtoupper($form->barcode->val()));
      $operator_id = $form->operator_id->val();
      $site_id     = $form->site_id->val();

      if ($site_id)     $args['sites.id'] = $site_id;
      if ($operator_id) $args['operators.id'] = $operator_id;

      if (!$suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'barcode', TRUE, 2, 0.3, 3, 20, 0)) Notify::msg('No results found.');
      else if (count($suggestions) == 1) Notify::msg('1 result found.', 'success');
      else Notify::msg(count($suggestions). ' results found.', 'success');

      $barcodes = array();
      foreach ($suggestions as $suggestion) $barcodes[] = ORM::factory('barcode')
        ->where('barcode', '=', $suggestion)
        ->find_all()
        ->as_array();

      $barcodes = SGS::flattenify($barcodes);
      if ($barcodes) $table = View::factory('barcodes')
        ->set('barcodes', $barcodes);
    }

    $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')
      ->set('content', $content)
      ->set('left', $left);

    $this->response->body($view);
  }

}
