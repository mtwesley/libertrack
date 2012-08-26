<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th>Print Job</th>
    <th>Operator</th>
    <th>Site</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($printjobs as $printjob): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $printjob->number; ?></td>
    <td><?php echo $printjob->site->operator->name; ?></td>
    <td><?php echo $printjob->site->name; ?></td>
    <td class="links">
      <?php echo HTML::anchor('admin/printjobs/'.$printjob->id.'/barcodes', 'View Barcodes', array('class' => 'link')); ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
