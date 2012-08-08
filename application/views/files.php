<table border="1">
  <tr>
    <td><strong>Name</strong></td>
    <td><strong>Type</strong></td>
    <td><strong>Size</strong></td>
    <td><strong>Operation</strong></td>
    <td><strong>Operation Type</strong></td>

    <?php if ($mode == 'import'): ?>
    <td><strong>Statistics</strong></td>
    <?php endif; ?>

    <td><strong>Created</strong></td>
    <td></td>
  </tr>
  <?php foreach ($files as $file): ?>
  <tr>
    <td><?php print $file->name; ?></td>
    <td><?php print $file->type; ?></td>
    <td><?php print Num::unbytes($file->size); ?></td>
    <td><?php print $operation = SGS::value($file->operation, 'operation', 'U'); ?></td>
    <td><?php print SGS::value($file->operation_type, 'operation_type', 'UNKWN'); ?></td>
    <td><?php print SGS::datetime($file->timestamp); ?></td>

    <?php if ($mode == 'import'): ?>
    <?php
      $_p = (int) $file->csv->where('status', '=', 'P')->find_all()->count();
      $_a = (int) $file->csv->where('status', '=', 'A')->find_all()->count();
      $_r = (int) $file->csv->where('status', '=', 'R')->find_all()->count();

      $_total = $_p + $_a + $_r;

      $_pp = $_total ? number_format($_p * 100 / $_total) : 0;
      $_ap = $_total ? number_format($_a * 100 / $_total) : 0;
      $_rp = $_total ? number_format($_r * 100 / $_total) : 0;
    ?>
    <td>
      <?php print $_p; ?> Pending (<?php print $_pp; ?>%)<br />
      <?php print $_a; ?> Accepted (<?php print $_ap; ?>%)<br />
      <?php print $_r; ?> Rejected (<?php print $_rp; ?>%)
    </td>
    <?php endif; ?>

    <td>
      <?php if (in_array($file->operation, array('I','E'))) print HTML::anchor(strtolower($operation).'/files/'.$file->id.'/review', 'Review'); ?>
      <?php if ($mode == 'import') print HTML::anchor('import/files/'.$file->id.'/process', 'Process'); ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>