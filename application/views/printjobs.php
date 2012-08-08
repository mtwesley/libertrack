<table border="1">
  <tr>
    <td><strong>Operator</strong></td>
    <td><strong>Site</strong></td>
    <td><strong>Number</strong></td>
    <td></td>
  </tr>
  <?php foreach ($printjobs as $printjob): ?>
  <tr>
    <td><?php print $printjob->site->operator->name; ?></td>
    <td><?php print $printjob->site->name; ?></td>
    <td><?php print $printjob->number; ?></td>
    <td><?php print HTML::anchor('admin/printjobs/'.$printjob->id.'/edit', 'edit'); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
