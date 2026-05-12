<?php
$v = static function ($value, $fallback = '-') {
  if ($value === NULL) {
    return $fallback;
  }
  $text = trim((string) $value);
  return $text === '' ? $fallback : $text;
};

$d = static function ($date) {
  if (empty($date) || $date === '0000-00-00') {
    return '-';
  }
  $ts = strtotime($date);
  return $ts ? date('d-m-Y', $ts) : '-';
};

$jkKode = strtoupper((string) (isset($profile['jenis_kelamin_kode']) ? $profile['jenis_kelamin_kode'] : ''));
$jenisKelamin = '-';
if ($jkKode === 'L') {
  $jenisKelamin = 'Laki-laki';
} elseif ($jkKode === 'P') {
  $jenisKelamin = 'Perempuan';
}

$statusIzin = 'Tidak ada izin aktif';
$statusIzinClass = 'badge badge-success';
if (!empty($profile['izin_aktif_mulai']) && !empty($profile['izin_aktif_selesai'])) {
  $statusIzin = 'Sedang izin';
  $statusIzinClass = 'badge badge-warning';
}

$statusIzinTerakhir = '-';
if (isset($profile['acc_izin_terakhir'])) {
  if ((string) $profile['acc_izin_terakhir'] === '1') {
    $statusIzinTerakhir = 'Disetujui';
  } elseif ((string) $profile['acc_izin_terakhir'] === '2') {
    $statusIzinTerakhir = 'Ditolak';
  } elseif ((string) $profile['acc_izin_terakhir'] === '0') {
    $statusIzinTerakhir = 'Menunggu';
  }
}
?>

<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
  <div class="pr-md-3">
    <h4 class="mb-1 text-dark">Dashboard Santri</h4>
    <div class="text-muted small">Peta data lengkap: ringkasan utama, akademik, biodata, kontak, ortu/wali, dan riwayat izin.</div>
  </div>
  <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row align-self-stretch">
    <a href="<?php echo site_url('user/presensi'); ?>" class="btn btn-primary btn-sm mb-2 mb-sm-0 mr-sm-2">
      <i class="fas fa-check-circle mr-1"></i>Presensi
    </a>
    <a href="<?php echo site_url('user/perizinan'); ?>" class="btn btn-primary btn-sm mb-2 mb-sm-0 mr-sm-2">
      <i class="fas fa-file-signature mr-1"></i>Perizinan
    </a>
    <a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-outline-primary btn-sm">
      <i class="fas fa-sign-out-alt mr-1"></i>Logout
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="info-box">
      <span class="info-box-icon bg-info elevation-1"><i class="fas fa-id-card"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">NIM</span>
        <span class="info-box-number"><?php echo html_escape($v(isset($profile['nim']) ? $profile['nim'] : NULL)); ?></span>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="info-box">
      <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Nama</span>
        <span class="info-box-number" style="font-size:1rem;"><?php echo html_escape($v(isset($profile['nama']) ? $profile['nama'] : NULL)); ?></span>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="info-box">
      <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-graduation-cap"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Prodi</span>
        <span class="info-box-number" style="font-size:.9rem;"><?php echo html_escape($v(isset($profile['prodi']) ? $profile['prodi'] : NULL)); ?></span>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="info-box">
      <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-door-open"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Kamar</span>
        <span class="info-box-number"><?php echo html_escape($v(isset($profile['kamar']) ? $profile['kamar'] : NULL)); ?></span>
      </div>
    </div>
  </div>
</div>

