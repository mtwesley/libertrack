<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'printjob_is')), 'Print Job'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'barcode')), 'Barcode'); ?></th>
    <th>Operator</th>
    <th>Site</th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'type')), 'Type'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'is_locked')), 'Locked'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'coc_status')), 'Status'); ?></th>
  </tr>
  <?php foreach ($barcodes as $barcode): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $barcode->printjob->number; ?></td>
    <td><?php echo $barcode->barcode; ?></td>
    <td><?php echo $barcode->printjob->site->operator->name; ?></td>
    <td><?php echo $barcode->printjob->site->name; ?></td>
    <td><?php echo SGS::value($barcode->type, 'barcode_type', 'Unknown'); ?></td>
    <td><?php echo $barcode->is_locked ? 'YES' : 'NO'; ?></td>
    <td><?php echo 'Unknown'; ?></td>
  </tr>
  <?php endforeach; ?>
</table>
