<?php if ($errors): ?>
<div class="details-errors">
  <strong>Errors:</strong>
  <ul>
    <?php foreach ($errors as $field => $error): ?>
    <li><?php echo SGS::errorfy($error); ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<div class="clear"></div>
<?php endif; ?>