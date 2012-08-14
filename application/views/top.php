<style type="text/css">
  #top {
    background-image: url('/images/liberfor.jpg');
    background-repeat: no-repeat;
    background-position: 1110px 20px;
  }

  #title {
    padding: 10px;
  }

  #title .sgs {
    color: #f60;
  }

  #title .main {
    font-size: 20px;
    font-weight: bold;
    line-height: 40px;
  }

  #title .dot {
    margin: 0 -5px;
  }

  #title .divider {
    margin: 0 -10px 0 -5px;
    color: #000;
    font-size: 40px;
    line-height: 40px;
    position: relative;
    top: 5px;
  }

  #title .sub {
    color: #8a8a8a;
    font-size: 20px;
    line-height: 40px;
  }

  .top-home-link,
  .top-home-link:visited {
    text-decoration: none;
    color: #333;
  }
</style>
<div id="title">
  <a class="top-home-link" href="/">
  <span class="main">
    <span class="sgs">SGS</span>
    <span class="dot"> · </span>
    LiberFor
  </span>
  <?php if ($title): ?>
  <span class="divider"> | </span>
  <span class="sub"><?php echo $title; ?></span>
  <?php endif; ?>
  </a>
</div>