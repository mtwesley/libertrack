<div class="details-tips">
  <?php foreach ($tips as $field => $tip): ?>
  <div class="details-tips-title"><?php if ($fields[$field]) echo $fields[$field]; ?> Tips</div>
  <div class="details-tip">
    <?php echo $tip; ?>
  </div>
  <?php endforeach; ?>

  <?php if (!$tips): ?>
  <div class="details-tips-title">Tips</div>
  <div class="details-no-tips">
    Sorry, no tips found.
  </div>
  <?php endif; ?>
</div>


