<ul>
  <?php foreach ($errors as $field => $error): ?>
  <li>
    <?php if ($fields[$field]): ?>
    <strong><?php print $fields[$field]; ?>: </strong>
    <?php endif; ?>
    <?php print $error; ?>
  </li>
  <?php endforeach; ?>
</ul>