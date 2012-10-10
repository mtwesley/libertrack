<?php

$classes[] = 'data';

$options = array(
  'table'   => TRUE,
  'rows'    => TRUE,
  'details' => TRUE,
  'header'  => ($operator or $site or $block) ? TRUE : FALSE
) + (array) $options;

$header_columns = 0;

?>
<?php if ($title): ?>
<p><strong><?php echo $title; ?>:</strong></p>
<?php endif; ?>

<?php if ($options['table']): ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th></th>
    <th></th>
    <?php foreach ($fields as $field => $name): ?>
    <?php
      if ($options['header']) switch ($field):
        case 'create_date':
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
    <td><span class="data-type"><?php echo $csv->form_type; ?></span></td>
    <td>
      <?php
        switch ($csv->status):
          case 'P': echo HTML::image('images/flag_yellow.png', array('class' => 'status pending', 'title' => 'Pending')); break;
          case 'A': echo HTML::image('images/flag_green.png', array('class' => 'status accepted', 'title' => 'Accepted')); break;
          case 'R': echo HTML::image('images/flag_red.png', array('class' => 'status rejected', 'title' => 'Rejected')); break;
          case 'U': echo HTML::image('images/flag_blue.png', array('class' => 'status duplicated', 'title' => 'Duplicated')); break;
        endswitch;
      ?>
    </td>
    <?php foreach ($fields as $field => $name): ?>
    <?php
      if ($options['header']) switch ($field):
        case 'create_date':
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
      <?php echo HTML::anchor('import/data/'.$csv->id.'/view', 'View', array('class' => 'link')); ?>

      <?php if (in_array($csv->status, array('P', 'R', 'U'))): ?>
      <?php echo HTML::anchor('import/data/'.$csv->id.'/edit', 'Edit', array('class' => 'link')); ?>
      <?php endif; ?>

      <?php if ($mode == 'import') echo HTML::anchor('import/data/'.$csv->id.'/delete', 'Delete', array('class' => 'link')); ?>

      <?php if ($mode == 'import' AND in_array($csv->status, array('P', 'R'))): ?>
      <?php echo HTML::anchor('import/data/'.$csv->id.'/process', 'Process', array('class' => 'link')); ?>
      <?php endif; ?>

      <?php if ($errors): ?>
      <span id="csv-<?php echo $csv->id; ?>-details" class="link toggle-details">Details</span>
      <?php endif; ?>
    </td>
  </tr>
  <?php endif; // rows ?>
  <?php if ($options['details']): ?>
  <?php if ($csv->get_errors()): ?>
  <tr class="details <?php echo $odd ? 'odd' : 'even'; ?>">
    <td class="loading" colspan="<?php echo (count($fields) + 3); ?>"></td>
  </tr>
  <?php endif; // get_errors ?>
  <?php endif; // details ?>
  <?php endforeach; ?>
<?php if ($options['table']): ?>
</table>
<?php endif; ?>