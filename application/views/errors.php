<style type="text/css">
  .details-errors {
    margin: 7px 0 3px;
  }
  .details-errors ul {
    margin: 0;
    padding: 0;
    list-style-type: none;
  }
  .details-errors ul li {
    margin: 4px 4px 4px 0;
    padding: 2px 4px;
    display: block;
    float: left;
    color: #ae3636;
    border: 1px dotted #f38284;
    background-color: #fcdfe0;
  }
</style>
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