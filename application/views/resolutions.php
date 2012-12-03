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
        <span id="csv-<?php echo $csv->id.'-duplicate-'.$duplicate->id.'-resolve'; ?>">Select</span>
        <?php echo HTML::anchor('import/data/'.$duplicate->id.'/edit', 'Edit'); ?>
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

