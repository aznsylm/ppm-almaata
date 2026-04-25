<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?php echo html_escape($title); ?></title>
</head>
<body>
  <h1><?php echo html_escape($title); ?></h1>
  <ul>
    <?php foreach ($menu as $label => $url): ?>
      <li><a href="<?php echo $url; ?>"><?php echo html_escape($label); ?></a></li>
    <?php endforeach; ?>
  </ul>
</body>
</html>
