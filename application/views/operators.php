<table border="1">
  <tr>
    <td><strong>TIN</strong></td>
    <td><strong>Name</strong></td>
    <td><strong>Contact</strong></td>
    <td><strong>Address</strong></td>
    <td><strong>E-mail</strong></td>
    <td><strong>Phone Number</strong></td>
    <td></td>
  </tr>
  <?php foreach ($operators as $operator): ?>
  <tr>
    <td><?php print $operator->tin; ?></td>
    <td><?php print $operator->name; ?></td>
    <td><?php print $operator->contact; ?></td>
    <td><?php print $operator->address; ?></td>
    <td><?php print $operator->email; ?></td>
    <td><?php print $operator->phone; ?></td>
    <td><?php print HTML::anchor('admin/operators/'.$operator->id.'/edit', 'edit'); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
