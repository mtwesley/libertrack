<!-- <style type="text/css">
  body, form, table {
    font-size: 12px;
    font-family: "Helvetica", "Arial", sans-serif;
    color: #222;
  }

  div.nav {
    margin: 1em 1em 0 0;
    padding: 1em;
    border: 1px solid #ccc;
    float: left;
  }

  div.nav ul {
    margin: 0;
    padding: 0;
  }

  div.nav li,
  div.nav strong {
    margin: 0;
    padding: 0.5em 2em 0.5em 1em;
    display: block;
  }

  div.nav strong {
    background-color: #eee;
  }

  div.nav li {
    border-top: 1px dotted #ccc;
    background-color: #fafafa;
  }

  div.nav li:hover {
    background-color: #f5f5f5;
  }

  div.nav li a {
    color: #444;
    text-decoration: none;
  }

  div.nav li a:hover {
    text-decoration: underline;
  }

  div.clear {
    clear: both;
    margin-bottom: 1em;
  }
</style> -->

<div class="nav">
  <strong>Administration:</strong>
  <ul>
    <li><a href="/admin/operators">Operators</a></li>
    <li><a href="/admin/sites">Sites</a></li>
    <li><a href="/admin/blocks">Blocks</a></li>
    <li><a href="/admin/printjobs">Printjobs</a></li>
  </ul>
</div>
<div class="nav">
  <strong>Management:</strong>
  <ul>
    <li><a href="/files">Files</a></li>
  </ul>
</div>
<div class="nav">
  <strong>Import:</strong>
  <ul>
    <li><a href="/import">Home</a></li>
    <li><a href="/import/upload">Upload</a></li>
    <li><a href="/import/files">Files</a></li>
    <li><a href="/import/data">Data</a></li>
  </ul>
</div>
<div class="nav">
  <strong>Export:</strong>
  <ul>
    <li><a href="/export/ssf">Stock Survey Form</a></li>
    <li><a href="/export/tdf">Tree Data Form</a></li>
    <li><a href="/export/ldf">Log Data Form</a></li>
  </ul>
</div>
<div class="clear"></div>
<?php print Notify::render(); ?>