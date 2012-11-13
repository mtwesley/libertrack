<?php

class Controller_Admin extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('admin')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['admin'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_operators() {
    $id = $this->request->param('id');

    $operator = ORM::factory('operator', $id);
    $form = Formo::form(array('attr' => array('style' => $id || $_POST ? '' : 'display: none;')))
      ->orm('load', $operator, array('sites', 'user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Operator' : 'Add a New Operator'
      ));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      try {
        $operator->save();
        if ($id) Notify::msg('Operator successfully updated.', 'success', TRUE);
        else Notify::msg('Operator successfully added.', 'success', TRUE);

        $this->request->redirect('admin/operators');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save operator due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, operator failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $operator->find_all()->count()));

      $operators = ORM::factory('operator')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $operators->order_by($sort);
      $operators = $operators->order_by('name')
        ->find_all()
        ->as_array();

      $table .= View::factory('operators')
        ->set('classes', array('has-pagination'))
        ->set('operators', $operators);

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' operator found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' operators found');
      else Notify::msg('No operators found');
    }

    $content .= $id || $_POST ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_sites() {
    $id = $this->request->param('id');

    $site = ORM::factory('site', $id);
    $form = Formo::form(array('attr' => array('style' => $id || $_POST ? '' : 'display: none;')))
      ->orm('load', $site, array('blocks', 'printjobs', 'invoices', 'user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Site' : 'Add a New Site'
      ));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      try {
        $site->save();
        if ($id) Notify::msg('Site successfully updated.', 'success', TRUE);
        else Notify::msg('Site successfully added.', 'success', TRUE);

        $this->request->redirect('admin/sites');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save site due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, site failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $site->find_all()->count()));

      $sites = ORM::factory('site')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $sites->order_by($sort);
      $sites = $sites->order_by('name')
        ->find_all()
        ->as_array();

      $table .= View::factory('sites')
        ->set('classes', array('has-pagination'))
        ->set('sites', $sites);

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' site found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' sites found');
      else Notify::msg('No sites found');
    }

    $content .= $id || $_POST ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_blocks() {
    $id = $this->request->param('id');

    $block = ORM::factory('block', $id);
    $form = Formo::form(array('attr' => array('style' => $id || $_POST ? '' : 'display: none;')))
      ->orm('load', $block, array('user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Block' : 'Add a New Block'
      ))
      ->order(array('name' => 0));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      try {
        $block->save();
        if ($id) Notify::msg('Block successfully updated.', 'success', TRUE);
        else Notify::msg('Block successfully added.', 'success', TRUE);

        $this->request->redirect('admin/blocks');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save block due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, block failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $block->find_all()->count()));

      $blocks = ORM::factory('block')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $blocks->order_by($sort);
      $blocks = $blocks->order_by('site_id')
        ->order_by('name')
        ->find_all()
        ->as_array();

      $table = View::factory('blocks')
        ->set('classes', array('has-pagination'))
        ->set('blocks', $blocks);

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' block found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' blocks found');
      else Notify::msg('No blocks found');
    }

    $content .= $id || $_POST ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }


  public function action_species() {
    $id = $this->request->param('id');

    $species = ORM::factory('species', $id);
    $form = Formo::form(array('attr' => array('style' => $id || $_POST ? '' : 'display: none;')))
      ->orm('load', $species, array('user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Species' : 'Add a New Species'
      ));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      try {
        $species->save();
        if ($id) Notify::msg('Species successfully updated.', 'success', TRUE);
        else Notify::msg('Species successfully added.', 'success', TRUE);

        $this->request->redirect('admin/species');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save species due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, species failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $species->find_all()->count()));

      $speciess = ORM::factory('species')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $speciess->order_by($sort);
      $speciess = $speciess->order_by('trade_name')
        ->find_all()
        ->as_array();

      $table .= View::factory('species')
        ->set('classes', array('has-pagination'))
        ->set('species', $speciess);

      if ($pagination->total_items) Notify::msg($pagination->total_items.' species found');
      else Notify::msg('No species found');
    }

    $content .= $id || $_POST ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}
