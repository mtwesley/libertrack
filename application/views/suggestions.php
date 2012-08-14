<?php foreach ($suggestions as $field => $suggestion): ?>
<?php if ($suggestion): ?>
<?php if ($fields[$field]): ?>
<strong><?php echo $fields[$field]; ?>: </strong>
<?php endif; ?>

<ul>
  <?php foreach ($suggestion as $suggest): ?>
  <li><?php echo $suggest; ?></li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
<?php endforeach; ?>