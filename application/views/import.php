<fieldset>
  <legend>CSV</legend>
  <form method="post" action="/import" enctype="multipart/form-data">
    <input type="hidden" name="form" value="csv" />

    <strong>File: </strong>
    <input type="file" name="csv" value="" />
    &nbsp;&nbsp;

    <strong>Type: </strong>
    <input type="radio" name="form_type" value="SSF" checked="checked" /> SSF
    <input type="radio" name="form_type" value="TDF" /> TDF
    <input type="radio" name="form_type" value="LDF" /> LDF
    &nbsp;&nbsp;

    <input type="submit" name="submit" value="Import" />
  </form>
</fieldset>
