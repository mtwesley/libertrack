<?php if ($errors): ?>
<div class="details-errors">
  <ul>
    <?php foreach ($errors as $field => $array): ?>
    <?php foreach ((array) $array as $error): ?>
    <li>
      <?php echo SGS::decode_error($field, $error, array(':field' => $fields[$field]), $values); ?>
      <?php if ($csv->status == 'U'): ?>
      <span id="csv-<?php echo $csv->id.'-'.$field.'-'.$error; ?>-resolutions" class="details-link details-resolutions-link">Resolutions</span>
      <?php endif; ?>
      <span id="csv-<?php echo $csv->id.'-'.$field.'-'.$error; ?>-suggestions" class="details-link details-suggestions-link">Suggestions</span>
      <span id="csv-<?php echo $csv->id.'-'.$field.'-'.$error; ?>-tips" class="details-link details-tips-link">Tips</span>
    </li>
    <?php endforeach; ?>
    <?php endforeach; ?>
  </ul>
</div>
<div class="clear"></div>
<?php endif; ?>