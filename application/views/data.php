<?php
$fields = ORM::factory($form_type)->labels();
$classes[] = 'data';
?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th></th>
    <th></th>
    <?php foreach ($fields as $name): ?>
    <th><?php echo $name; ?></th>
    <?php endforeach; ?>
    <th class="links"></th>
  </tr>
  <?php foreach ($data as $record): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><span class="data-type"><?php echo $form_type; ?></span></td>
    <td>
      <?php
        switch ($record->status):
          case 'P': echo HTML::image('images/bullet_yellow.png', array('class' => 'status pending', 'title' => 'Pending')); break;
          case 'A': echo HTML::image('images/bullet_green.png', array('class' => 'status accepted', 'title' => 'Accepted')); break;
          case 'R': echo HTML::image('images/bullet_red.png', array('class' => 'status rejected', 'title' => 'Rejected')); break;
        endswitch;
      ?>
    </td>
    <?php foreach ($fields as $key => $name): ?>
    <td>
      <?php
        switch ($key):
          case 'operator_id': echo $record->operator->name; break;
          case 'site_id': echo $record->site->name; break;
          case 'block_id': echo $record->block->name; break;
          case 'species_id': echo $record->species->code; break;

          case 'barcode_id':
          case 'tree_barcode_id':
          case 'stump_barcode_id':
          case 'parent_barcode_id': echo ORM::factory('barcode', $record->$key)->barcode; break;

          // dates
          case 'create_date': echo SGS::date($record->$key); break;

          // booleans
          case 'is_fda_approved':
          case 'is_requested': echo $record->$key ? 'YES' : 'NO'; break;
          default: echo $record->$key; break;
        endswitch;
      ?>
    </td>
    <?php endforeach; ?>
    <td class="links">
      <?php /* echo HTML::anchor('import/data/'.$record->id.'/view', 'View', array('class' => 'link')); ?>
      <?php if (in_array($record->status, array('P', 'R', 'U'))) echo HTML::anchor('import/data/'.$record->id.'/edit', 'Edit', array('class' => 'link')); ?>
      <?php if (in_array($record->status, array('P', 'R'))): ?>
      <?php echo HTML::anchor('import/data/'.$record->id.'/process', 'Process', array('class' => 'link')); ?>
      <?php endif; ?>
      <?php echo HTML::anchor('analysis/data/'.$record->id.'/delete', 'Delete', array('class' => 'link')); */ ?>
      <?php /* if ($record->errors): ?>
      <span class="link toggle-details">Details</span>
      <?php endif; */ ?>
    </td>
  </tr>
  <?php /* if ($record->errors): ?>
  <tr class="details <?php echo $odd ? 'odd' : 'even'; ?>">
    <td colspan="<?php echo (count($fields) + 3); ?>">
      <?php
          if ($record->errors) echo View::factory('errors')
            ->set('fields', $fields)
            ->set('errors', $record->errors)
            ->render();
      ?>
    </td>
  </tr>
  <?php endif; */ ?>
  <?php endforeach; ?>
</table>