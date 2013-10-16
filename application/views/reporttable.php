<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <?php foreach ($headers as $name): ?>
    <th><?php echo $name; ?></th>
    <?php endforeach; ?>
  </tr>
  <?php foreach ($results as $result): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <?php foreach ($result as $data): ?>
    <td><?php echo $data; ?></td>
    <?php endforeach; ?>
  </tr>
  <?php endforeach; ?>
</table>
