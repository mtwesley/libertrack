<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th>Print Job</th>
    <th>Barcode</th>
    <th>Operator</th>
    <th>Site</th>
    <th>Type</th>
    <th>Locked</th>
  </tr>
  <?php foreach ($barcodes as $barcode): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $barcode->printjob->number; ?></td>
    <td><?php echo $barcode->barcode; ?></td>
    <td><?php echo $barcode->printjob->site->operator->name; ?></td>
    <td><?php echo $barcode->printjob->site->name; ?></td>
    <td><?php echo SGS::value($barcode->type, 'barcode_type', 'Unknown'); ?></td>
    <td><?php echo $barcode->is_locked ? 'YES' : 'NO'; ?></td>
  </tr>
  <?php endforeach; ?>
</table>
