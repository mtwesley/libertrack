<table class="data">
  <tr class="head">
    <!-- <th>Type</th> -->
    <!-- <th>Reference</th> -->
    <th>Name</th>
    <th>Operator</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($sites as $site): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <!-- <td><?php echo $site->type; ?></td> -->
    <!-- <td><?php echo $site->reference; ?></td> -->
    <td><?php echo $site->name; ?></td>
    <td><?php echo $site->operator->name; ?></td>
    <td class="links">
      <?php echo HTML::anchor('admin/sites/'.$site->id.'/edit', 'Edit', array('class' => 'link')); ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
