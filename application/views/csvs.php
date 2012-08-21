<?php

$classes[] = 'data';

?>
<?php if ($title): ?>
<p><strong><?php echo $title; ?>:</strong></p>
<?php endif; ?>

<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th></th>
    <th>Form</th>
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
        endswitch;
      ?>
    </td>
    <td><?php echo $csv->form_type; ?></td>
    <?php foreach ($fields as $key => $name): ?>
    <td><?php echo $csv->values[$key]; ?></td>
    <?php endforeach; ?>
    <td class="links">
      <?php echo HTML::anchor('import/data/'.$csv->id.'/view', 'View', array('class' => 'link')); ?>
      <?php if ($csv->status != 'A'): ?>
      <?php echo HTML::anchor('import/data/'.$csv->id.'/edit', 'Edit', array('class' => 'link')); ?>
      <?php echo HTML::anchor('import/data/'.$csv->id.'/process', 'Process', array('class' => 'link')); ?>
      <span class="link toggle-details">Details</span>
      <?php endif; ?>
    </td>
  </tr>
  <?php if ($csv->errors or $csv->suggestions): ?>
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
    </td>
  </tr>
  <?php endif; ?>
  <?php endforeach; ?>
</table>