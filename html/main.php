<?php

/**

clear-block:
 * page
 * main
 * top
 * middle
 * bottom
 * header
 * footer
 * left
 * right
 * center

clear:
 * left
 * bottom

**/

?><!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo $head; ?>
    <title><?php echo $title; ?></title>
    <?php echo $styles; ?>
    <?php echo $scripts; ?>
  </head>

  <body class="<?php echo $classes['body']; ?>">
    <!-- Opener -->
    <?php echo $opener; ?>

    <!-- Page -->
    <div id="page" class="<?php echo $classes['page']; ?>">
      <div id="page-inner">

        <!-- Main -->
        <div id="main" class="<?php echo $classes['main']; ?>">
          <div id="main-inner">

              <!-- Top -->
              <div id="top" class="<?php echo $classes['top']; ?>">
                <div id="top-inner">
                  <?php echo $top; ?>

                </div>

                <!-- Breadcrumbs -->
                <?php echo $breadcrumbs; ?>
              </div>

              <!-- Middle -->
              <div id="middle" class="<?php echo $classes['middle']; ?>">
                <div id="middle-inner">

                  <!-- Tabs -->
                  <?php if ($tabs): ?>
                    <div class="tabs">
                      <?php echo $tabs; ?>
                    </div>
                  <?php endif; ?>

                  <!-- Left -->
                  <div id="left" class="sidebar <?php echo $classes['left']; ?>">
                    <div id="left-inner">
                      <?php echo $left; ?>
                    </div>
                  </div>

                  <!-- Center container -->
                  <div id="center" class="<?php echo $classes['center']; ?>">
                    <div id="center-inner">

                      <!-- Header -->
                      <div id="header" class="<?php echo $classes['header']; ?>">
                        <div id="header-inner">
                          <?php echo $messages; ?>
                          <?php echo $help; ?>
                          <?php echo $header; ?>
                        </div>
                      </div>

                      <!-- Content -->
                      <?php if ($content): ?>
                      <div id="content" <?php echo $classes['content']; ?>>
                        <div id="content-inner">
                          <?php echo $content; ?>
                        </div>
                      </div>
                      <?php endif; ?>

                      <!-- Footer -->
                      <div id="footer" <?php echo $classes['footer']; ?>>
                        <div id="footer-inner">
                          <?php echo $copyright; ?>
                          <?php echo $notices; ?>
                          <?php echo $footer; ?>
                        </div>
                      </div>

                    </div>
                  </div>

                  <!-- Right -->
                  <div id="right" class="sidebar <?php echo $classes['right']; ?>">
                    <div id="right-inner">
                      <?php echo $right; ?>
                    </div>
                  </div>

                  <!-- Clearing -->
                  <div class="clear"></div>

                </div>
              </div>

              <!-- Bottom -->
              <div id="bottom" class="<?php echo $classes['bottom']; ?>">
                <div id="bottom-inner">
                  <?php echo $bottom; ?>
                </div>
              </div>

          </div>
        </div>

      </div>
    </div>

    <!-- Closure -->
    <?php echo $closure; ?>
  </body>
</html>
