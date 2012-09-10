<?php $classes[] = 'data'; ?>
<?php if ($title): ?>
<p><strong><?php echo $title; ?>:</strong></p>
<?php endif; ?>

<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th></th>
    <th>Type</th>
    <?php foreach ($fields as $name): ?>
    <th><?php echo $name; ?></th>
    <?php endforeach; ?>
    <th class="links"></th>
  </tr>
  <?php foreach ($csvs as $csv): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
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
    <td><?php echo $csv->form_type; ?></td>
    <?php foreach ($fields as $key => $name): ?>
    <td><?php echo $csv->values[$key]; ?></td>
    <?php endforeach; ?>
    <td class="links">
      <?php echo HTML::anchor('import/data/'.$csv->id.'/view', 'View', array('class' => 'link')); ?>

      <?php if (in_array($csv->status, array('P', 'R', 'U'))): ?>
      <?php echo HTML::anchor('import/data/'.$csv->id.'/edit', 'Edit', array('class' => 'link')); ?>
      <span class="link toggle-edit">EIP</span>
      <?php endif; ?>

      <?php if ($mode == 'import') echo HTML::anchor('import/data/'.$csv->id.'/delete', 'Delete', array('class' => 'link')); ?>

      <?php if ($mode == 'import' AND in_array($csv->status, array('P', 'R'))): ?>
      <?php echo HTML::anchor('import/data/'.$csv->id.'/process', 'Process', array('class' => 'link')); ?>
      <?php endif; ?>

      <?php if ($csv->errors or $csv->suggestions): ?>
      <span class="link toggle-details">Details</span>
      <?php endif; ?>
    </td>
  </tr>
  <?php if ($mode == 'import' AND in_array($csv->status, array('P', 'R', 'U'))): ?>
  <tr class="edit <?php echo $odd ? 'odd' : 'even'; ?>">
    <td colspan="<?php echo (count($fields) + 3); ?>">
      <?php echo $csv->get_form(); ?>
    </td>
  </tr>
  <?php endif; ?>
  <?php if ($csv->errors or $csv->suggestions or $csv->duplicates): ?>
  <tr class="details <?php echo $odd ? 'odd' : 'even'; ?>">
    <td colspan="<?php echo (count($fields) + 3); ?>">
      <?php
          if ($csv->errors) echo View::factory('errors')
            ->set('fields', $fields)
            ->set('errors', $csv->errors)
            ->render();
      ?>
      <?php
          if ($csv->suggestions) echo View::factory('suggestions')
            ->set('fields', $fields)
            ->set('suggestions', $csv->suggestions)
            ->render();
      ?>
      <?php
          if ($csv->duplicates) echo View::factory('duplicates')
            ->set('fields', $fields)
            ->set('duplicates', $csv->duplicates)
            ->render();
      ?>
    </td>
  </tr>
  <?php endif; ?>
  <?php endforeach; ?>
</table>