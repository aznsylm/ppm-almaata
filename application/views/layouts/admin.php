<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo html_escape(!empty($page_title) ? $page_title : 'PPM Alma Ata'); ?></title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <style>
    .text-gray-800 { color: #343a40 !important; }
    .small-text { color: #6c757d; font-size: .875rem; }
    .badge-soft { border-radius: 999px; padding: .35rem .7rem; }
    .content-card { border-radius: .5rem; }
    .main-sidebar .brand-link { border-bottom: 1px solid rgba(255,255,255,.08); }
    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
      background-color: rgba(255,255,255,.15);
      color: #fff;
    }
    .card-border-left-primary { border-left: .25rem solid #007bff !important; }
    .card-border-left-success { border-left: .25rem solid #28a745 !important; }
    .card-border-left-warning { border-left: .25rem solid #ffc107 !important; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<?php
$authUser = $this->session->userdata('auth_user');
$uri = uri_string();

if (!empty($authUser) && $authUser['role'] === 'admin') {
  $menuItems = array(
    array('label' => 'Dashboard', 'url' => site_url('admin/dashboard'), 'icon' => 'fa-tachometer-alt', 'match' => 'admin/dashboard'),
    array('label' => 'Data Santri', 'url' => site_url('admin/santri'), 'icon' => 'fa-users', 'match' => 'admin/santri'),
    array('label' => 'Data Perizinan', 'url' => site_url('admin/perizinan'), 'icon' => 'fa-clipboard-list', 'match' => 'admin/perizinan'),
    array('label' => 'Raising', 'url' => 'https://raising.almaata.ac.id/', 'icon' => 'fa-external-link-alt', 'target' => '_blank', 'rel' => 'noopener noreferrer'),
    array('label' => 'PPM Alma Ata', 'url' => 'https://ponpesmahasiswa.almaata.ac.id/', 'icon' => 'fa-external-link-alt', 'target' => '_blank', 'rel' => 'noopener noreferrer'),
  );
} else {
  $menuItems = array(
    array('label' => 'Dashboard', 'url' => site_url('user/dashboard'), 'icon' => 'fa-home', 'match' => 'user/dashboard'),
    array('label' => 'Perizinan', 'url' => site_url('user/perizinan'), 'icon' => 'fa-file-signature', 'match' => 'user/perizinan'),
    array('label' => 'Raising', 'url' => 'https://raising.almaata.ac.id/', 'icon' => 'fa-external-link-alt', 'target' => '_blank', 'rel' => 'noopener noreferrer'),
    array('label' => 'PPM Alma Ata', 'url' => 'https://ponpesmahasiswa.almaata.ac.id/', 'icon' => 'fa-external-link-alt', 'target' => '_blank', 'rel' => 'noopener noreferrer'),
  );
}
?>
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link font-weight-bold"><?php echo html_escape(!empty($page_title) ? $page_title : 'PPM Alma Ata'); ?></span>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link text-muted"><?php echo html_escape(!empty($authUser['display_name']) ? $authUser['display_name'] : 'Guest'); ?></span>
      </li>
      <li class="nav-item">
        <a class="nav-link text-danger" href="<?php echo site_url('auth/logout'); ?>"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?php echo !empty($authUser) && $authUser['role'] === 'admin' ? site_url('admin/dashboard') : site_url('user/dashboard'); ?>" class="brand-link">
      <span class="brand-text font-weight-light"><strong>PPM</strong> Alma Ata</span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <?php foreach ($menuItems as $item): ?>
            <?php $isActive = !empty($item['match']) && (strpos($uri, $item['match']) === 0); ?>
            <li class="nav-item">
              <a href="<?php echo $item['url']; ?>" class="nav-link<?php echo $isActive ? ' active' : ''; ?>"<?php echo !empty($item['target']) ? ' target="' . html_escape($item['target']) . '"' : ''; ?><?php echo !empty($item['rel']) ? ' rel="' . html_escape($item['rel']) . '"' : ''; ?>>
                <i class="nav-icon fas <?php echo html_escape($item['icon']); ?>"></i>
                <p><?php echo html_escape($item['label']); ?></p>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <h1 class="m-0 text-dark"><?php echo html_escape(!empty($page_title) ? $page_title : 'PPM Alma Ata'); ?></h1>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <?php if (!empty($this->session->flashdata('success'))): ?>
          <div class="alert alert-success"><?php echo html_escape($this->session->flashdata('success')); ?></div>
        <?php endif; ?>
        <?php if (!empty($this->session->flashdata('error'))): ?>
          <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
        <?php endif; ?>

        <div class="card content-card">
          <div class="card-body">
            <?php $this->load->view($content_view, isset($content_data) ? $content_data : array()); ?>
          </div>
        </div>
      </div>
    </section>
  </div>

  <footer class="main-footer">
    <strong>Pondok Pesantren Mahasiswa Universitas Alma Ata</strong>
  </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
