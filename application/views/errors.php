<style type="text/css">
  .details-errors {
    margin-right: 10px;
    min-width: 250px;
    max-width: 400px;
    float: left;
  }
  .details-errors ul {
    margin: 0;
    padding: 0;
    list-style-type: none;
  }
  .details-errors ul li {
    margin: 6px 0;
    padding: 6px 15px;
    color: #ae3636;
    border: 1px dotted #f38284;
    background-color: #fcdfe0;
  }
</style>
<?php if ($errors): ?>
<div class="details-errors">
  <strong>Processing Errors:</strong>
  <ul>
    <?php foreach ($errors as $field => $error): ?>
    <li><?php echo ucfirst($error); ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>