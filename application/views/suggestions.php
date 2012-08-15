<style type="text/css">
  .details-suggestions {}
  .details-suggestion {
    margin-right: 10px;
    min-width: 150px;
    max-width: 300px;
    float: left;
  }
  .details-suggestion strong {
    padding-right: 20px;
  }
  .details-suggestion ul {
    margin: 0;
    padding: 0;
    list-style-type: none;
  }
  .details-suggestion li {
    margin: 6px 0;
    padding: 6px 15px;
    color: #4a96ff;
    border: 1px dotted #b0d1ff;
    background-color: #e3efff;
  }
  .details-suggestion li.more {}
  .details-suggestion li.more a {}
</style>
<?php if ($suggestions): ?>
<div class="details-suggestions">
  <?php foreach ($suggestions as $field => $suggestion): ?>
  <?php if ($suggestion): ?>
  <div class="details-suggestion">
    <strong>
      <?php if ($fields[$field]): ?>
      <?php echo $fields[$field]; ?>
      <?php endif; ?>
      Suggestions:
    </strong>
    <ul>
      <?php foreach ($suggestion as $suggest): ?>
      <li><?php echo $suggest; ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>
  <?php endforeach; ?>
</div>
<?php endif; ?>