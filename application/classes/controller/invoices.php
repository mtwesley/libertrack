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

//  public function action_process() {
//    set_time_limit(0);
//    foreach (DB::select('id')
//      ->from('csv')
//      ->where('status', '!=', 'A')
//      ->execute() as $id) {
//      $csv = ORM::factory('CSV', $id);
//      $csv->process();
//    }
//  }
//
//  private function handle_st_

  public function action_st() {
    $command = $this->request->param('command');

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

      $summary_data = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price', array(DB::expr('sum(volume)'), 'volume'))
        ->from('ldf_data')
        ->join('species')
        ->on('species_id', '=', 'species.id')
        ->where('site_id', '=', $site_id)
        ->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->group_by('species_code', 'species_class', 'fob_price')
        ->execute()
        ->as_array();

      foreach(DB::select('barcode', array('create_date', 'scan_date'), array('code', 'species_code'), array('class', 'species_class'), array('botanic_name', 'species_botanic_name'), array(DB::expr('((top_min + top_max + bottom_min + bottom_max) / 4)'), 'diameter'), 'length', 'volume')
        ->from('ldf_data')
        ->join('barcodes')
        ->on('barcode_id', '=', 'barcodes.id')
        ->join('species')
        ->on('species_id', '=', 'species.id')
        ->where('site_id', '=', $site_id)
        ->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->execute() as $result) $details_data[$result['species_code']][] = $result;

      $site     = ORM::factory('site', $site_id);
      $operator = $site->operator;

      $summary_first_page_max  = 9;
      $summary_last_page_max   = 10;
      $summary_normal_page_max = 13;

      $summary_count = count($summary_data);
      $summary_page_count = ceil(($summary_count - $summary_first_page_max - $summary_last_page_max) / $summary_normal_page_max) + 2;

      foreach ($summary_data as $record) {
        foreach ($record as $key => $value) $summary_total[$key] += $value;
        $summary_total['total'] += $record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']];
      }

      $details_page_max = 32;

      foreach ($details_data as $code => $records) {
        $details_page_count += ceil(count($records) / $details_page_max);
        foreach ($records as $record) foreach ($record as $key => $value) $details_total[$code][$key] += $value;
      }

      $signature_page_count = 1;
      $page_count = $summary_page_count + $signature_page_count + $details_page_count;

      $cntr = 0;
      for ($page = 1; $page <= $summary_page_count; $page++) {
        if ($page == 1) {
          $max = $summary_first_page_max;
          $options = array(
            'break' => FALSE,
            'styles' => TRUE,
            'info' => TRUE
          );
        } else if ($page == $summary_page_count) {
          $max = $summary_last_page_max;
          $options = array(
            'total' => TRUE
          );
        } else {
          $max = $summary_normal_page_max;
        }

        $set = array_slice($summary_data, $cntr, $max);
        $invoice .= View::factory('invoices/st')
          ->set('data', $set)
          ->set('from', $from)
          ->set('to', $to)
          ->set('site', $site)
          ->set('operator', $operator)
          ->set('options', array('summary' => TRUE) + (array) $options)
          ->set('page', $page)
          ->set('page_count', $page_count)
          ->set('total', array('summary' => $summary_total))
          ->render();

        $cntr += $max;
      }

      $invoice .= View::factory('invoices/st')
        ->set('options', array('signature' => TRUE))
        ->set('page', $page)
        ->set('page_count', $page_count)
        ->render();
      $page++;

      $max = $details_page_max;
      foreach ($details_data as $code => $records) {
        $cntr = 0;
        while ($cntr < $max) {
          $set = array_slice($records, $cntr, $max);
          $invoice .= View::factory('invoices/st')
            ->set('data', $set)
            ->set('options', array(
              'details' => TRUE,
              'total'   => count($records) > ($cntr + $max) ? FALSE : TRUE)
              )
            ->set('page', $page)
            ->set('page_count', $page_count)
            ->set('total', array('details' => $details_total))
            ->render();
          $cntr += $max;
          $page++;
        }
      }
    }

    if ($form)    $content .= $form;
    if ($invoice) {
      if ($format == 'html') $content .= $invoice;
      else {
        set_time_limit(0);
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