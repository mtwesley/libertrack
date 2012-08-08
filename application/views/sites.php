<table border="1">
  <tr>
    <td><strong>Type</strong></td>
    <td><strong>Reference</strong></td>
    <td><strong>Name</strong></td>
    <td><strong>Operator</strong></td>
    <td></td>
  </tr>
  <?php foreach ($sites as $site): ?>
  <tr>
    <td><?php print $site->type; ?></td>
    <td><?php print $site->reference; ?></td>
    <td><?php print $site->name; ?></td>
    <td><?php print $site->operator->name; ?></td>
    <td><?php print HTML::anchor('admin/sites/'.$site->id.'/edit', 'edit'); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
