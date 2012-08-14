<div id="help">
  <?php foreach ($msgs as $title => $value): ?>
    <?php foreach ($value as $text): ?>
  <div class="help-section">
      <div class="help-title"><?php echo $title; ?></div>
      <div class="help-text"><?php echo $text;?></div>
  </div>
    <?php endforeach ?>
  <?php endforeach ?>
</div>
