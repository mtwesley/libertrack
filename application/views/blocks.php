<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th>Name</th>
    <th>Site</th>
    <th>Operator</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($blocks as $block): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $block->name; ?></td>
    <td><?php echo $block->site->name; ?></td>
    <td><?php echo $block->site->operator->name; ?></td>
    <td class="links"><?php echo HTML::anchor('admin/blocks/'.$block->id.'/edit', 'Edit', array('class' => 'link')); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
