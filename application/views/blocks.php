<table class="data">
  <tr class="head">
    <th>Name</th>
    <th>Site</th>
    <th>Operator</th>
    <th></th>
  </tr>
  <?php foreach ($blocks as $block): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $block->coordinates; ?></td>
    <td><?php echo $block->site->name; ?></td>
    <td><?php echo $block->site->operator->name; ?></td>
    <td><?php echo HTML::anchor('admin/blocks/'.$block->id.'/edit', 'Edit', array('class' => 'link')); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
