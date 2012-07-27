<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Import extends Controller {

  const SSF_PARSE_START      = 13;
  const TDF_PARSE_START      = 13;
  const LDF_PARSE_START      = 9;
  const MIF_PARSE_START      = 13;
  const MOF_PARSE_START      = 13;
  const SPECS_PARSE_START    = 13;
  const EPR_PARSE_START      = 13;
  const PRINTJOB_PARSE_START = 8;

  private static function format_bytes($bytes) {
    if ($bytes < 1024) {
        return $bytes .' B';
    } elseif ($bytes < 1048576) {
        return round($bytes / 1024, 2) .' KB';
    } elseif ($bytes < 1073741824) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes < 1099511627776) {
        return round($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes < 1125899906842624) {
        return round($bytes / 1099511627776, 2) .' TB';
    }
  }

  public function action_edit() {
    $id = $this->request->param('id');
    $body = View::factory('header')->render();

    $csv = ORM::factory('csv', $id);
    $data = unserialize($csv->data);
    $form_type = strtolower($csv->form_type);

    $form = Formo::form();
    foreach ($data as $key => $value) {
      $form->add(array(
        'alias' => $key,
        'value' => $value
      ));
    }
    $form->add(array(
      'alias' => 'save',
      'driver' => 'submit',
      'value' => 'save and re-process'
    ));

    if ($form->sent($_POST) and $form->load($_POST)) {
      foreach ($data as $key => $value) {
        $data[$key] = $form->$key->val();
      }
      $csv->data = serialize($data);
      $csv->status = 'P';
      try {
        $csv->save();
        $csv_saved = true;
      } catch (Exception $e) {
        $body .= '<p>Unable to update CSV</p>';
      }

      if ($csv_saved) {
        $form_model = ORM::factory($form_type);
        $form_model->parse_data($data);

        try {
          $form_model->save();

          $csv->form_data_id = $form_model->id;
          $csv->status = 'A';
          $csv->save();
          $body .= '<p>CSV '.$csv->id.' accepted</p>';
        } catch (Exception $e) {
          $csv->status = 'R';
          $csv->save();
          $body .= '<p>CSV '.$csv->id.' rejected</p>';
        }
      }
    }

    $body .= $form->render();

    $this->response->body($body);
  }

  public function action_review() {
    $id = $this->request->param('id');
    $file = ORM::factory('file', $id);

    $body = View::factory('header');

    // pending
    $pending = $file->csv
      ->where('status', '=', 'P')
      ->find_all()
      ->as_array();

    if ($pending) {
      $rows = $header = '';
      $body .= '<strong>'.count($pending).' pending records</strong>';
      foreach ($pending as $item) {
        $data = unserialize($item->data);
        $rows .= '<tr>';
        $rows .= '<td>'.$item->form_type.'</td>';
        foreach ($data as $value) {
          $rows .= '<td>'.$value.'</td>';
        }
        $rows .= '<td>'.($item->form_data_id ? 'YES' : 'NO').'</td>';
        $rows .= '</tr>';
      }
      $header .= '<tr><td><strong>form_type</strong></td>';
      foreach (array_keys($data) as $key) {
        $header .= '<td><strong>'.$key.'</td>';
      }
      $header .= '<td><strong>is_imported</strong></td></tr>';
      $body .= '<p><table border="1">' . $header . $rows . '</table></p>';
    } else {
      $body .= '<p><strong>No pending records</strong></p>';
    }

    // accepted
    $accepted = $file->csv
      ->where('status', '=', 'A')
      ->find_all()
      ->as_array();

    if ($accepted) {
      $rows = $header = '';
      $body .= '<strong>'.count($accepted).' accepted records</strong>';
      foreach ($accepted as $item) {
        $data = unserialize($item->data);
        $rows .= '<tr>';
        $rows .= '<td>'.$item->form_type.'</td>';
        foreach ($data as $value) {
          $rows .= '<td>'.$value.'</td>';
        }
        $rows .= '<td>'.($item->form_data_id ? 'YES' : 'NO').'</td>';
        $rows .= '</tr>';
      }
      $header .= '<tr><td><strong>form_type</strong></td>';
      foreach (array_keys($data) as $key) {
        $header .= '<td><strong>'.$key.'</td>';
      }
      $header .= '<td><strong>is_imported</strong></td></tr>';
      $body .= '<p><table border="1">' . $header . $rows . '</table></p>';
    } else {
      $body .= '<p><strong>No accepted records</strong></p>';
    }

    // rejected
    $rejected = $file->csv
      ->where('status', '=', 'R')
      ->find_all()
      ->as_array();

    if ($rejected) {
      $rows = $header = '';
      $body .= '<strong>'.count($rejected).' rejected records</strong>';
      foreach ($rejected as $item) {
        $data = unserialize($item->data);
        $rows .= '<tr>';
        $rows .= '<td>'.$item->form_type.'</td>';
        foreach ($data as $value) {
          $rows .= '<td>'.$value.'</td>';
        }
        $rows .= '<td>'.($item->form_data_id ? 'YES' : 'NO').'</td>';
        $rows .= '<td>'.HTML::anchor('/import/csv/'.$item->id.'/edit', 'edit').'</td>';
        $rows .= '</tr>';
      }
      $header .= '<tr><td><strong>form_type</strong></td>';
      foreach (array_keys($data) as $key) {
        $header .= '<td><strong>'.$key.'</td>';
      }
      $header .= '<td><strong>is_imported</strong></td><td></td></tr>';
      $body .= '<p><table border="1">' . $header . $rows . '</table></p>';
    } else {
      $body .= '<p><strong>No rejected records</strong></p>';
    }

    $this->response->body($body);
  }

  public function action_process() {
    $db = Database::instance();

    $id = $this->request->param('id');
    $file = ORM::factory('file', $id);

    $body = View::factory('header');

    // pending
    $pending = $file->csv
      ->where('status', 'LIKE', 'P')
      ->find_all()
      ->as_array();

    foreach ($pending as $csv) {
      $data = unserialize($csv->data);
      $form_type = strtolower($csv->form_type);

      $model = ORM::factory($form_type);
      $model->parse_data($data);

      try {
        $model->save();

        $csv->form_data_id = $model->id;
        $csv->status = 'A';
        $csv->save();
        $body .= '<p>CSV '.$csv->id.' accepted</p>';
      } catch (Exception $e) {
        $csv->status = 'R';
        $csv->save();
        $body .= '<p>CSV '.$csv->id.' rejected</p>';
      }
    }

    $this->request->redirect('import/files/'.$id.'/review');
  }

  public function action_files() {
    $id = $this->request->param('id');
    $body = View::factory('header')->render();

    $form = Formo::form()
      ->add('import', 'file')
      ->add('upload', 'submit');

    if ($form->sent($_POST) and $form->load($_POST)) {
      $import = $form->import->val();
      $array = PHPExcel_IOFactory::load($import['tmp_name'])->getActiveSheet()->toArray(null,true,true,true);

      // detect type of file
      if (strpos($array[1][D], 'STOCK SURVEY FORM') !== false) {
        $is_form = true;
        $form_type = 'SSF';
      } elseif (strpos($array[1][C], 'Tree Felling') !== false) {
        $is_form = true;
        $form_type = 'TDF';
      } elseif (strpos($array[1][C], 'L0G DATA F0RM') !== false) {
        $is_form = true;
        $form_type = 'LDF';
      } elseif (strpos($array[3][A], 'Print Job') !== false) {
        $is_printjob = true;
      }

      $file = ORM::factory('file', $id);
      $file->name = $import['name'];
      $file->type = $import['type'];
      $file->size = $import['size'];
      $file->content_md5 = md5_file($import['tmp_name']);
      try {
        $file->save();
        $body .= '<p>File uploaded.</p>';
      } catch (Exception $e) {
          $body .= '<p>Unable to upload file.</p>';
      }

      $csv_ids = array();
      $csv_errors = 0;
      $file_data = array();
      $model = ORM::factory($form_type);
      $start = constant('Model_'.$form_type.'::PARSE_START');
      $count = count($array);
      for ($i = $start; $i <= $count; $i++) {
        $row = $array[$i];
        $data = $model->parse_csv($row, $array);
        if (!$data) continue;
        $file_data[] = $data;

        if ($is_form) {
          $csv = ORM::factory('csv');
          $csv->file_id = $file->id;
          $csv->type = 'I';
          $csv->form_type = $form_type;
          $csv->data = serialize($data);
          try {
            $csv->save();
            $csv_ids[] = $csv->id;
          } catch (Exception $e) {
            $csv_errors++;
          }
        }
      }

      $body .= '<p>'.count($csv_ids).' items parsed from file.</p>';
      if ($csv_errors) {
        $body .= '<p>'.$csv_errors.' items not parsed due to errors.</p>';
      }

      if ($is_form) $file->data_type = 'F';
      elseif ($is_printjob) $file->data_type = 'P';

      $file->data_md5 = md5(serialize($file_data));
      try {
        $file->save();
      } catch (Exception $e) {
        // this error doesnt really matter now
      }

    }

    $body .= $form->render();

    $files = ORM::factory('file')->where('data_type', '=', 'F')->find_all()->as_array();
    $body .= '<table border="1">';
    $body .= '<tr>';
    $body .= '<td><strong>name</strong></td>';
    $body .= '<td><strong>type</strong></td>';
    $body .= '<td><strong>size</strong></td>';
    $body .= '<td><strong>statistics</strong></td>';
    $body .= '<td></td>';
    $body .= '</tr>';
    foreach ($files as $f) {
      $_p = (int) $f->csv->where('status', '=', 'P')->find_all()->count();
      $_a = (int) $f->csv->where('status', '=', 'A')->find_all()->count();
      $_r = (int) $f->csv->where('status', '=', 'R')->find_all()->count();

      $_total = $_p + $_a + $_r;

      $body .= '<tr>';
      $body .= '<td>'.$f->name.'</td>';
      $body .= '<td>'.$f->type.'</td>';
      $body .= '<td>'.Controller_Import::format_bytes($f->size).'</td>';
      $body .= '<td>'.$_p.' Pending ('.number_format($_p * 100 / $_total).'%)<br> '.$_a.' Accepted ('.number_format($_a * 100 / $_total).'%)<br> '.$_r.' Rejected ('.number_format($_r * 100 / $_total).'%)</td>';
      $body .= '<td>'.HTML::anchor('/import/files/'.$f->id.'/review', 'review').' | '.HTML::anchor('/import/files/'.$f->id.'/process', 'process').'</td>';
      $body .= '</tr>';
    }
    $body .= '</table>';

    $this->response->body($body);
  }

  private static function ssf_parse_data($row, &$array) {
    $matches = array();
    preg_match('/((([A-Z]+)\/)?([A-Z1-9\s-_]+)?)\/?([A-Z1-9]+)/',$array[2][B],$matches);

    $operator_tin = $array[2][H];
    $site_name = $matches[1];
    $site_type = $matches[3];
    $site_reference = $matches[4];
    $block_coordinates = $matches[5];

    return (array_filter($row)) ? array(
      'operator_tin' => $operator_tin,
      'site_name' => $site_name,
      'site_type' => $site_type,
      'site_reference' => $site_reference,
      'block_coordinates' => $block_coordinates,
      'barcode' => $row[A],
      'tree_map_number' => $row[B],
      'survey_line' => $row[C],
      'cell_number' => $row[D],
      'species_code' => $row[E],
      'diameter' => $row[F],
      'height' => $row[G],
      'is_requested' => $row[H],
      'is_fda_approved' => $row[I],
      'fda_remarks' => $row[J],
    ) : null;
  }

  private static function ssf_process_data($row) {
    $row['is_requested'] = $row['is_requested'] ? $row['is_requested'] : 'YES';
    $row['is_fda_approved'] = $row['is_fda_approved'] ? $row['is_fda_approved'] : 'YES';

    $sql = "INSERT INTO ssf_data (site_id,operator_id,block_id,barcode_id,species_id,survey_line,cell_number,tree_map_number,diameter,height,is_requested,is_fda_approved,fda_remarks)
            VALUES (lookup_site_id('{$row['site_type']}','{$row['site_reference']}'),lookup_operator_id({$row['operator_tin']}),lookup_block_id('{$row['site_type']}','{$row['site_reference']}','{$row['block_coordinates']}'),lookup_barcode_id('{$row['barcode']}'),lookup_species_id('{$row['species_code']}'),{$row['survey_line']},{$row['cell_number']},{$row['tree_map_number']},{$row['diameter']},{$row['height']},'{$row['is_requested']}','{$row['is_fda_approved']}','{$row['fda_remarks']}')";
    try {
      $result = DB::query(Database::INSERT, $sql)->execute();
      return true;
    } catch (Exception $e) {}

    return false;
  }

  private static function tdf_parse_data($row, &$array) {
    $matches = array();
    preg_match('/((([A-Z]+)\/)?([A-Z1-9\s-_]+)?)\/?([A-Z1-9]+)/',$array[2][B],$matches);

    $operator_tin = $array[2][G];
    $site_name = $matches[1];
    $site_type = $matches[3];
    $site_reference = $matches[4];
    $block_coordinates = $matches[5];

    return (array_filter($row)) ? array(
      'operator_tin' => $operator_tin,
      'site_name' => $site_name,
      'site_type' => $site_type,
      'site_reference' => $site_reference,
      'block_coordinates' => $block_coordinates,
      'survey_line' => $row[A],
      'cell_number' => $row[B],
      'tree_barcode' => $row[C],
      'species_code' => $row[D],
      'barcode' => $row[E],
      'bottom_max' => $row[F],
      'bottom_min' => $row[G],
      'top_max' => $row[H],
      'top_min' => $row[I],
      'length' => $row[J],
      'stump_barcode' => $row[K],
      'action' => $row[L],
      'comment' => $row[M],
    ) : null;
  }

  private static function ldf_parse_data($row, &$array) {
    $matches = array();
    preg_match('/((([A-Z]+)\/)?([A-Z1-9\s-_]+)?)\/?([A-Z1-9]+)/',$array[2][B],$matches);

    $operator_tin = $array[4][B];
    $site_name = $matches[1];
    $site_type = $matches[3];
    $site_reference = $matches[4];
    $block_coordinates = $matches[5];

    return (array_filter($row)) ? array(
      'operator_tin' => $operator_tin,
      'site_name' => $site_name,
      'site_type' => $site_type,
      'site_reference' => $site_reference,
      'parent_barcode' => $row[A],
      'species_code' => $row[B],
      'barcode' => $row[C],
      'bottom_max' => $row[D],
      'bottom_min' => $row[E],
      'top_max' => $row[F],
      'top_min' => $row[G],
      'length' => $row[H],
      'volume' => $row[I],
      'action' => $row[J],
      'comment' => $row[K],
      // 'coc_status' => $row[L],
    ) : null;
  }
}

