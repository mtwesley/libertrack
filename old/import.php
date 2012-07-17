<?php

include 'app/app.php';
include 'views/header.php';

?>
<fieldset>
  <legend>CSV</legend>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="form" value="csv" />

    <strong>File: </strong>
    <input type="file" name="csv" value="" />
    &nbsp;&nbsp;

    <strong>Type: </strong>
    <input type="radio" name="form_type" value="SSF" checked="checked" /> SSF
    <input type="radio" name="form_type" value="TDF" /> TDF
    <input type="radio" name="form_type" value="LDF" /> LDF
    &nbsp;&nbsp;

    <input type="submit" name="submit" value="Import" />
  </form>
</fieldset>
<?php
if ($_POST['form'] == 'csv') {

  // process form information
  $form_type = $_POST['form_type'];

  // process file information
  $filename = $_FILES['csv']['name'];
  $filepath = $_FILES['csv']['tmp_name'];
  $csv_file = $_FILES['csv']['tmp_name'];

  // insert file into database
  $sql = "INSERT INTO files (file_id,name,path,mime_type,ctime,mtime)
          VALUES (nextval('s_files_file_id'),'$filename','$filepath','text/csv',now(),now())
          RETURNING file_id";
  $result = pg_fetch_assoc(pg_query($connection, $sql));
  $file_id = $result['file_id'];

  // parse CSV
  include_once 'lib/parsecsv.php';
  $csv = new parseCSV($csv_file);

  $csv_ids = array(
    'accepted' => array(),
    'rejected' => array()
  );

  $headers_function = strtolower($form_type).'_results_header';
  $headers = $headers_function();

  $accepted_header_output = '<br />';
  $accepted_header_output .= '<form method="POST"><input type="hidden" name="form" value="form_accepted">';
  $accepted_header_output .= '<table class="csv_results" cellpadding="0" cellspacing="0"><tr>';
  foreach ($headers as $key => $value) {
    $accepted_header_output .= '<td><strong>'.$value.'</strong></td>';
  }
  $accepted_header_output .= '</tr>';

  $rejected_header_output = '<br />';
  $rejected_header_output .= '<form method="post"><input type="hidden" name="form" value="form_rejected">';
  $rejected_header_output .= '<table class="csv_results" cellpadding="0" cellspacing="0"><tr>';
  foreach ($headers as $key => $value) {
    $rejected_header_output .= '<td><strong>'.$value.'</strong></td>';
  }
  $rejected_header_output .= '</tr>';


  // iterate through CSV rows, gather data, insert into database, do everything...
  $start = strtolower($form_type).'_parse_start';
  $parse_function = strtolower($form_type).'_parse_data';
  for ($i = $start(); $i < count($csv->data); $i++) {
    if (!$data = $parse_function($csv->data[$i], $csv)) continue;
    $_data = pg_escape_string(serialize($data));
    $sql = "INSERT INTO csv (csv_id,file_id,type,form_type,data)
            VALUES (nextval('s_csv_csv_id'),{$file_id},'I','{$form_type}','{$_data}')
            RETURNING csv_id";
    $result = pg_fetch_assoc(pg_query($sql));
    $csv_id = $result['csv_id'];
    $csv_ids[] = $csv_id;

    // attempt import into form data table
    $import_function = strtolower($form_type).'_import_data';
    $form_data_id = $import_function($data);

    if ($form_data_id) {
      $csv_ids['accepted'][] = $csv_id;
      pg_query("UPDATE csv
                SET form_data_id = $form_data_id, status = 'A'
                WHERE csv_id = $csv_id");
      $accepted_output .= '<tr>';
      foreach ($data as $key => $value) {
        $accepted_output .= '<td class="accepted"><input class="form_data_id_'.$form_data_id.' csv_id_'.$csv_id.'" type="text" name="form_data_id_'.$form_data_id.'_'.$key.'" value="'.$value.'"/></td>';
      }
      $accepted_output .= '</tr>';
    } else {
      $csv_ids['rejected'][] = $csv_id;
      pg_query("UPDATE csv
                SET status = 'R'
                WHERE csv_id = $csv_id");
      $rejected_output .= '<tr>';
      foreach ($data as $key => $value) {
        $rejected_output .= '<td class="rejected"><input class="csv_id_'.$csv_id.'" type="text" name="csv_id_'.$csv_id.'_'.$key.'" value="'.$value.'"/></td>';
      }
      $rejected_output .= '</tr>';
    }
  }

  echo '<strong class="accepted">'.count($csv_ids['accepted']).' Rows Accepted -- Passed Import</strong>';
//  echo $accepted_header_output;
//  echo $accepted_output;
//  echo '</table><div class="submit"><input type="submit" name="submit" value="Apply Corrections" /></div></form>';

  echo '<strong class="rejected">'.count($csv_ids['rejected']).' Rows Rejected -- Failed Import</strong>';
  echo $rejected_header_output;
  echo $rejected_output;
  echo '</table><div class="submit"><input type="submit" name="submit" value="Apply Corrections" /></div>';
  echo '<input type="hidden" name="accepted_csv_ids" value="'.implode(',',$csv_ids['accepted']).'" />';
  echo '<input type="hidden" name="rejected_csv_ids" value="'.implode(',',$csv_ids['rejected']).'" />';
  echo '</form>';

}

else if ($_POST['form'] == 'form_rejected') {
  print "<pre>";
  print_r($_POST);
}

function ssf_parse_start() {
  return 11;
}

function ssf_parse_data($row, &$csv) {
  $matches = array();
  preg_match('/((([A-Z]+)\/)?([A-Z1-9\s-_]+)?)\/?([A-Z1-9]+)/',$csv->data[0][1],$matches);

  $operator_tin = $csv->data[0][7];
  $site_name = $matches[1];
  $site_type = $matches[3];
  $site_reference = $matches[4];
  $block_coordinates = $matches[5];

  foreach ($row as $col) {
    if ($col) $notempty = true;
  }

  return $notempty ? array(
    'operator_tin' => $operator_tin,
    'site_name' => $site_name,
    'site_type' => $site_type,
    'site_reference' => $site_reference,
    'block_coordinates' => $block_coordinates,
    'barcode' => $row[0],
    'tree_map_number' => $row[1],
    'survey_line' => $row[2],
    'cell_number' => $row[3],
    'species_code' => $row[4],
    'diameter' => $row[5],
    'height' => $row[6],
    'is_requested' => $row[7],
    'is_fda_approved' => $row[8],
    'fda_remarks' => $row[9],
  ) : null;
}

function ssf_import_data($row) {
  $row['is_requested'] = $row['is_requested'] ? $row['is_requested'] : 'YES';
  $row['is_fda_approved'] = $row['is_fda_approved'] ? $row['is_fda_approved'] : 'YES';

  $sql = "INSERT INTO ssf_data (data_id,site_id,operator_id,block_id,barcode_id,species_id,survey_line,cell_number,tree_map_number,diameter,height,is_requested,is_fda_approved,fda_remarks)
          VALUES (nextval('s_ssf_data_data_id'),lookup_site_id('{$row['site_type']}','{$row['site_reference']}'),lookup_operator_id({$row['operator_tin']}),lookup_block_id('{$row['site_type']}','{$row['site_reference']}','{$row['block_coordinates']}'),lookup_barcode_id('{$row['barcode']}'),lookup_species_id('{$row['species_code']}'),{$row['survey_line']},{$row['cell_number']},{$row['tree_map_number']},{$row['diameter']},{$row['height']},'{$row['is_requested']}','{$row['is_fda_approved']}','{$row['fda_remarks']}')
          RETURNING data_id";

  if (($result = pg_fetch_assoc(pg_query($sql)))) {
    return $result['data_id'];
  }
}

function ssf_results_header() {
  return array(
    'operator_tin' => 'Operator TIN',
    'site_name' => 'Site Name',
    'site_type' => 'Site Type',
    'site_reference' => 'Site Reference',
    'block_coordinates' => 'Block Coordinates',
    'barcode' => 'Barcode',
    'tree_map_number' => 'Tree Map Number',
    'survey_line' => 'Survey Line',
    'cell_number' => 'Cell Number',
    'species_code' => 'Species Code',
    'diameter' => 'Diameter',
    'height' => 'Height',
    'is_requested' => 'Is Requested',
    'is_fda_approved' => 'Is FDA Approved',
    'fda_remarks' => 'FDA Remarks',
  );
}

function tdf_parse_start() {
  return 7;
}

function tdf_parse_data($row, &$csv) {
  $matches = array();
  preg_match('/((([A-Z]+)\/)?([A-Z1-9\s-_]+)?)\/?([A-Z1-9]+)/',$csv->data[0][1],$matches);

  $operator_tin = $csv->data[0][6];
  $site_name = $matches[1];
  $site_type = $matches[3];
  $site_reference = $matches[4];
  $block_coordinates = $matches[5];

  foreach ($row as $col) {
    if ($col) $notempty = true;
  }

  return $notempty ? array(
    'operator_tin' => $operator_tin,
    'site_name' => $site_name,
    'site_type' => $site_type,
    'site_reference' => $site_reference,
    'block_coordinates' => $block_coordinates,
    'survey_line' => $row[0],
    'cell_number' => $row[1],
    'tree_barcode' => $row[2],
    'species_code' => $row[3],
    'barcode' => $row[4],
    'bottom_max' => $row[5],
    'bottom_min' => $row[6],
    'top_max' => $row[7],
    'top_min' => $row[8],
    'length' => $row[9],
    'stump_barcode' => $row[10],
    'action' => $row[11],
    'comment' => $row[12],
  ) : null;
}

function tdf_import_data($row) {
  $sql = "INSERT into tdf_data (data_id,site_id,operator_id,block_id,barcode_id,tree_barcode_id,stump_barcode_id,species_id,survey_line,cell_number,top_min,top_max,bottom_min,bottom_max,length)
          VALUES (nextval('s_tdf_data_data_id'),lookup_site_id('{$row['site_type']}','{$row['site_reference']}'),lookup_operator_id({$row['operator_tin']}),lookup_block_id('{$row['site_type']}','{$row['site_reference']}','{$row['block_coordinates']}'),lookup_barcode_id('{$row['barcode']}'),lookup_barcode_id('{$row['tree_barcode']}'),lookup_barcode_id('{$row['stump_barcode']}'),lookup_species_id('{$row['species_code']}'),{$row['survey_line']},{$row['cell_number']},{$row['top_min']},{$row['top_max']},{$row['bottom_min']},{$row['bottom_max']},{$row['length']})
          RETURNING data_id";

  if (($result = pg_fetch_assoc(pg_query($sql)))) {
    return $result['data_id'];
  }
}

function tdf_results_header() {
  return array(
    'operator_tin' => 'Operator TIN',
    'site_name' => 'Site Name',
    'site_type' => 'Site Type',
    'site_reference' => 'Site Reference',
    'block_coordinates' => 'Block Coordinates',
    'survey_line' => 'Survey Line',
    'cell_number' => 'Cell Number',
    'tree_barcode' => 'Tree Barcode',
    'species_code' => 'Species Code',
    'barcode' => 'New Cross Cut Barcode',
    'bottom_max' => 'Butt Max',
    'bottom_min' => 'Butt Min',
    'top_max' => 'Top Max',
    'top_min' => 'Top Min',
    'stump_barcode' => 'Stump Barcode',
    'length' => 'Length',
    'action' => 'Action',
    'comment' => 'Comment',
  );
}

?>

