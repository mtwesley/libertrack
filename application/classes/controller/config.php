<?php

class Controller_Config extends Controller {

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
    $form = Formo::form(array('attr' => array('style' => ($id or $_POST) ? '' : 'display: none;')))
      ->orm('load', $operator, array('sites', 'user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Operator' : 'Add a New Operator'
      ));

    if ($id) $form->remove('tin');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $operator->save();
        if ($id) Notify::msg('Operator successfully updated.', 'success', TRUE);
        else Notify::msg('Operator successfully added.', 'success', TRUE);

        $this->request->redirect('config/operators');
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

    $content .= ($id or $_POST) ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_sites() {
    if (!Request::$current->query()) Session::instance()->delete('pagination.sites.list');

    $id   = $this->request->param('id');
    $form = $this->request->post('form');

    $site = ORM::factory('site', $id);
    $add_form = Formo::form(array('attr' => array('style' => ($id or $form == 'add_form') ? '' : 'display: none;')))
      ->orm('load', $site, array('blocks', 'printjobs', 'invoices', 'user_id', 'timestamp'), true)
      ->add('form', 'hidden', 'add_form')
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Site' : 'Add a New Site'
      ));

    if ($id) $add_form->remove('name');

    $operator_ids = DB::select('id', 'name')
      ->from('operators')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $filter_form = Formo::form()
      ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator'))
      ->add('form', 'hidden', 'filter_form')
      ->add('filter', 'submit', 'Filter');

    if ($form == 'add_form' and $add_form->sent($_REQUEST) and $add_form->load($_REQUEST)->validate()) {
      try {
        $site->save();
        if ($id) Notify::msg('Site successfully updated.', 'success', TRUE);
        else Notify::msg('Site successfully added.', 'success', TRUE);

        $this->request->redirect('config/sites');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save site due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, site failed to be saved. Please try again.', 'error');
      }
    } elseif ($form == 'filter_form' and $filter_form->sent($_REQUEST) and $filter_form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.sites.list');

      $operator_id = $filter_form->operator_id->val();

      Session::instance()->set('pagination.sites.list', array(
        'operator_id' => $operator_id,
      ));
    } elseif ($settings = Session::instance()->get('pagination.sites.list')) {
      $filter_form->operator_id->val($operator_id = $settings['operator_id']);
    }

    if ($id) {
      $sites = array_filter(array(ORM::factory('site', $id)));
    } else {
      $sites = ORM::factory('site');
      if ($operator_id) $sites = $sites->where('operator_id', '=', $operator_id);

      $clone = clone($sites);
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $clone->find_all()->count()));

      $sites = $sites
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $sites->order_by($sort);
      $sites = $sites->order_by('name')
        ->find_all()
        ->as_array();
    }

    $table .= View::factory('sites')
      ->set('classes', array('has-pagination'))
      ->set('sites', $sites);

    if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' site found');
    elseif ($pagination->total_items) Notify::msg($pagination->total_items.' sites found');
    else Notify::msg('No sites found');

    $content .= ($id or $form == 'add_form') ? $add_form->render() : SGS::render_form_toggle($add_form->save->get('label')).$add_form->render();
    if (!$id) $content .= $filter_form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_blocks() {
    if (!Request::$current->query()) Session::instance()->delete('pagination.blocks.list');

    $id   = $this->request->param('id');
    $form = $this->request->post('form');

    $block = ORM::factory('block', $id);
    $add_form = Formo::form(array('attr' => array('style' => ($id or $form == 'add_form') ? '' : 'display: none;')))
      ->orm('load', $block, array('user_id', 'timestamp'), true)
      ->add('form', 'hidden', 'add_form')
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Block' : 'Add a New Block'
      ))
      ->order(array('name' => 0));

    if ($id) $add_form->remove('name');

    $site_ids = DB::select('id', 'name')
      ->from('sites')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $filter_form = Formo::form()
      ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
      ->add('form', 'hidden', 'filter_form')
      ->add('filter', 'submit', 'Filter');

    if ($add_form->sent($_REQUEST) and $add_form->load($_REQUEST)->validate()) {
      try {
        $block->save();
        if ($id) Notify::msg('Block successfully updated.', 'success', TRUE);
        else Notify::msg('Block successfully added.', 'success', TRUE);

        $this->request->redirect('config/blocks');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save block due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, block failed to be saved. Please try again.', 'error');
      }
    } elseif ($form == 'filter_form' and $filter_form->sent($_REQUEST) and $filter_form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.blocks.list');

      $site_id = $filter_form->site_id->val();

      Session::instance()->set('pagination.blocks.list', array(
        'site_id' => $site_id,
      ));
    } elseif ($settings = Session::instance()->get('pagination.blocks.list')) {
      $filter_form->site_id->val($site_id = $settings['site_id']);
    }

    if ($id) {
      $blocks = array_filter(array(ORM::factory('block', $id)));
    } else {
      $blocks = ORM::factory('block');
      if ($site_id) $blocks = $blocks->where('site_id', '=', $site_id);

      $clone = clone($blocks);
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $clone->find_all()->count()));

      $blocks = $blocks
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $blocks->order_by($sort);
      $blocks = $blocks->order_by('site_id')
        ->order_by('name')
        ->find_all()
        ->as_array();
    }

    $table = View::factory('blocks')
      ->set('classes', array('has-pagination'))
      ->set('blocks', $blocks);

    if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' block found');
    elseif ($pagination->total_items) Notify::msg($pagination->total_items.' blocks found');
    else Notify::msg('No blocks found');

    $content .= ($id or $form == 'add_form') ? $add_form->render() : SGS::render_form_toggle($add_form->save->get('label')).$add_form->render();
    if (!$id) $content .= $filter_form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_species() {
    $id = $this->request->param('id');

    $species = ORM::factory('species', $id);
    $form = Formo::form(array('attr' => array('style' => ($id or $_POST) ? '' : 'display: none;')))
      ->orm('load', $species, array('user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Species' : 'Add a New Species'
      ));

    if ($id) $add_form->remove('code');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $species->save();
        if ($id) Notify::msg('Species successfully updated.', 'success', TRUE);
        else Notify::msg('Species successfully added.', 'success', TRUE);

        $this->request->redirect('config/species');
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

    $content .= ($id or $_POST) ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}
