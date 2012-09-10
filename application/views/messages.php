<style type="text/css">
  #messages {}

  #messages .message {
    margin: 5px 0;
    padding: 4px 15px 4px 25px;
    border-top: 2px solid;
  }

  #messages .error {
    color: #ae3636;
    border-top-color: #f38284;
    background-color: #fcdfe0;
    background-image: url('/images/error.png');
    background-position: 4px 2px;
    background-repeat: no-repeat;
  }

  #messages .warning,
  #messages .locked {
    color: #a5890b;
    border-top-color: #efe9b7;
    background-color: #f8f5e1;
    background-image: url('/images/warning.png');
    background-position: 4px 2px;
    background-repeat: no-repeat;
  }

  #messages .locked {
    background-image: url('/images/locked.png');
  }

  #messages .success {
    color: #63922d;
    border-top-color: #99c494;
    background-color: #dde5c5;
    background-image: url('/images/success.png');
    background-position: 4px 2px;
    background-repeat: no-repeat;
  }

  #messages .notice {
    color: #4a96ff;
    border-top-color: #b0d1ff;
    background-color: #e3efff;
    background-image: url('/images/info.png');
    background-position: 4px 2px;
    background-repeat: no-repeat;
  }
</style>
<div id="messages">
  <?php foreach ($msgs as $type => $value): ?>
    <?php foreach ($value as $message): ?>
    <div class="message <?php echo $type; ?>"><?php echo $message; ?></div>
    <?php endforeach ?>
  <?php endforeach ?>
</div>
