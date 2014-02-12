<?php
$mode ? $mode = $mode.'/' : '';
$classes[] = 'data';
?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="image"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'number')), 'Reference Number'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'operator_id')), 'Operator'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'site_id')), 'Site'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'created_date')), 'Created'); ?></th>
    <th class="links"></th>
  </tr>
  <?php foreach ($documents as $document): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type"><?php echo $document->type; ?></span></td>
    <td class="image"><?php
      switch ($document->type) {
        case 'SPECS': $type = 'SPEC'; echo HTML::image('images/page_green.png'); break;
        case 'EXP': $type = 'EP'; echo HTML::image('images/page.png'); break;
      }
    ?></td>
    <td><?php echo $document->is_draft ? 'DRAFT' : $type.' '.$document->number; ?></td>
    <td><?php echo $document->operator->name; ?></td>
    <td><?php echo $document->site->loaded() ? $document->site->name : 'N/A'; ?></td>
    <td><?php echo SGS::date($document->created_date); ?></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor($mode.'documents/'.$document->id, 'View', array('class' => 'link')); ?>

          <?php if ($document->is_draft): ?>
          <?php echo HTML::anchor($mode.'documents/'.$document->id.'/delete', 'Delete', array('class' => 'link')); ?>
          <?php echo HTML::anchor($mode.'documents/'.$document->id.'/finalize', 'Finalize', array('class' => 'link')); ?>
          <?php else: ?>
          <?php if (Auth::instance()->logged_in('management')) echo HTML::anchor($mode.'documents/'.$document->id.'/delete', 'Force Delete', array('class' => 'link')); ?>
          <?php if (Auth::instance()->logged_in('management')) echo HTML::anchor($mode.'documents/'.$document->id.'/refinalize', 'Re-finalize', array('class' => 'link')); ?>
          <?php if ($document->type == 'EXP'): ?>
          <?php echo HTML::anchor($mode.'documents/'.$document->id.'/loading', 'Loading', array('class' => 'link')); ?>
          <?php echo HTML::anchor($mode.'documents/'.$document->id.'/certificate', 'Certificate', array('class' => 'link')); ?>
          <?php endif; ?>
          <?php endif; ?>

          <?php echo HTML::anchor($document->file->path, 'Download', array('class' => 'link')); ?>
          <?php if (!$document->is_draft and $document->type == 'EXP') echo HTML::anchor($mode.'documents/'.$document->id.'/asycuda', 'ASYCUDA', array('class' => 'link')); ?>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
