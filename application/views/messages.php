<div id="messages">
  <?php foreach ($msgs as $type => $value): ?>
    <?php foreach ($value as $message): ?>
    <div class="message <?php echo $type; ?>"><?php echo $message; ?></div>
    <?php endforeach ?>
  <?php endforeach ?>
</div>
