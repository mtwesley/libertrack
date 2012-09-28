<?php if ($errors): ?>
<div class="details-errors">
  <strong>Errors:</strong>
  <ul>
    <?php foreach ($errors as $field => $error): ?>
    <?php foreach ((array) $error as $err): ?>
    <li><?php echo SGS::errorfy($err); ?></li>
    <?php endforeach; ?>
    <?php endforeach; ?>
  </ul>
</div>
<div class="clear"></div>
<?php endif; ?>