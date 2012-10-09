<div id="messages">
  <?php foreach ($msgs as $type => $value): ?>
    <?php foreach ($value as $message): ?>
    <div class="message <?php echo $type; ?>">
      <span class="text"><?php echo $message; ?></span>
      <span class="close">X</span>
    </div>
    <?php endforeach ?>
  <?php endforeach ?>
</div>
