<ul>
  <?php foreach ($errors as $field => $error): ?>
  <li>
    <?php if ($fields[$field]): ?>
    <strong><?php echo $fields[$field]; ?>: </strong>
    <?php endif; ?>
    <?php echo $error; ?>
  </li>
  <?php endforeach; ?>
</ul>