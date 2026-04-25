<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo html_escape($page_title ?? 'PPM Alma Ata'); ?></title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <style>
    .login-page { background: linear-gradient(135deg, #e9ecef 0%, #cfd8dc 100%); }
    .login-logo a { color: #343a40; font-weight: 700; }
    .text-gray-900 { color: #212529 !important; }
    .login-box {
      width: 100%;
      max-width: 430px;
      margin: 0 auto;
      padding: 0 12px;
    }
    @media (max-width: 576px) {
      .login-page { padding: 16px 0; }
      .login-logo { margin-bottom: 12px; }
      .login-card-body { padding: 1rem; }
    }
  </style>
</head>
<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <a href="#"><b>PPM</b> Alma Ata</a>
    </div>
    <div class="card card-outline card-primary">
      <div class="card-body login-card-body">
        <?php $this->load->view($content_view, isset($content_data) ? $content_data : array()); ?>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
