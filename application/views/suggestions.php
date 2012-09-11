<?php if ($suggestions): ?>
<div class="details-suggestions">
  <?php foreach ($suggestions as $field => $suggestion): ?>
  <?php if ($suggestion): ?>
  <div class="details-suggestion">
    <strong>
      <?php if ($fields[$field]) echo $fields[$field]; ?>
      Suggestions:
    </strong>
    <ul class="suggest">
      <?php foreach ($suggestion as $suggest): ?>
      <li class="<?php echo $field; ?>"><?php echo $suggest; ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="clear details-suggestion"></div>
  <?php endif; ?>
  <?php endforeach; ?>
</div>
<?php endif; ?>