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

  public function action_buyers() {
    $id = $this->request->param('id');

    $buyer = ORM::factory('buyer', $id);
    $form = Formo::form(array('attr' => array('style' => ($id or $_POST) ? '' : 'display: none;')))
      ->orm('load', $buyer, array('sites', 'user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Buyer' : 'Add a New Buyer'
      ));

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $buyer->save();
        if ($id) Notify::msg('Buyer successfully updated.', 'success', TRUE);
        else Notify::msg('Buyer successfully added.', 'success', TRUE);

        $this->request->redirect('config/buyers');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save buyer due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, buyer failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $buyer->find_all()->count()));

      $buyers = ORM::factory('buyer')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $buyers->order_by($sort);
      $buyers = $buyers->order_by('name')
        ->find_all()
        ->as_array();

      $table .= View::factory('buyers')
        ->set('classes', array('has-pagination'))
        ->set('buyers', $buyers);

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' buyer found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' buyers found');
      else Notify::msg('No buyers found');
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

  private function generate_inspection_file($block) {
    if (!$ids = $block->get_inspection_data()) return NULL;

    $schedule = DB::select('cell_number', 'survey_line')
      ->from('ssf_data')
      ->where('id', 'IN', $ids)
      ->execute()
      ->as_array();

    $html .= View::factory('schedule')
      ->set('schedule', $schedule)
      ->set('options', array(
        'info'    => TRUE,
        'summary' => TRUE,
        'styles'  => TRUE,
      ))
      ->set('block', $block)
      ->render();

    // generate pdf
    set_time_limit(600);

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array(
      'schedules',
      $block->site->name,
      $block->name
    ));

    $newname = SGS::wordify('SCHEDULE_'.$block->site->name.'_'.$block->name.'_'.SGS::date('now', 'Y_m_d')).'.'.$ext;

    $version = 0;
    $testname = $newname;
    while (file_exists(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname)) {
      $newname = substr($testname, 0, strrpos($testname, '.'.$ext)).'_'.($version++).'.'.$ext;
    }

    if (!is_dir(DOCPATH.$newdir) and !mkdir(DOCPATH.$newdir, 0777, TRUE)) {
      Notify::msg('Sorry, cannot access block inspection schedule folder. Check file access capabilities with the site administrator and try again.', 'error');
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
        'footer-html' => View::factory('schedule')
          ->set('options', array(
            'header' => FALSE,
            'footer' => TRUE,
            'break'  => FALSE))
          ->render()
      ));
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to generate block inspection schedule. If this problem continues, contact the system administrator.', 'error');
      return FALSE;
    }

    try {
      // prepare and save file
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

  private function handle_block_inspection($id) {
    $block = ORM::factory('block', $id);

    if (!$block->loaded()) {
      Notify::msg('No block found.', 'warning', TRUE);
      $this->request->redirect('blocks');
    }

    $form_type = 'SSF';
    $ids = $block->get_inspection_data();

    $declaration = ORM::factory('SSF')->where('block_id', '=', $block->id)->find_all()->count();
    if (!$declaration) {
      Notify::msg('No declaration data found.', 'warning', TRUE);
      $this->request->redirect('config/blocks/'.$id);
    }

    $inspection  = count($ids);

    // $rate = $inspection / $declaration;
    if (!$ids) {
      $needed = TRUE;
      $form = Formo::form()
        // ->add('confirm', 'text', 'Increasing block inspection by at least '.$needed.' trees is necessary to achieve a rate of '.SGS::floatify(SGS::INSPECTION_RATE * 100).'%. <br />Are you sure you want to do this?')
        ->add('update', 'centersubmit', 'Update Block Inspection');
    }

    if ($form and $form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $cell_numbers = DB::select('cell_number')
        ->distinct(TRUE)
        ->from('ssf_data')
        ->where('block_id', '=', $block->id)
        ->execute()
        ->as_array(NULL, 'cell_number');

      $survey_lines = DB::select('survey_line')
        ->distinct(TRUE)
        ->from('ssf_data')
        ->where('block_id', '=', $block->id)
        ->execute()
        ->as_array(NULL, 'survey_line');

      // $max = (count($cell_numbers) ?: 1) * (count($survey_lines) ?: 1);
      
      if ($block->site->type == 'TSC') $max = 10;
      else $max = 20;
      
      $inspected_cells = array();
      while (count($inspected_cells) < $max) {
        $rand_cell_number = array_rand($cell_numbers);
        $rand_survey_line = array_rand($survey_lines);
        $rand_cell = "$rand_cell_number-$rand_survey_line";
        
        if (!in_array($rand_cell, $inspected_cells)) $inspected_cells[] = $rand_cell;
        
        $additions = DB::select('id')
          ->from('ssf_data')
          ->where('block_id', '=', $block->id)
          ->and_where('cell_number', '=', $cell_numbers[$rand_cell_number])
          ->and_where('survey_line', '=', $survey_lines[$rand_survey_line]);
        if ($ids) $additions->and_where('id', 'NOT IN', $ids);
        $additions = $additions
          ->execute()
          ->as_array(NULL, 'id');

        foreach ($additions as $addition) $block->set_inspection_data('SSF', $addition);
        // $needed -= count($additions);
        // if ($needed <= 0) { $needed = FALSE; break; }
      }

      $ids = $block->get_inspection_data();
      $rate = count($ids) / $declaration;

      $this->request->redirect($this->request->url());
    }

    if ($ids) $download_form = Formo::form()->add('download', 'centersubmit', 'Download Block Inspection Schedule');
    if ($download_form and $download_form->sent($_REQUEST) and $download_form->load($_REQUEST)->validate()) {
      $inspection_file = ORM::factory('file', $block->inspection_file_id);

      if (!$inspection_file->loaded()) {
        $block->inspection_file_id = self::generate_inspection_file($block);
        try {
          $block->save();
        } catch (Exception $e) {
          Notify::msg('Sorry, unable to create block inspection file. Please try again.', 'error');
        }
        $inspection_file = ORM::factory('file', $block->inspection_file_id);
      }

      $this->response->send_file(DOCROOT.$inspection_file->path);
    }

    // Notify::msg('Inspection rate currently at '.SGS::floatify($rate * 100).'%', $needed ? 'warning' : 'success');

    if ($ids) {
      $data = ORM::factory($form_type)
        ->where(strtolower($form_type).'.id', 'IN', (array) $ids)
        ->order_by('survey_line')
        ->order_by('cell_number');

      $clone = clone($data);
      $pagination = Pagination::factory(array(
        'current_page' => array(
          'source' => 'query_string',
          'key' => 'summary_page',
        ),
        'items_per_page' => 50,
        'total_items' => $total_items = $clone->find_all()->count()));

      $data = $data
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page)
        ->find_all()
        ->as_array();

      $table = View::factory('data')
        ->set('classes', array('has-pagination'))
        ->set('form_type', $form_type)
        ->set('data', $data)
        ->set('operator', $block->site->operator->loaded() ? $block->site->operator : NULL)
        ->set('site', $block->site->loaded() ? $block->site : NULL)
        ->set('options', array(
          'links'  => FALSE,
          'header' => FALSE,
        ))
        ->render();
      Notify::msg($total_items.' block inspection data found.', 'notice');
    } else Notify::msg('No block inspection data found', 'notice');

    $block_table = View::factory('blocks')
      ->set('blocks', array($block));

    if ($needed and $form) $content .= $form;
    else if ($download_form) $content .= $download_form;
    $content .= $block_table;
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')
      ->set('title', 'Block Inspection')
      ->set('content', $content);
    $this->response->body($view);
  }

  public function action_blocks() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');
    $form    = $this->request->post('form');

    switch ($command) {
      case 'inspection': return self::handle_block_inspection($id);
      default: continue;
    }

    if (!Request::$current->query()) Session::instance()->delete('pagination.blocks.list');

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

    if ($id) $form->remove('code');

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
