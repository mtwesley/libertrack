<?php

if ($block and !$site) $site = $block->site;
if ($site  and !$operator) $operator = $site->operator;

$options = (array) $options + array(
  'table'    => TRUE,
  'rows'     => TRUE,
  'details'  => TRUE,
  'links'    => TRUE,
  'actions'  => FALSE,
  'resolve'  => FALSE,
  'header'   => $site or $operator ? TRUE : FALSE,
  'dropdown' => TRUE,
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
<table class="data <?php echo SGS::render_classes($classes); ?>">
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
    <th class="<?php echo $hidden_column ? 'hide' : ''; ?>"><?php echo $name; ?></th>
    <?php endforeach; ?>
    <th class="<?php echo $options['hide_upload_info'] ? 'hide' : ''; ?>">Uploaded By</th>
    <th class="<?php echo $options['hide_upload_info'] ? 'hide' : ''; ?>">Date and Time</th>
    <?php if ($options['dropdown']): ?>
    <th class="links"></th>
    <?php endif; ?>
  </tr>
<?php endif; // table ?>
  <?php foreach ($csvs as $csv): ?>
  <?php if ($options['rows']): ?>
  <?php
    $errors = $csv->get_errors();
    $duplicates = $csv->get_duplicates();
  ?>
  <tr id="csv-<?php echo $csv->id; ?>" class="csv-<?php echo $csv->id; ?> <?php print SGS::odd_even($odd); ?>">
    <?php if ($options['actions']): ?>
    <td class="checkbox"><input type="checkbox" name="action" value="<?php echo $csv->id; ?>" /></td>
    <?php endif; ?>
    <td class="csv-row-details type"><span class="data-type"><?php echo $csv->form_type; ?></span></td>
    <td class="status">
      <?php
        switch ($csv->status):
          case 'P': echo HTML::image('images/flag_yellow.png', array('class' => 'status pending', 'title' => 'Pending')); break;
          case 'A': echo HTML::image('images/flag_green.png', array('class' => 'status accepted', 'title' => 'Accepted')); break;
          case 'R': echo HTML::image('images/flag_red.png', array('class' => 'status rejected', 'title' => 'Rejected')); break;
          case 'U': echo HTML::image('images/flag_blue.png', array('class' => 'status duplicated', 'title' => 'Duplicated')); break;
          case 'D': echo HTML::image('images/flag_grey.png', array('class' => 'status deleted', 'title' => 'Deleted')); break;
        endswitch;
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
    <td class="<?php echo $hidden_column ? 'hide' : ''; ?> <?php if ($errors[$field] or $errors[SGS::fieldify($field)]): ?>error<?php endif; ?> <?php if ($duplicates[$field] or $duplicates[SGS::fieldify($field)]): ?>duplicate<?php endif; ?>">
      <div id="<?php echo implode('-', array('csv', $csv->id, $field)); ?>" class="<?php if ((Auth::instance()->logged_in('management') or ($csv->form_type == 'SSF' and Auth::instance()->get_user()->id == 10) /** FIXME: give albert access to SSF **/ ) and in_array($csv->status, array('P', 'R', 'U'))): ?>csv-eip eip<?php endif; ?>"><?php echo trim($csv->values[$field]); ?></div>
    </td>
    <?php endforeach; ?>
    <td class="<?php echo $options['hide_upload_info'] ? 'hide' : ''; ?>"><?php echo ORM::factory('user', $csv->user_id)->name; ?></td>
    <td class="<?php echo $options['hide_upload_info'] ? 'hide' : ''; ?>"><?php echo SGS::datetime($csv->timestamp); ?></td>
    <?php if ($options['dropdown']): ?>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor($csv->data_type.'/data/'.$csv->id, 'View', array('class' => 'link')); ?>
          <?php if ($options['links']): ?>
          <?php if (Auth::instance()->logged_in('analysis') and $csv->form_data_id) echo HTML::anchor('analysis/review/'.strtolower($csv->form_type).'/'.$csv->form_data_id, 'View '.$csv->form_type, array('class' => 'link')); ?>
          <?php echo HTML::anchor($csv->data_type.'/files/'.$csv->file->id, 'View File', array('class' => 'link')); ?>
          <?php if (Auth::instance()->logged_in('management') and in_array($csv->status, array('P', 'R', 'U'))) echo HTML::anchor($csv->data_type.'/data/'.$csv->id.'/edit', 'Edit', array('class' => 'link')); ?>
          <?php if (Auth::instance()->logged_in('management')) echo HTML::anchor($csv->data_type.'/data/'.$csv->id.'/delete', 'Delete', array('class' => 'link')); ?>
          <?php if (in_array($csv->status, array('P', 'R', 'U'))): ?>
          <span id="csv-<?php echo $csv->id; ?>-process" class="link csv-process">Process</span>
          <?php endif; ?>
          <?php echo HTML::anchor($csv->data_type.'/data/'.$csv->id.'/revisions', 'Revisions', array('class' => 'link')); ?>
          <?php endif; // links ?>
          <?php if ($options['details'] and $errors = $csv->get_errors()): ?>
          <span id="csv-<?php echo $csv->id; ?>-details" class="link toggle-details">Details</span>
          <?php endif; ?>
        </div>
      </div>
    </td>
    <?php endif; ?>
  </tr>
  <?php endif; // rows ?>
  <?php if ($options['details']): ?>
  <?php if ($errors = $csv->get_errors()): ?>
  <tr class="details <?php echo $odd ? 'odd' : 'even'; ?>">
    <td colspan="<?php echo (count($fields) + $additional_columns - $header_columns); ?>">
      <div class="details-errors">
        <ul>
          <?php if ($duplicates = $csv->get_duplicates() and $duplicates['all']): ?>
          <li>Imported data must be unique, but is an exact duplicate of <?php echo count($duplicates['all']); ?> already accepted form data</li>
          <?php endif; ?>
          <?php foreach ($errors as $field => $array): ?>
          <?php foreach ((array) $array as $error): ?>
          <li>
            <?php if ($csv->status == 'U'): ?>
            <span id="csv-<?php echo $csv->id.'-'.$field.'-'.$error; ?>-resolutions" class="details-link details-resolutions-link">Resolutions</span>
            <?php endif; ?>
            <span id="csv-<?php echo $csv->id.'-'.$field.'-'.$error; ?>-suggestions" class="details-link details-suggestions-link">Suggestions</span>
            <span id="csv-<?php echo $csv->id.'-'.$field.'-'.$error; ?>-tips" class="details-link details-tips-link">Tips</span>
            <?php echo SGS::decode_error($field, $error, array(':field' => $fields[$field] ?: $fields[substr($field, 0, strrpos($field, '_id'))]), $values); ?>
          </li>
          <?php endforeach; ?>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="clear"></div>
    </td>
  </tr>
  <?php endif; // get_errors ?>
  <?php endif; // details ?>
  <?php endforeach; ?>
<?php if ($options['table']): ?>
</table>
<?php endif; ?>