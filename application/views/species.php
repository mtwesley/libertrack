<table class="data">
  <tr class="head">
    <th>Code</th>
    <th>Botanic Name</th>
    <th>Trade Name</th>
    <th>Class</th>
    <th>FOB Price</th>
    <th></th>
  </tr>
  <?php foreach ($species as $spcs): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $spcs->code; ?></td>
    <td><?php echo $spcs->botanic_name; ?></td>
    <td><?php echo $spcs->trade_name; ?></td>
    <td><?php echo $spcs->class; ?></td>
    <td><?php echo $spcs->fob_price; ?></td>
    <td><?php echo HTML::anchor('admin/species/'.$spcs->id.'/edit', 'Edit', array('class' => 'link')); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
