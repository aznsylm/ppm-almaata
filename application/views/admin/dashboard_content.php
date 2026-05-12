<!-- Content Header -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Dashboard Admin</h1>
        <p class="text-muted">Kontrol data santri dan perizinan pondok</p>
      </div>
    </div>
  </div>
</div>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>Dashboard</h3>
            <p>Menu Utama</p>
          </div>
          <div class="icon">
            <i class="fas fa-tachometer-alt"></i>
          </div>
          <a href="<?php echo site_url('admin/dashboard'); ?>" class="small-box-footer">
            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>Data Santri</h3>
            <p>Kelola Santri</p>
          </div>
          <div class="icon">
            <i class="fas fa-users"></i>
          </div>
          <a href="<?php echo site_url('admin/santri'); ?>" class="small-box-footer">
            Kelola Data <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>Perizinan</h3>
            <p>Kelola Izin</p>
          </div>
          <div class="icon">
            <i class="fas fa-clipboard-list"></i>
          </div>
          <a href="<?php echo site_url('admin/perizinan'); ?>" class="small-box-footer">
            Kelola Izin <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Info boxes -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-mosque mr-1"></i>
              Profil Pondok
            </h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <strong><i class="fas fa-university mr-1"></i> Nama Pondok</strong>
                <p class="text-muted">Pondok Pesantren Mahasiswa Universitas Alma Ata</p>
              </div>
              <div class="col-md-6">
                <strong><i class="fas fa-user-tie mr-1"></i> Pimpinan Pondok</strong>
                <p class="text-muted">KH. Hamam Hadi</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
