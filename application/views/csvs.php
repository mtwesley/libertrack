<?php

if ($block and !$site) $site = $block->site;
if ($site  and !$operator) $operator = $site->operator;

$options = (array) $options + array(
  'table'   => TRUE,
  'rows'    => TRUE,
  'details' => TRUE,
  'links'   => TRUE,
  'actions' => FALSE,
  'resolve' => FALSE,
  'header'  => $site ? TRUE : FALSE,
);

$header_columns = 0;
$additional_columns = 3;

if ($options['actions']) $classes[] = 'has-actions';
if ($options['header'])  $classes[] = 'has-header';

?>
<?php if ($options['header']): ?>
<table class="data-header">
  <tr>
    <td class="label">Operator:</td>
    <td><?php if ($operator) echo $operator->name; ?></td>
    <td class="label">Site:</td>
    <td><?php if ($site) echo $site->name; ?></td>
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
        case 'site_name':
        case 'site_id':
        case 'block_name':
        case 'block_id':
          $header_columns++;
          continue 2;
      endswitch;
    ?>
    <th><?php echo $name; ?></th>
    <?php endforeach; ?>
    <th class="links"></th>
  </tr>
<?php endif; // table ?>
  <?php foreach ($csvs as $csv): ?>
  <?php if ($options['rows']): ?>
  <?php $errors = $csv->get_errors(); ?>
  <tr id="csv-<?php echo $csv->id; ?>" class="<?php print SGS::odd_even($odd); ?>">
    <?php if ($options['actions']): ?>
    <td class="checkbox"><input type="checkbox" name="action" value="<?php echo $csv->id; ?>" /></td>
    <?php endif; ?>
    <td class="type"><span class="data-type"><?php echo $csv->form_type; ?></span></td>
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
        case 'site_name':
        case 'site_id':
        case 'block_name':
        case 'block_id':
          continue 2;
      endswitch;
    ?>
    <td class="<?php if ($errors[$field]): ?>error<?php endif; ?>">
      <div class="<?php if ($mode == 'import' AND in_array($csv->status, array('P', 'R', 'U'))): ?>csv-eip eip<?php endif; ?>"
           id="<?php echo implode('-', array('csv', $csv->id, $field)); ?>"><?php echo trim($csv->values[$field]); ?></div>
    </td>
    <?php endforeach; ?>
    <td class="links">
      <?php if ($options['links']): ?>
      <?php echo HTML::anchor('import/data/'.$csv->id.'/view', 'View', array('class' => 'link')); ?>

      <?php if (in_array($csv->status, array('P', 'R', 'U'))): ?>
      <?php echo HTML::anchor('import/data/'.$csv->id.'/edit', 'Edit', array('class' => 'link')); ?>
      <?php endif; ?>

      <?php if ($mode == 'import') echo HTML::anchor('import/data/'.$csv->id.'/delete', 'Delete', array('class' => 'link')); ?>

      <?php if ($mode == 'import' AND in_array($csv->status, array('P', 'R', 'U'))): ?>
      <span id="csv-<?php echo $csv->id; ?>-process" class="link csv-process">Process</span>
      <!-- <?php echo HTML::anchor('import/data/'.$csv->id.'/process', 'Process', array('class' => 'link')); ?> -->
      <?php endif; ?>

      <?php if ($options['details'] and $errors): ?>
      <span id="csv-<?php echo $csv->id; ?>-details" class="link toggle-details">Details</span>
      <?php endif; ?>

      <?php endif; ?>
    </td>
  </tr>
  <?php endif; // rows ?>
  <?php if ($options['details']): ?>
  <?php if ($errors = $csv->get_errors()): ?>
  <tr class="details <?php echo $odd ? 'odd' : 'even'; ?>">
    <td colspan="<?php echo (count($fields) + $additional_columns - $header_columns); ?>">
      <div class="details-errors">
        <ul>
          <?php foreach ($errors as $field => $array): ?>
          <?php foreach ((array) $array as $error): ?>
          <li>
            <?php echo SGS::decode_error($field, $error, array(':field' => $fields[$field]), $values); ?>
            <?php if ($csv->status == 'U'): ?>
            <span id="csv-<?php echo $csv->id.'-'.$field.'-'.$error; ?>-resolutions" class="details-link details-resolutions-link">Resolutions</span>
            <?php endif; ?>
            <span id="csv-<?php echo $csv->id.'-'.$field.'-'.$error; ?>-suggestions" class="details-link details-suggestions-link">Suggestions</span>
            <span id="csv-<?php echo $csv->id.'-'.$field.'-'.$error; ?>-tips" class="details-link details-tips-link">Tips</span>
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