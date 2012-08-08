<?php foreach ($suggestions as $field => $suggestion): ?>
<?php if ($suggestion): ?>
<?php if ($fields[$field]): ?>
<strong><?php print $fields[$field]; ?>: </strong>
<?php endif; ?>

<ul>
  <?php foreach ($suggestion as $suggest): ?>
  <li><?php print $suggest; ?></li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
<?php endforeach; ?>