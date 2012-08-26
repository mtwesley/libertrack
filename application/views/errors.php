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
    margin: 6px 6px 6px 0;
    padding: 3px 6px;
    display: block;
    float: left;
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
    <li><?php echo SGS::errorfy($error); ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<div class="clear"></div>
<?php endif; ?>