<?php
if ($block and !$site) $site = $block->site;
if ($site  and !$operator) $operator = $site->operator;

$options = (array) $options + array(
  'table'   => TRUE,
  'rows'    => TRUE,
  'details' => TRUE,
  'links'   => TRUE,
  'actions' => FALSE,
  'header'  => $site or $operator ? TRUE : FALSE,
);

$header_columns = 0;
$additional_columns = 3;

if ($options['actions']) $classes[] = 'has-actions';
if ($options['header'])  $classes[] = 'has-header';

$fields = ORM::factory($form_type)->labels();
$classes[] = 'data';
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
        case 'site_name':
        case 'site_id':
        case 'block_name':
        case 'block_id':
          $header_columns++;
          continue 2;
      endswitch;
    ?>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => $field)), $name); ?></th>
    <?php endforeach; ?>
    <th class="links"></th>
  </tr>
<?php endif; // table ?>
  <?php foreach ($data as $record): ?>
  <?php if ($options['rows']): ?>
  <tr class="<?php echo $record::$type.'-'.$record->id; ?> <?php echo SGS::odd_even($odd); ?>">
    <?php if ($options['actions']): ?>
    <td class="checkbox"><input type="checkbox" name="action" value="<?php echo $record->id; ?>" /></td>
    <?php endif; ?>
    <td class="type"><span class="data-type"><?php echo $form_type; ?></span></td>
    <td class="status">
      <?php
        switch ($record->status):
          case 'P': echo HTML::image('images/bullet_yellow.png', array('class' => 'status pending', 'title' => 'Unchecked')); break;
          case 'A': echo HTML::image('images/bullet_green.png', array('class' => 'status accepted', 'title' => 'Passed')); break;
          case 'R': echo HTML::image('images/bullet_red.png', array('class' => 'status rejected', 'title' => 'Failed')); break;
        endswitch;
      ?>
    </td>
    <?php
      $errors   = $record->get_errors();
      $warnings = $record->get_warnings();
    ?>
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
    <td class="<?php if ($errors[$field]) print 'error'; else if ($warnings[$field]) print 'warning'; ?>">
      <?php
        switch ($field):
          case 'operator_id': if ($record->operator) echo $record->operator->name; break;
          case 'site_id': if ($record->site) echo $record->site->name; break;
          case 'block_id': if ($record->block) echo $record->block->name; break;
          case 'species_id': if ($record->species) echo $record->species->code; break;

          case 'barcode_id':
          case 'tree_barcode_id':
          case 'stump_barcode_id':
          case 'epr_barcode_id':
          case 'specs_barcode_id':
          case 'parent_barcode_id': echo ORM::factory('barcode', $record->$field)->barcode; break;

          // dates
          case 'create_date': echo SGS::date($record->$field); break;

          // booleans
          case 'is_fda_approved':
          case 'is_requested': echo $record->$field ? 'YES' : 'NO'; break;
          default: echo $record->$field; break;
        endswitch;
      ?>
    </td>
    <?php endforeach; ?>
    <td class="links">
      <?php echo HTML::anchor('analysis/review/'.strtolower($record::$type).'/'.$record->id, 'View', array('class' => 'link')); ?>
      <?php if ($options['links']): ?>
      <?php echo HTML::anchor('analysis/review/'.strtolower($record::$type).'/'.$record->id.'/edit', 'Edit', array('class' => 'link')); ?>
      <?php echo HTML::anchor('analysis/review/'.strtolower($record::$type).'/'.$record->id.'/delete', 'Delete', array('class' => 'link')); ?>
      <?php endif; // links ?>
      <?php if ($options['details']): ?>
      <span class="link toggle-details">Details</span>
      <?php endif; // details-links ?>
    </td>
  </tr>
  <?php endif; // rows ?>
  <?php if ($options['details']): ?>
  <?php
    $errors   = SGS::flattenify($record->get_errors());
    $warnings = SGS::flattenify($record->get_warnings());
  ?>
  <tr class="details <?php echo $odd ? 'odd' : 'even'; ?>">
    <td colspan="<?php echo (count($fields) + $additional_columns - $header_columns); ?>">
      <table class="details-checks">
        <tr>
          <?php
            foreach ($record::$checks as $type => $info)
            foreach ($info['checks'] as $check => $array):
          ?>
          <th><?php print $array['title']; ?></th>
          <?php endforeach; ?>
        </tr>
        <tr>
          <?php
            foreach ($record::$checks as $type => $info)
            foreach ($info['checks'] as $check => $array):
          ?>
          <td>
            <?php if ($record->status == 'P'): ?>
            <div class="warning">Unchecked</div>

            <?php elseif (in_array($check, $errors)): ?>
            <div class="error">Failed</div>

            <?php elseif (in_array($check, $warnings)): ?>
            <div class="warning"><?php print 'Warned'; // $array['warning']; ?></div>

            <?php else: ?>
            <div class="success">Passed</div>
            <?php endif; ?>
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