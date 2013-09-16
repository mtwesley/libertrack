<?php
$classes[] = 'data';
?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="image"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'number')), 'Reference Number'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'name')), 'Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'model')), 'Base Model'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'created_date')), 'Created'); ?></th>
    <th class="links"></th>
  </tr>
  <?php foreach ($reports as $report): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type"><?php echo $report->type; ?></span></td>
    <td class="image"><?php echo HTML::image('images/report.png'); ?></td>
    <td><?php echo $report->is_draft ? 'DRAFT' : $type.' '.$report->number; ?></td>
    <td><?php echo $report->name; ?></td>
    <td><?php echo $report::$model[$report->model]; ?></td>
    <td><?php echo SGS::date($report->created_date); ?></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor('reports/'.$report->id, 'View', array('class' => 'link')); ?>
          <?php if ($report->is_draft): ?>
          <?php echo HTML::anchor('reports/'.$report->id.'/delete', 'Delete', array('class' => 'link')); ?>
          <?php echo HTML::anchor('reports/'.$report->id.'/finalize', 'Finalize', array('class' => 'link')); ?>
          <?php endif; ?>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
