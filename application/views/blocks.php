<table border="1">
  <tr>
    <td><strong>Operator</strong></td>
    <td><strong>Site</strong></td>
    <td><strong>Block Name</strong></td>
    <td></td>
  </tr>
  <?php foreach ($blocks as $block): ?>
  <tr>
    <td><?php print $block->site->operator->name; ?></td>
    <td><?php print $block->site->name; ?></td>
    <td><?php print $block->coordinates; ?></td>
    <td><?php print HTML::anchor('admin/blocks/'.$block->id.'/edit', 'edit'); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
