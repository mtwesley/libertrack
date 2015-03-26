<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'number')), 'Print Job'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'type')), 'Type'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'site_id')), 'Site'); ?></th>
    <th>Operator</th>
    <th>Monitored</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($printjobs as $printjob): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type">PJ</span></td>
    <td class="status"><?php echo HTML::image('images/printjobs.png', array('class' => 'barcode', 'title' => 'Print Job')); ?></td>
    <td><?php echo $printjob->number; ?></td>
    <td><?php echo $printjob->type ? SGS::$printjob_type[$printjob->type] : ''; ?></td>
    <td><?php echo $printjob->site->name; ?></td>
    <td><?php echo $printjob->site->operator->name; ?></td>
    <td><?php echo SGS::booleanify($printjob->is_monitored) ? 'YES' : 'NO'; ?></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor('manage/printjobs/'.$printjob->id, 'View', array('class' => 'link')); ?>
          <?php echo HTML::anchor('manage/printjobs/'.$printjob->id.'/edit', 'Edit', array('class' => 'link')); ?>
          <?php echo HTML::anchor('manage/printjobs/'.$printjob->id.'/download', 'Download', array('class' => 'link')); ?>
          <?php echo HTML::anchor('manage/printjobs/'.$printjob->id.'/labels', 'Labels', array('class' => 'link')); ?>
          <?php echo HTML::anchor('manage/printjobs/'.$printjob->id.'/tags', 'Tags', array('class' => 'link')); ?>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
