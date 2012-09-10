<style type="text/css">
  .details-duplicates {
    margin: 7px 0 3px;
  }
  .details-duplicates ul {
    margin: 0;
    padding: 0;
    list-style-type: none;
  }
  .details-duplicates ul li {
    margin: 4px 4px 4px 0;
    padding: 2px 4px;
    display: block;
    float: left;
    color: #ae3636;
    border: 1px dotted #f38284;
    background-color: #fcdfe0;
  }
</style>
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