<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'number')), 'Print Job'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'site_id')), 'Site'); ?></th>
    <th>Operator</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($printjobs as $printjob): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $printjob->number; ?></td>
    <td><?php echo $printjob->site->name; ?></td>
    <td><?php echo $printjob->site->operator->name; ?></td>
    <td class="links">
      <?php echo HTML::anchor('barcodes/'.$printjob->id.'/list', 'List Barcodes', array('class' => 'link')); ?>
      <?php echo HTML::anchor('barcodes/'.$printjob->id.'/download', 'Download', array('class' => 'link')); ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