<div class="row mt-1">
  <div class="col-lg-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-start align-items-center">
        <strong>Ringkasan Utama</strong>
        <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseRingkasanUtama" aria-expanded="false" aria-controls="collapseRingkasanUtama">
          <i class="fas fa-chevron-down"></i>
        </button>
      </div>
      <div id="collapseRingkasanUtama" class="collapse">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Status Izin Saat Ini</div>
            <span class="<?php echo $statusIzinClass; ?>"><?php echo html_escape($statusIzin); ?></span>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Periode Izin Aktif</div>
            <div class="font-weight-bold"><?php echo html_escape($d(isset($profile['izin_aktif_mulai']) ? $profile['izin_aktif_mulai'] : NULL)); ?> s/d <?php echo html_escape($d(isset($profile['izin_aktif_selesai']) ? $profile['izin_aktif_selesai'] : NULL)); ?></div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-start align-items-center">
        <strong>Riwayat Izin Singkat</strong>
        <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseRiwayatIzinSingkat" aria-expanded="false" aria-controls="collapseRiwayatIzinSingkat">
          <i class="fas fa-chevron-down"></i>
        </button>
      </div>
      <div id="collapseRiwayatIzinSingkat" class="collapse">
      <div class="card-body">
        <div class="mb-3">
          <div class="text-muted small">Total Pengajuan</div>
          <div class="h4 font-weight-bold mb-0"><?php echo (int) (isset($profile['total_izin']) ? $profile['total_izin'] : 0); ?></div>
        </div>
        <div class="mb-2">
          <div class="text-muted small">Pengajuan Terakhir</div>
          <div class="font-weight-bold"><?php echo html_escape($d(isset($profile['tanggal_izin_terakhir']) ? $profile['tanggal_izin_terakhir'] : NULL)); ?></div>
        </div>
        <div>
          <div class="text-muted small">Status Terakhir</div>
          <div class="font-weight-bold"><?php echo html_escape($statusIzinTerakhir); ?></div>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-start align-items-center">
        <strong>Profil Akademik</strong>
        <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseProfilAkademik" aria-expanded="false" aria-controls="collapseProfilAkademik">
          <i class="fas fa-chevron-down"></i>
        </button>
      </div>
      <div id="collapseProfilAkademik" class="collapse">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Angkatan</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['angkatan']) ? $profile['angkatan'] : NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Semester Masuk</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['semester_masuk']) ? $profile['semester_masuk'] : NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Status Mahasiswa</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['status_mahasiswa']) ? $profile['status_mahasiswa'] : NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Jenjang / Kode Prodi</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['jenjang_kode']) ? $profile['jenjang_kode'] : NULL)); ?> / <?php echo html_escape($v(isset($profile['prodi_kode']) ? $profile['prodi_kode'] : NULL)); ?></div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-start align-items-center">
        <strong>Profil Pribadi</strong>
        <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseProfilPribadi" aria-expanded="false" aria-controls="collapseProfilPribadi">
          <i class="fas fa-chevron-down"></i>
        </button>
      </div>
      <div id="collapseProfilPribadi" class="collapse">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Tempat, Tanggal Lahir</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['tempat_lahir']) ? $profile['tempat_lahir'] : NULL)); ?>, <?php echo html_escape($d(isset($profile['tanggal_lahir']) ? $profile['tanggal_lahir'] : NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Jenis Kelamin</div>
            <div class="font-weight-bold"><?php echo html_escape($jenisKelamin); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Agama</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['agama']) ? $profile['agama'] : NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Golongan Darah</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['golongan_darah']) ? $profile['golongan_darah'] : NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Tinggi Badan</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['tinggi_badan']) ? $profile['tinggi_badan'] : NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Berat Badan</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['berat_badan']) ? $profile['berat_badan'] : NULL)); ?></div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-start align-items-center">
        <strong>Kontak &amp; Domisili</strong>
        <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseKontakDomisili" aria-expanded="false" aria-controls="collapseKontakDomisili">
          <i class="fas fa-chevron-down"></i>
        </button>
      </div>
      <div id="collapseKontakDomisili" class="collapse">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Email Kampus</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['email_kampus']) ? $profile['email_kampus'] : NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Email Akun</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['email']) ? $profile['email'] : NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">No HP</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['no_hp']) ? $profile['no_hp'] : NULL)); ?></div>
          </div>
          <div class="col-md-12 mb-3">
            <div class="text-muted small">Alamat</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['alamat']) ? $profile['alamat'] : NULL)); ?></div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-start align-items-center">
        <strong>Orang Tua / Wali &amp; Sekolah Asal</strong>
        <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseOrtuSekolah" aria-expanded="false" aria-controls="collapseOrtuSekolah">
          <i class="fas fa-chevron-down"></i>
        </button>
      </div>
      <div id="collapseOrtuSekolah" class="collapse">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Nama Ortu/Wali</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['nama_ortu_wali']) ? $profile['nama_ortu_wali'] : NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="text-muted small">Telp Ortu/Wali</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['telp_ortu_wali']) ? $profile['telp_ortu_wali'] : NULL)); ?></div>
          </div>
          <div class="col-md-12 mb-3">
            <div class="text-muted small">Pekerjaan Ortu/Wali</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['pekerjaan_ortu_wali']) ? $profile['pekerjaan_ortu_wali'] : NULL)); ?></div>
          </div>
          <div class="col-md-8 mb-3">
            <div class="text-muted small">Sekolah Asal</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['nama_sekolah']) ? $profile['nama_sekolah'] : NULL)); ?></div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="text-muted small">Tahun Lulus</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['tahun_lulus']) ? $profile['tahun_lulus'] : NULL)); ?></div>
          </div>
          <div class="col-md-12">
            <div class="text-muted small">Jenjang Sekolah</div>
            <div class="font-weight-bold"><?php echo html_escape($v(isset($profile['pendidikan_sekolah']) ? $profile['pendidikan_sekolah'] : NULL)); ?></div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>
