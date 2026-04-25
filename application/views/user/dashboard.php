<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?php echo html_escape($title); ?></title>
</head>
<body>
  <h1><?php echo html_escape($title); ?></h1>
  <p><a href="<?php echo site_url('user/perizinan'); ?>">Perizinan</a> | <a href="<?php echo site_url('auth/logout'); ?>">Logout</a></p>

  <h3>Profil</h3>
  <ul>
    <li>NIM: <?php echo html_escape($profile['nim']); ?></li>
    <li>Nama: <?php echo html_escape($profile['nama']); ?></li>
    <li>Email Kampus: <?php echo html_escape($profile['email_kampus']); ?></li>
    <li>Kamar: <?php echo html_escape($profile['kamar']); ?></li>
  </ul>
</body>
</html>
