<style type="text/css">
  .pending {
    color: #aa7700;
  }

  .accepted {
    color: #008200;
  }

  .rejected {
    color: #990000;
  }
</style>
<table class="data">
  <tr class="head">
    <th>Name</th>
    <!-- <th>Type</th> -->
    <th>Size</th>
    <th>Operation</th>
    <th>Content</th>
    <th>Uploaded</th>

    <?php if ($mode == 'import'): ?>
    <th>Statistics</th>
    <?php endif; ?>

    <th class="links"></th>
  </tr>
  <?php foreach ($files as $file): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $file->name; ?></td>
    <!-- <td><?php echo $file->type; ?></td> -->
    <td><?php echo Num::unbytes($file->size, 0); ?></td>
    <td><?php echo $operation = SGS::value($file->operation, 'operation', 'U'); ?></td>
    <td><?php echo SGS::value($file->operation_type, 'operation_type', 'UNKWN'); ?></td>
    <td><?php echo SGS::datetime($file->timestamp); ?></td>

    <?php if ($mode == 'import'): ?>
    <?php
      $_p = (int) $file->csv->where('status', '=', 'P')->find_all()->count();
      $_a = (int) $file->csv->where('status', '=', 'A')->find_all()->count();
      $_r = (int) $file->csv->where('status', '=', 'R')->find_all()->count();

      $_total = $_p + $_a + $_r;

      $_pp = $_total ? round(($_p * 100 / $_total), 0, PHP_ROUND_HALF_DOWN) : 0;
      $_ap = $_total ? round(($_a * 100 / $_total), 0, PHP_ROUND_HALF_DOWN) : 0;
      $_rp = $_total ? round(($_r * 100 / $_total), 0, PHP_ROUND_HALF_DOWN) : 0;
    ?>
    <td>
      <div class="pending"><?php echo $_p; ?> Pending (<?php echo $_pp; ?>%)</div>
      <div class="accepted"><?php echo $_a; ?> Accepted (<?php echo $_ap; ?>%)</div>
      <div class="rejected"><?php echo $_r; ?> Rejected (<?php echo $_rp; ?>%)</div>
    </td>
    <?php endif; ?>

    <td class="links">
      <?php if (in_array($file->operation, array('I','E'))) echo HTML::anchor(strtolower($operation).'/files/'.$file->id.'/review', 'Review', array('class' => 'link')); ?>
      <?php if ($mode == 'import') echo HTML::anchor('import/files/'.$file->id.'/process', 'Process', array('class' => 'link')); ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>