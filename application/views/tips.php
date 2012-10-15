<div class="details-tips">
  <div class="details-tips-title">Tips</div>
  
  <?php foreach ($tips as $field => $tip): ?>
  <div class="details-tip">
    <?php if (count($tips) > 1): ?>
    <strong><?php if ($fields[$field]) echo $fields[$field]; ?>:</strong>
    <?php endif; ?>

    <?php echo $tip; ?>
  </div>
  <?php endforeach; ?>

  <?php if (!$tips): ?>
  <div class="details-no-tips">
    Sorry, no tips found.
  </div>
  <?php endif; ?>
</div>


