<div id="csv-<?php echo $csv->id; ?>-details-suggestions" class="details-suggestions">
  <?php foreach ($suggestions as $field => $suggestion): ?>
  <div class="details-suggestions-title"><?php if ($fields[$field]) echo $fields[$field]; ?> Suggestions (<?php echo $csv->values[$field]; ?>)</div>
  <div class="details-suggestion">
    <?php if (count($suggestions) > 1): ?>
    <strong><?php if ($fields[$field]) echo $fields[$field]; ?>:</strong>
    <?php endif; ?>

    <ul id="csv-<?php echo $csv->id.'-'.$field; ?>-suggest" class="suggest">
      <?php foreach ($suggestion as $suggest): ?>
      <li class="<?php echo $field; ?>"><?php echo $suggest; ?></li>
      <?php endforeach; ?>
    </ul>
    <div class="clear"></div>
  </div>
  <?php endforeach; ?>

  <?php if (!$suggestions): ?>
  <div class="details-suggestions-title">Suggestions</div>
  <div class="details-no-suggestions">
    Sorry, no suggestions found.
  </div>
  <?php endif; ?>
</div>

