<?php if ($duplicates): ?>
<div class="details-duplicates">
  <strong>Duplicates:</strong>
  <ul>
    <?php foreach ($duplicates as $field => $duplicate): ?>
    <li>
      <?php
        if (is_numeric($field)) echo 'Perfect duplicate found';
        else echo 'Duplicate found for "'.$field.'"';
      ?>
    </li>
    <?php endforeach; ?>
  </ul>
</div>
<div class="clear"></div>
<?php endif; ?>