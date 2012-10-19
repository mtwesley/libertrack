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
      ->add('format', 'radios', array(
        'options' => array(
          'html' => 'HTML Document',
          'pdf' => 'PDF Document'),
        'label' => '&nbsp;',
        'required' => TRUE,
        ))
      ->add('is_draft', 'radios', array(
        'options' => array(
          0 => 'Final Copy',
          1 => 'Draft Copy'),
        'label' => '&nbsp;',
        'required' => TRUE,
        ))
      ->add('submit', 'submit', 'Generate');

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $site_id  = $form->site_id->val();
      $format   = $form->format->val();
      $is_draft = $form->is_draft->val();
      $from     = $form->from->val();
      $to       = $form->to->val();

      $data = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price',array(DB::expr('sum(volume)'), 'volume'))
        ->from('ldf_data')
        ->join('species')
        ->on('species_id', '=', 'species.id')
        ->where('site_id', '=', $site_id)
        ->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->group_by('species_code', 'species_class', 'fob_price')
        ->execute()
        ->as_array();

      $site     = ORM::factory('site', $site_id);
      $operator = $site->operator;

      $set = array_slice($data, 0, 9);

      $invoice .= View::factory('invoices/st')
        ->set('data', $set)
        ->set('from', $from)
        ->set('to', $to)
        ->set('site', $site)
        ->set('operator', $operator)
        ->set('options', array(
          'format' => $format
        ))
        ->render();

      $set = array_slice($data, 9, 9);

      $invoice .= View::factory('invoices/st')
        ->set('data', $set)
        ->set('from', $from)
        ->set('to', $to)
        ->set('site', $site)
        ->set('operator', $operator)
        ->set('options', array(
          'styles' => FALSE,
          'info'   => FALSE,
          'format' => $format
        ))
        ->render();

//array(
//  'styles' => TRUE,
//  'header' => TRUE,
//  'footer' => TRUE,
//  'info'   => TRUE,
//  'table'  => TRUE,
//  'format' => 'pdf'
//);

    }

    if ($form)    $content .= $form;
    if ($invoice) {
      if ($format == 'html') $content .= $invoice;
      else {
        $dompdf = new DOMPDF();
        $dompdf->set_paper("A4");
        $dompdf->load_html($invoice);
        $dompdf->render();
        $dompdf->stream("sample.pdf");
      }
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}