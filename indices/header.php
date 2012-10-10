<!DOCTYPE html>
<?php

$uri = urldecode($_SERVER['REQUEST_URI']);
$uri = preg_replace("/\/ *$/", "", $uri);
$uri = preg_replace("/\?.*$/", "", $uri);

$path = array_filter(explode('/', $uri));
?>
<html lang="en">
  <head>
    <title>SGS &middot; LiberFor | Documents</title>
    <link rel="shortcut icon" href="/favicon.png" />
    <link type="text/css" href="/css/layout.css" rel="stylesheet" />
    <link type="text/css" href="/css/style.css" rel="stylesheet" />
    <link type="text/css" href="/css/effects.css" rel="stylesheet" />
    <script type="text/javascript" src="/js/jquery-1.8.0.min.js"></script>
    <script type="text/javascript" src="/js/jquery-ui-1.8.23.min.js"></script>
   <!--  <script type="text/javascript" src="/js/effects.js"></script> -->
    <style type="text/css">
      body {
        font-family: 'Lucida Grande', Tahoma, Verdana, Arial, sans-serif;
        font-size: 11px;
        color: #333;
      }

      #container a, #container a:visited {
        text-decoration: none;
        color: #333;
      }
      #container a:hover {
        text-decoration: underline;
      }

      img {
        border: 0;
      }

      .path {}

      #container table {
        margin: 10px 0;
        padding: 0;
        width: 100%;
        border: none;
        border-collapse: collapse;
      }

      tr {
        text-align: left;
      }

      th {
        background-color: #cfdfad;
      }

      td {
        background-color: #eff1e1;
      }

      td, th {
        padding: 4px 6px;
        text-align: left;
      }

      td a, th a {
        margin: 0;
        padding: 0;
        display: block;
        height: 100%;
      }

      th a, th a:hover {
        text-decoration: none;
      }

      .dirlink {
        font-weight: bold;
        text-transform: capitalize;
      }

      .row_header .col_icon {
        padding: 4px 6px;
      }

      .row_normal .col_icon {
        text-align: center;
        width: 24px;
      }

      .col_name {}

      .col_date {
        display: none;
      }

      .row_header .col_size {}

      .row_parentdir {
        display: none;
      }

      .col_size {
        display: none;
      }

      .col_desc {
        display: none;
      }

    </style>
    <script type="text/javascript">
      function init() {
          var tablerows = document.getElementsByTagName("tr");

          for (var i=0; i < tablerows.length; i++) {
              var currow = tablerows[i];

              if (i == 0) {
                  currow.className += " row_header";
              } else if (i == 1) {
                  currow.className += " row_parentdir";
              } else {
                  currow.className += " row_normal";
              }

              var rowcells = currow.getElementsByTagName((i == 0 ? "th" : "td"));
              rowcells[0].className += " col_icon";
              rowcells[1].className += " col_name";
              rowcells[2].className += " col_date";
              rowcells[3].className += " col_size";
              // apache output is sort of broken-tabley for the description column
              if (rowcells[4]) rowcells[4].className += " col_desc";

              var namecell = rowcells[1];
              var anchors = namecell.getElementsByTagName("a");
              if (anchors.length == 1) {
                  var curanchor = anchors[0];

                  var anchorcontent = curanchor.innerHTML;
                  if (curanchor.parentNode.tagName == "TD") {
                      if (anchorcontent.match(/\/$/)) {
                          // add a class for directories, and strip the trailing slash.
                          curanchor.className = "dirlink";
                          anchorcontent = anchorcontent.replace(/\/$/, "");
                      } else {
                          curanchor.className = "filelink";
                      }
                  }
              }

              for (j=0; j < rowcells.length; j++) {
                  var curcell = rowcells[j];

                  // the "parent directory" row
                  if (i == 0) {
                      curcell.className += " cell_header";
                  } else if (i == 1) {
                      curcell.className += " cell_parentdir";
                  }
              }
          }

          // Content is hidden by a piece of script in the div tag, to prevent browsers (IE)
          // that show the original content before this JS executes.  So, show it now.
          var container = document.getElementById("container");
          container.style.display = 'block';
      }

      $(function() {
        init();
        $(".dirlink").each(function() {
          $(this).text($(this).text().replace(/\/$/, ''));
        });
      });
    </script>
  </head>
  <body>
   <div id="page">
      <div id="page-inner">

        <!-- Main -->
        <div id="main">
          <div id="main-inner">

              <!-- Top -->
              <div id="top">
                <div id="top-inner">
                  <style type="text/css">
                    #top {
                      background-image: url('/images/libertrack.png');
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
                      <span class="divider"> | </span>
                    <span class="sub">Documents</span>
                      </a>
                  </div>
                </div>
              </div>

              <!-- Middle -->
              <div id="middle">
                <div id="middle-inner">

                  <!-- Tabs -->
                <style type="text/css">
                  #navigation .primary {
                    margin: 0;
                    padding: 0;
                    height: 28px;
                    width: 100%;
                    background-color: #e4e4e4;
                    list-style-type: none;
                  }

                  #navigation .primary li {
                    margin: 0;
                    padding: 0 0 0 25px;
                    width: auto;
                    float: left;
                    display: block;
                    text-transform: uppercase;
                  }

                  #navigation .primary li a {
                    padding: 7px 0 6px;
                    color: #555;
                    text-decoration: none;
                    line-height: 26px;
                  }

                  #navigation .primary li a:hover {
                    color: #333;
                    border-bottom: 2px solid #aaa;
                    cursor: pointer;
                  }

                  #navigation .primary li.active a {
                    color: #333;
                    cursor: default;
                    font-weight: bold;
                    border-bottom: 2px solid #000;
                  }

                  #navigation .primary li.active a:hover {
                    border-bottom: 2px solid #f60;
                  }


                  #navigation .secondary,
                  .path {
                    margin: 0;
                    padding: 4px 0;
                    list-style-type: none;
                    text-align: left;
                    background-color: #fafafa;
                  }

                  #navigation .secondary li,
                  .path li {
                    margin: 0;
                    padding: 6px 10px 0;
                  }

                  #navigation .secondary li a,
                  .path li a {
                    background: transparent url('/images/list_off.gif') left center no-repeat;
                    padding-left: 15px;
                    text-align: left;
                    text-decoration: none;
                    color: #555;
                  }

                  #navigation .secondary li a:hover,
                  .path li a:hover {
                    background: transparent url('/images/list_on.gif') left center no-repeat;
                    color: #333;
                  }

                  #navigation .secondary li.active a,
                  .path li.active a {
                    background: transparent url('/images/list_active.gif') left center no-repeat;
                    color: #000;
                  }

                  #navigation .secondary li.active a:hover,
                  .path li.active a:hover {
                    background: transparent url('/images/list_orange.gif') left center no-repeat;
                    cursor: default;
                  }

                  #navigation .secondary li .commands {
                    margin: 0;
                    padding: 0 16px;
                    list-style-type: none;
                  }
                  #navigation .secondary li .commands li {}
                  #navigation .secondary li .commands li a {
                    background: transparent url('/images/list_off.gif') left center no-repeat;
                    padding-left: 15px;
                    text-align: left;
                    text-decoration: none;
                    color: #555;
                    cursor: pointer;
                  }
                  #navigation .secondary li .commands li a:hover {
                    background: transparent url('/images/list_on.gif') left center no-repeat;
                    color: #333;
                    cursor: pointer;
                  }
                  #navigation .secondary li .commands li.active a {
                    background: transparent url('/images/list_active.gif') left center no-repeat;
                    color: #000;
                  }
                  #navigation .secondary li .commands li.active a:hover {
                    background: transparent url('/images/list_orange.gif') left center no-repeat;
                    cursor: default;
                  }

                </style>
                <div id="navigation">
                  <ul class="nav primary">
                    <li class=""><a href="/">Home</a></li>
                    <li class=""><a href="/import">Import</a></li>
                    <li class=""><a href="/export">Export</a></li>
                    <!-- <li class=""><a href="/">Analysis, Checks, and Queries</a></li> -->
                    <!-- <li class=""><a href="/">Reports</a></li> -->
                    <li class="active"><a href="/">Documents</a></li>
                    <li class=""><a href="/admin">Administration</a></li>
                    <li style="float: right; margin-right: 25px;"><a href="/admin">Logout</a></li>
                  </ul>
                </div>
                <!-- Center container -->
                <div id="center">
                  <div id="center-inner">
                    <!-- Content -->
                    <div id="content">
                      <div id="content-inner">
                        <?php if ($path): ?>
                        <ul class="path">
                          <?php foreach ($path as $item): ?>
                          <?php $link .= '/'.$item;?>
                          <li class="<?php if ($item == $path[count($path)]) echo 'active'; ?>">
                            <a href="<?php echo $link; ?>"><?php echo in_array($item, array('documents', 'import', 'export')) ? ucwords($item) : $item; ?></a>
                          </li>
                          <?php endforeach; ?>
                        </ul>
                        <div class="clear"></div>
                        <?php endif; ?>
                        <div id="container">
