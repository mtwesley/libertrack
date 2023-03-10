<?php
if ($block and !$site) $site = $block->site;
if ($site  and !$operator) $operator = $site->operator;

$options = (array) $options + array(
  'table'    => TRUE,
  'rows'     => TRUE,
  'details'  => TRUE,
  'links'    => TRUE,
  'actions'  => FALSE,
  'dropdown' => TRUE,
  'header'   => ($site or $operator or $specs_info or $exp_info or $wb_info) ? TRUE : FALSE,
  'hide_hidden_info' => TRUE,
  'hide_header_info' => FALSE,
  'hide_upload_info' => TRUE,
);

$header_columns = 0;
$additional_columns = 3;

if ($options['actions']) $classes[] = 'has-actions';
if ($options['details']) $classes[] = 'has-details';
if ($options['header'])  $classes[] = 'has-header';
if ($options['hide_header_info']) $classes[] = 'has-hide-header';
if ($options['hide_upload_info']) $classes[] = 'has-hide-upload';

$fields = ORM::factory($form_type)->labels();
$classes[] = 'data';
?>
<?php if ($options['header']): ?>
<table class="data-header">
  <tr>
    <td class="label">Operator:</td>
    <td><?php if ($operator) echo $operator->name; ?></td>
    <td class="label">
      <?php
        if ($site) echo 'Site:';
        else if ($specs_info) echo 'Shipment Specification Barcode:';
        else if ($wb_info) echo 'Waybill Barcode:';
      ?>
    </td>
    <td>
      <?php
        if ($site) echo $site->name;
        else if ($specs_info) echo $specs_info['barcode'];
        else if ($wb_info) echo $wb_info['barcode'];
      ?>
    </td>
  </tr>
  <tr>
    <td class="label">TIN:</td>
    <td><?php if ($operator) echo $operator->tin; ?></td>
    <td class="label"><?php if ($block) echo 'Block:'; ?></td>
    <td><?php if ($block) echo $block->name; ?></td>
  </tr>
</table>
<?php endif; ?>

<?php if ($options['actions']): ?>
<?php $additional_columns++; ?>
<div class="action-bar">
  <?php if ($total_items): ?>
  <!-- <span class="link">Select All</span>
  <span class="link">De-select All</span>
  (<?php echo $total_items; ?> records) -->
  <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($options['table']): ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <?php if ($options['actions']): ?>
    <th class="checkbox"><input type="checkbox" name="action-all" value="" /></th>
    <?php endif; ?>
    <th class="type"></th>
    <th class="status"></th>
    <?php foreach ($fields as $field => $name): ?>
    <?php
      if ($options['header'] or $options['hide_header_info']) switch ($field):
        case 'operator_tin':
        case 'operator_id':
        case 'transport_operator_tin':
        case 'transport_operator_id':
        case 'site_name':
        case 'site_id':
        case 'block_name':
        case 'block_id':
        case 'specs_barcode':
        case 'specs_barcode_id';
        case 'specs_number':
        case 'exp_barcode':
        case 'exp_barcode_id':
        case 'exp_number':
          $header_columns++;
          continue 2;
      endswitch;
    ?>
    <?php
      if ($options['hide_hidden_info']) switch ($field):
        case 'enumerator':
        case 'buyer':
        case 'driver':
        case 'origin_date':
        case 'destination_date':
        case 'unloading_date':
        case 'entered_date':
        case 'checked_date':
        case 'loading_date':
        case 'loading_supervised_by':
        case 'receiving_supervised_by':
        case 'measured_by':
        case 'entered_by':
        case 'checked_by':
        case 'signed_by':
        case 'submitted_by':
        case 'contract_number':
        case 'truck_number':
        case 'form_number':
        case 'is_requested':
        case 'is_fda_approved':
        case 'fda_remarks':
        case 'comment':
        case 'action':
          $hidden_column = TRUE; break;
        default:
          $hidden_column = FALSE; break;
      endswitch;
    ?>
    <th class="<?php echo $hidden_column ? 'hide' : ''; ?>"> <?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => $field)), $name); ?></th>
    <?php endforeach; ?>
    <th class="<?php echo $options['hide_upload_info'] ? 'hide' : ''; ?>">Uploaded By</th>
    <th class="<?php echo $options['hide_upload_info'] ? 'hide' : ''; ?>">Date and Time</th>
    <?php if ($options['dropdown']): ?>
    <th class="links"></th>
    <?php endif; ?>
  </tr>
