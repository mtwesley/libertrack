<div id="csv-<?php echo $csv->id; ?>-details-resolutions" class="details-resolutions">
  <div class="details-resolutions-title">Duplicate Resolutions</div>
  <?php if ($duplicates): ?>
  <table class="details-resolution">
    <?php foreach ($csv->values as $field => $value) if ($fields[$field]): ?>
    <tr>
      <td class="details-resolution-label"><?php print $fields[$field]; ?></td>
      <?php $col = 0; foreach ($duplicates as $duplicate): ?>
      <td class="<?php print $columns[$col] ?: $columns[$col] = SGS::odd_even($odd); ?>"><?php print $duplicate->values[$field]; ?></td>
      <?php $col++; ?>
      <?php endforeach; ?>
    </tr>
    <?php endif; ?>
    <tr>
      <td></td>
      <?php $col = 0; foreach ($duplicates as $duplicate): ?>
      <td class="details-resolution-select">
        <?php echo HTML::anchor($csv->data_type.'/data/'.$duplicate->id, 'View'); ?>
        <?php if (Auth::instance()->logged_in('management')) echo HTML::anchor($csv->data_type.'/data/'.$duplicate->id.'/edit', 'Edit'); ?>
        <?php if (Auth::instance()->logged_in('management')): ?>
        <span id="csv-<?php echo $csv->id.'-duplicate-'.$duplicate->id.'-resolve'; ?>">Resolve</span>
        <?php endif; ?>
      </td>
      <?php $col++; ?>
      <?php endforeach; ?>
    </tr>
  </table>

  <?php else: ?>
  <div class="details-no-resolutions">
    Sorry, no resolutions found.
  </div>
  <?php endif; ?>
</div>

