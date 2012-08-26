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
/128.151.244.179/xc-6.x-1.2/
**/

if (!$path)  $path  = Request::$current->url();
if (!$title) $title = SGS::title($path);

if (!$top) $top = View::factory('top')->set('title', $title);

if (!$navigation) $navigation = View::factory('navigation');
if (!$messages)   $messages   = Notify::render();
if (!$help)       $help       = Help::render();

if ($left or $help) $classes['body'][] = 'sidebar-left';
if ($right)         $classes['body'][] = 'sidebar-right';

$styles[] = 'layout';
$styles[] = 'style';
$styles[] = 'effects';

$scripts[] = 'jquery-1.8.0.min';
$scripts[] = 'jquery-ui-1.8.23.min';
$scripts[] = 'effects';

foreach (array_filter(explode('/', $path)) as $item) {
  $class_arr[] = $item;
  $classes['body'][] = implode('-', $class_arr);
}

?><!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo $head; ?>
    <title>SGS &middot; LiberFor <?php if ($title): ?> | <?php echo $title; ?><?php endif; ?></title>
    <link rel="shortcut icon" href="/favicon.png" />
    <style type="text/css">
      body {
        font-family: 'Lucida Grande', Tahoma, Verdana, Arial, sans-serif;
        font-size: 11px;
        color: #333;
      }
    </style>
    <?php echo SGS::render_styles($styles); ?>
    <?php echo SGS::render_scripts($scripts); ?>
  </head>

  <body class="<?php echo SGS::render_classes($classes['body']); ?>">
    <!-- Opener -->
    <?php echo $opener; ?>

    <!-- Page -->
    <div id="page"<?php if ($classes['page']): ?> class="<?php echo SGS::render_classes($classes['page']); ?>"<?php endif; ?>>
      <div id="page-inner">

        <!-- Main -->
        <div id="main"<?php if ($classes['main']): ?> class="<?php SGS::render_classes($classes['main']); ?>"<?php endif; ?>>
          <div id="main-inner">

              <!-- Top -->
              <div id="top"<?php if ($classes['top']): ?> class="<?php SGS::render_classes($classes['top']); ?>"<?php endif; ?>>
                <div id="top-inner">
                  <?php echo $top; ?>

                </div>

                <!-- Breadcrumbs -->
                <?php echo $breadcrumbs; ?>
              </div>

              <!-- Middle -->
              <div id="middle"<?php if ($classes['middle']): ?>  class="<?php SGS::render_classes($classes['middle']); ?>"<?php endif; ?>>
                <div id="middle-inner">

                  <!-- Tabs -->
                  <?php if ($navigation): ?>
                  <?php echo $navigation; ?>
                  <?php endif; ?>

                  <?php if ($help or $left): ?>
                  <!-- Left -->
                  <div id="left"<?php if ($classes['left']): ?> class="sidebar <?php SGS::render_classes($classes['left']); ?>"<?php endif; ?>>
                    <div id="left-inner">
                      <?php echo $help; ?>
                      <?php echo $left; ?>
                    </div>
                  </div>
                  <?php endif; ?>

                  <!-- Center container -->
                  <div id="center"<?php if ($classes['center']): ?> class="<?php SGS::render_classes($classes['center']); ?>"<?php endif; ?>>
                    <div id="center-inner">

                      <!-- Header -->
                      <div id="header" class="<?php SGS::render_classes($classes['header']); ?>">
                        <div id="header-inner">
                          <?php echo $messages; ?>
                          <?php echo $header; ?>
                        </div>
                      </div>

                      <!-- Content -->
                      <?php if ($content): ?>
                      <div id="content"<?php if ($classes['content']): ?> class="<?php SGS::render_classes($classes['content']); ?>"<?php endif; ?>>
                        <div id="content-inner">
                          <?php echo $content; ?>
                        </div>
                      </div>
                      <?php endif; ?>

                      <!-- Footer -->
                      <div id="footer"<?php if ($classes['footer']): ?> class="<?php SGS::render_classes($classes['footer']); ?>"<?php endif; ?>>
                        <div id="footer-inner">
                          <?php echo $copyright; ?>
                          <?php echo $footer; ?>
                        </div>
                      </div>

                    </div>
                  </div>

                  <?php if ($right): ?>
                  <!-- Right -->
                  <div id="right"<?php if ($classes['right']): ?>  class="sidebar <?php SGS::render_classes($classes['right']); ?>"<?php endif; ?>>
                    <div id="right-inner">
                      <?php echo $right; ?>
                    </div>
                  </div>
                  <?php endif; ?>

                  <!-- Clearing -->
                  <div class="clear"></div>

                </div>
              </div>

              <!-- Bottom -->
              <div id="bottom"<?php if ($classes['bottom']): ?>  class="<?php SGS::render_classes($classes['bottom']); ?>"<?php endif; ?>>
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