<?php endif; // table ?>
  <?php foreach ($data as $record): ?>
  <?php if ($options['rows']): ?>
  <tr id="<?php echo $record::$type.'-'.$record->id; ?>" class="<?php echo $record::$type.'-'.$record->id; ?> <?php echo SGS::odd_even($odd); ?>">
    <?php if ($options['actions']): ?>
    <td class="checkbox"><input type="checkbox" name="action" value="<?php echo $record->id; ?>" /></td>
    <?php endif; ?>
    <td class="data-row-details type"><span class="data-type"><?php echo $form_type; ?></span></td>
    <td class="status">
      <?php
        if (!$record->is_verification()) switch ($record->status):
          case 'P': echo HTML::image('images/bullet_yellow.png', array('class' => 'status pending', 'title' => 'Unchecked')); break;
          case 'A': echo HTML::image('images/bullet_green.png', array('class' => 'status accepted', 'title' => 'Passed')); break;
          case 'R': echo HTML::image('images/bullet_red.png', array('class' => 'status rejected', 'title' => 'Failed')); break;
        endswitch;
        if (($verification = $record->verification()) and $verification->loaded()) switch ($verification->status):
          case 'P': echo HTML::image('images/bullet_check_yellow.png', array('class' => 'status pending', 'title' => 'Unverified')); break;
          case 'A': echo HTML::image('images/bullet_check.png', array('class' => 'status accepted', 'title' => 'Accurate')); break;
          case 'R': echo HTML::image('images/bullet_check_red.png', array('class' => 'status rejected', 'title' => 'Inaccurate')); break;
        endswitch;
        if ($record->is_locked()) echo HTML::image('images/bullet_locked.png', array('class' => 'locked', 'title' => 'Locked'));
        if ($record->has_problem()) echo HTML::image('images/bullet_exclamation.png', array('class' => 'problem', 'title' => 'Problems'));
      ?>
    </td>
    <?php foreach ($fields as $field => $name): ?>
    <?php
      if ($options['header'] or $options['hide_header_info']) switch ($field):
        case 'operator_tin':
        case 'operator_id':
        case 'transport_operator_tin':
        case 'transport_operator_id':
        case 'site_name':
        case 'site_id':
        case 'block_name':
        case 'block_id':
        case 'specs_barcode':
        case 'specs_barcode_id';
        case 'specs_number':
        case 'exp_barcode':
        case 'exp_barcode_id':
        case 'exp_number':
          continue 2;
      endswitch;
    ?>
   <?php
      if ($options['hide_hidden_info']) switch ($field):
        case 'enumerator':
        case 'buyer':
        case 'driver':
        case 'origin_date':
        case 'destination_date':
        case 'unloading_date':
        case 'entered_date':
        case 'checked_date':
        case 'loading_date':
        case 'loading_supervised_by':
        case 'receiving_supervised_by':
        case 'measured_by':
        case 'entered_by':
        case 'checked_by':
        case 'signed_by':
        case 'submitted_by':
        case 'contract_number':
        case 'truck_number':
        case 'form_number':
        case 'is_requested':
        case 'is_fda_approved':
        case 'fda_remarks':
        case 'comment':
        case 'action':
          $hidden_column = TRUE; break;
        default:
          $hidden_column = FALSE; break;
      endswitch;
    ?>
    <?php
      $errors   = $record->get_errors();
      $warnings = $record->get_warnings();
    ?>
    <td class="<?php echo $hidden_column ? 'hide' : ''; ?> <?php if ($errors[$field]) print 'error'; else if ($warnings[$field]) print 'warning'; ?>">
      <div id="<?php echo implode('-', array($record::$type, $record->id, $field)); ?>" class="<?php if (Auth::instance()->logged_in('management') and !strpos($field, '_id')): ?>data-eip eip<?php endif; ?>"><?php
        switch ($field):
          case 'operator_id':
            if ($record->operator) echo $record->operator->name; break;

          case 'site_id':
            if ($record->site) echo $record->site->name; break;

          case 'block_id':
            if ($record->block) echo $record->block->name; break;

          case 'species_id':
            if ($record->species) echo $record->species->code; break;

          case 'barcode_id':
          case 'tree_barcode_id':
          case 'stump_barcode_id':
          case 'exp_barcode_id':
          case 'specs_barcode_id':
          case 'parent_barcode_id':
            echo ORM::factory('barcode', $record->$field)->barcode; break;

          case 'create_date':
            echo SGS::date($record->$field); break;

          case 'is_fda_approved':
          case 'is_requested':
            echo $record->$field ? 'YES' : 'NO'; break;

          case 'bottom_min':
          case 'bottom_max':
          case 'top_min':
          case 'top_max':
            echo SGS::floatify($record->$field); break;

          case 'height':
          case 'length':
            echo SGS::floatify($record->$field, 1); break;

          case 'volume':
            echo SGS::quantitify($record->$field); break;

          case 'origin':
          case 'destination':
            echo SGS::locationify($record->$field); break;

          default:
            echo $record->$field; break;
        endswitch;
      ?></div>
    </td>
    <?php endforeach; ?>
    <td class="<?php echo $options['hide_upload_info'] ? 'hide' : ''; ?>"><?php echo ORM::factory('user', $record->user_id)->name; ?></td>
    <td class="<?php echo $options['hide_upload_info'] ? 'hide' : ''; ?>"><?php echo SGS::datetime($record->timestamp); ?></td>
    <?php if ($options['dropdown']): ?>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor('analysis/review/'.strtolower($record::$type).'/'.$record->id, 'View', array('class' => 'link')); ?>
          <?php if ($options['links']): ?>
          <?php if (Auth::instance()->logged_in('data')) if ($record->csv()->loaded()) echo HTML::anchor($record->csv()->data_type.'/data/'.$record->csv()->id, 'View CSV', array('class' => 'link')); ?>
          <?php if (Auth::instance()->logged_in('management')) echo HTML::anchor('analysis/review/'.strtolower($record::$type).'/'.$record->id.'/edit', 'Edit', array('class' => 'link')); ?>
          <?php echo HTML::anchor('analysis/review/'.strtolower($record::$type).'/'.$record->id.'/delete', 'Delete', array('class' => 'link')); ?>
          <?php if ($record->is_verification()): ?>
          <?php $record_data = $record->data(); if ($record_data->loaded()) echo HTML::anchor('analysis/review/'.strtolower($record_data::$type).'/'.$record->data()->id, 'View Data', array('class' => 'link')); ?>
          <span id="<?php echo $record::$type; ?>-<?php echo $record->id; ?>-check" class="link data-check">Verify</span>
          <?php else: ?>
          <span id="<?php echo $record::$type; ?>-<?php echo $record->id; ?>-check" class="link data-check">Check</span>
          <?php if (Auth::instance()->logged_in('barcodes')) echo HTML::anchor('manage/barcodes/'.$record->barcode->id, 'Barcode', array('class' => 'link')); ?>
          <?php echo HTML::anchor('analysis/review/'.strtolower($record::$type).'/'.$record->id.'/hierarchy', 'Hierarchy', array('class' => 'link')); ?>
          <?php echo HTML::anchor('analysis/review/'.strtolower($record::$type).'/'.$record->id.'/revisions', 'Revisions', array('class' => 'link')); ?>
          <?php endif; // verification ?>
          <?php if (Auth::instance()->logged_in('management')): ?><span id="<?php echo implode('-', array($record::$type, $record->id, 'status-update')); ?>" class="link data-status-update-link">Status</span><?php endif; ?>
          <?php if (Auth::instance()->logged_in('management')): ?><span id="<?php echo implode('-', array($record::$type, $record->id, 'activity-update')); ?>" class="link data-activity-update-link">Activity</span><?php endif; ?>
          <?php endif; // links ?>
          <?php if ($options['details']): ?>
          <span class="link toggle-details">Details</span>
          <?php endif; // details-links ?>
        </div>
      </div>
    </td>
    <?php endif; ?>
  </tr>
  <?php endif; // rows ?>
  <?php if ($options['details']): ?>
  <?php
    $errors    = $record->get_errors(TRUE, FALSE);
    $warnings  = $record->get_warnings(TRUE, FALSE);
    $successes = $record->get_successes(TRUE, FALSE);
    
    if ($form_type == 'SPECS' and $record->status == 'R') {
      $ldf = ORM::factory('LDF')
        ->where('barcode_id', '=', $record->barcode->id)
        ->find();
      $ldf_errors = $ldf->get_errors(TRUE, FALSE);
    } else {
      $ldf = $form;
      $ldf_errors = array();
    }
  ?>
  <tr class="details <?php echo $odd ? 'odd' : 'even'; ?>">
    <td colspan="<?php echo (count($fields) + $additional_columns - $header_columns); ?>">
      <table class="details-checks">
        <tr class="head">
          <th class="result">Result</th>
          <th class="type">Type</th>
          <th>Description</th>
          <th>Fields Checked</th>
          <th class="value">Value</th>
          <th class="value">Comparison</th>
        </tr>
        <?php
          foreach ($record::$checks as $type => $info) /* if (!in_array($type, array('consistency', 'reliability'))) */
          foreach ($info['checks'] as $check => $array):
        ?>
        <tr>
          <td class="result">
            <?php if ($record->status == 'P' or !in_array($check, array_keys($errors + $warnings + $successes + $ldf_errors))): $desc = 'title'; ?>
            <div class="warning">Unchecked</div>

            <?php elseif (in_array($check, array_keys($ldf_errors))): $sts = 'F'; $desc = 'error'; ?>
            <div class="error">Failed</div>

            <?php elseif (in_array($check, array_keys($errors))): $sts = 'E'; $desc = 'error'; ?>
            <div class="error">Failed</div>

            <?php elseif (in_array($check, array_keys($warnings))): $sts = 'W'; $desc = 'warning'; ?>
            <div class="warning">Warned</div>

            <?php else: $sts = 'S'; $desc = 'title'; ?>
            <div class="success">Passed</div>
            <?php endif; ?>
          </td>
          <td class="type"><span class="data-type"><?php print $info['title']; ?></span></td>
          <td><?php 
            if ($sts == 'F') print $ldf::$checks[$type]['checks'][$check][$desc];
            else print $array[$desc]; 
          ?></td>
          <td>
            <?php
              $fld  = NULL;
              $flds = array();
              if ($sts == 'E') foreach (array_filter(array_unique(array_keys((array) $errors[$check]))) as $fld) $flds[] = $fields[$fld];
              else if ($sts == 'F') foreach (array_filter(array_unique(array_keys((array) $ldf_errors[$check]))) as $fld) $flds[] = $fields[$fld];
              else if ($sts == 'W') foreach (array_filter(array_unique(array_keys((array) $warnings[$check]))) as $fld) $flds[] = $fields[$fld];
              else if ($sts == 'S') foreach (array_filter(array_unique(array_keys((array) $successes[$check]))) as $fld) $flds[] = $fields[$fld];
              if ($flds) print SGS::implodify($flds);
            ?>
          </td>
          <td class="value">
            <?php
              if ($ldf_errors[$check]) print $ldf_errors[$check][$fld]['value'];
              else if ($errors[$check]) print $errors[$check][$fld]['value'];
              else if ($warnings[$check]) print $warnings[$check][$fld]['value'];
              else if ($successes[$check]) print $successes[$check][$fld]['value'];
            ?>
          </td>
          <td class="value">
            <?php
              if ($ldf_errors[$check]) print $ldf_errors[$check][$fld]['comparison'];
              else if ($errors[$check]) print $errors[$check][$fld]['comparison'];
              else if ($warnings[$check]) print $warnings[$check][$fld]['comparison'];
              else if ($successes[$check]) print $successes[$check][$fld]['comparison'];
            ?>
          </td>
          <?php endforeach; ?>
        </tr>
      </table>
    </td>
  </tr>
  <?php endif; // details ?>
  <?php endforeach; ?>
<?php if ($options['table']): ?>
</table>
<?php endif; ?>