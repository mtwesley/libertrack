<?php

class Controller_Invoices extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('invoices')) {
      Notify::msg('Sorry, access denied. You must have '.SGS::$roles['invoices'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    Session::instance()->write();
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_st() {
    $site_ids = DB::select('id', 'name')
      ->from('sites')
      ->execute()
      ->as_array('id', 'name');

    $form = Formo::form()
      ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'required' => TRUE))
      ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
      ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')))
      ->add('is_draft', 'radios', array(
        'options' => array(
          0 => 'Final Copy',
          1 => 'Draft Copy'),
        'label' => '&nbsp;',
        'required' => TRUE,
        ))
      ->add('submit', 'submit', 'Generate');

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $site_id   = $form->site_id->val();
      $from      = $form->from->val();
      $to        = $form->to->val();

      $data = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price',array(DB::expr('sum(volume)'), 'volume'))
        ->from('ldf_data')
        ->join('species')
        ->on('species_id', '=', 'species.id')
        ->where('site_id', '=', $site_id)
        ->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->group_by('species_code', 'species_class', 'fob_price')
        ->execute()
        ->as_array();

      $invoice = View::factory('invoices/st')
        ->set('data', $data)
        ->set('from', $from)
        ->set('to', $to)
        ->render();
    }

    if ($form)    $content .= $form;
    if ($invoice) $content .= $invoice;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}