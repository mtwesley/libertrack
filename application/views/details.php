<?php if ($errors): ?>
<div class="details-errors">
  <ul>
    <?php foreach ($errors as $field => $array): ?>
    <?php foreach ((array) $array as $error): ?>
    <li>
      <?php echo SGS::decode_error($field, $error, array(':field' => $fields[$field])); ?>
      <span id="csv-<?php echo $csv->id.'-'.$field.'-'.$error; ?>-suggestions" class="details-link details-suggestions-link">Tips and Suggestions</span>
    </li>
    <?php endforeach; ?>
    <?php endforeach; ?>
  </ul>
</div>
<div class="clear"></div>
<?php endif; ?>