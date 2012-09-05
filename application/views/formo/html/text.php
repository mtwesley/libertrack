<?php echo $open; ?>
  <span class="text <?php echo $name; ?>">
    <label<?php if ($id = $this->attr('id')) echo ' for="'.$id.'"'; ?>><?php echo $value; ?></label>
  </span>
<?php echo $close; ?>