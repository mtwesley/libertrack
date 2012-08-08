<?php if ($title): ?>
<p><strong><?php print $title; ?>:</strong></p>
<?php endif; ?>

<table border="1">
  <tr>
    <?php foreach ($fields as $name): ?>
    <td><strong><?php print $name; ?></strong></td>
    <?php endforeach; ?>
    <td><strong>Errors</strong></td>
    <td><strong>Suggestions</strong></td>
    <td></td>
  </tr>
  <?php foreach ($csvs as $csv): ?>
  <tr>
    <?php foreach ($fields as $key => $name): ?>
    <td><?php print $csv->values[$key]; ?></td>
    <?php endforeach; ?>

    <td>
      <?php
          if ($csv->errors) print View::factory('errors')
            ->set('fields', $fields)
            ->set('errors', $csv->errors)
            ->render();
      ?>
    </td>
    <td>
      <?php
          if ($csv->suggestions) print View::factory('suggestions')
            ->set('fields', $fields)
            ->set('suggestions', $csv->suggestions)
            ->render();
      ?>
    </td>
    <td><?php print HTML::anchor('import/data/'.$csv->id.'/edit', 'edit'); ?></td>
  </tr>
  <?php endforeach; ?>
</table>