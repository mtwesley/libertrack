<?php

include 'app/app.php';
include 'views/header.php';

?>
<fieldset>
  <legend>Barcode Magic</legend>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="form" value="barcode_magic" />

    <strong>Search: </strong>
    <input type="text" name="search" value="" />
    &nbsp;&nbsp;

    <strong>Type: </strong>
    <input type="checkbox" name="type[]" value="P" checked="checked" /> Pending / Not Assigned
    <input type="checkbox" name="type[]" value="S" /> Standing Tree
    <input type="checkbox" name="type[]" value="F" /> Felled Tree
    <input type="checkbox" name="type[]" value="S" /> Stump
    <input type="checkbox" name="type[]" value="L" /> Log
    <input type="checkbox" name="type[]" value="R" /> Sawn Timber
    &nbsp;&nbsp;

    <strong>Results: </strong>
    <select name="max_results">
      <option value="0">No Limit</option>
      <option value="10" selected="selected">10</option>
      <option value="25">25</option>
      <option value="50">50</option>
      <option value="100">100</option>
      <option value="250">250</option>
      <option value="500">500</option>
      <option value="1000">100</option>
    </select>
    &nbsp;&nbsp;

    <input type="submit" name="submit" value="Magic!" />
  </form>
</fieldset>
<?php
if ($_POST['form'] == 'barcode_magic') {

  // process form information
  $search = $_POST['search'];
  $max_results = $_POST['max_results'];
  $type = $_POST['type'];

  $barcodes = barcode_magic($search, $max_results, $type);
  $plural = count($barcodes) == 1 ? '' : 's';

  echo '<strong class="found">'.count($barcodes).' Barcode'.$plural.' Found -- Similar to "'.strtoupper($search).'"</strong>';
  echo '<br /><table class="barcode_results" cellpadding="0" cellspacing="0">';
  echo '<tr><td><strong>Barcode</strong></td></tr>';
  foreach ($barcodes as $barcode) {
    echo '<tr><td class="found">'.$barcode['barcode'].'</td></td>';
  }
  echo '</table>';
}

function barcode_magic($search, $max_results = 10, $type = array('P')) {
  $barcodes = array();
  $min_length = 2;
  $max_grab = strlen($search);

  $grab = 0;
  while ($grab < $max_grab) {
    $left = 0;
    $right = $grab;

    while ($left <= $grab) {
      $bcode = $right
             ? substr($search, $left, "-$right")
             : substr($search, $left);

      if (strlen($bcode) >= $min_length) {
        $sql = "SELECT barcode_id,barcode
                FROM barcodes
                WHERE barcode LIKE upper('%$bcode%') AND type IN ('".implode(',',$type)."')";
        if ($barcodes) $sql .= " AND barcode NOT IN (".implode(',',$barcodes).")";
        $resource = pg_query($sql);
        while ($result = pg_fetch_assoc($resource)) {
          $barcodes[] = $result;
          if ($result['barcode'] == $search) return $result;
          if ($max_results && (count($barcodes) == $max_results)) return $barcodes;
        }
      }

      $left++;
      $right--;
    }

    $grab++;
  }

  return $barcodes;
}

?>
