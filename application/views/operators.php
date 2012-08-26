<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th>TIN</th>
    <th>Name</th>
    <th>Contact</th>
    <th>Address</th>
    <th>E-mail</th>
    <th>Phone Number</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($operators as $operator): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $operator->tin; ?></td>
    <td><?php echo $operator->name; ?></td>
    <td><?php echo $operator->contact; ?></td>
    <td><?php echo $operator->address; ?></td>
    <td><?php echo $operator->email; ?></td>
    <td><?php echo $operator->phone; ?></td>
    <td class="links">
      <?php echo HTML::anchor('admin/operators/'.$operator->id.'/edit', 'Edit', array('class' => 'link')); ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
