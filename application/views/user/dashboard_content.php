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

$jkKode = strtoupper((string) ($profile['jenis_kelamin_kode'] ?? ''));
$jenisKelamin = '-';
if ($jkKode === 'L') {
  $jenisKelamin = 'Laki-laki';
} elseif ($jkKode === 'P') {
  $jenisKelamin = 'Perempuan';
}

$statusIzin = 'Tidak ada izin aktif';
$statusIzinClass = 'badge badge-success badge-soft';
if (!empty($profile['izin_aktif_mulai']) && !empty($profile['izin_aktif_selesai'])) {
  $statusIzin = 'Sedang izin';
  $statusIzinClass = 'badge badge-warning badge-soft';
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
    <h4 class="mb-1 text-gray-800">Dashboard Santri</h4>
    <div class="small-text">Peta data lengkap: ringkasan utama, akademik, biodata, kontak, ortu/wali, dan riwayat izin.</div>
  </div>
  <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row align-self-stretch">
    <a href="<?php echo site_url('user/perizinan'); ?>" class="btn btn-primary btn-sm mb-2 mb-sm-0 mr-sm-2">
      <i class="fas fa-file-signature mr-1"></i>Perizinan
    </a>
    <a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-outline-primary btn-sm">
      <i class="fas fa-sign-out-alt mr-1"></i>Logout
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-xs font-weight-bold text-uppercase mb-2">NIM</div>
        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo html_escape($v($profile['nim'] ?? NULL)); ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-xs font-weight-bold text-uppercase mb-2">Nama</div>
        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo html_escape($v($profile['nama'] ?? NULL)); ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-xs font-weight-bold text-uppercase mb-2">Prodi</div>
        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo html_escape($v($profile['prodi'] ?? NULL)); ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-xs font-weight-bold text-uppercase mb-2">Kamar</div>
        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo html_escape($v($profile['kamar'] ?? NULL)); ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row mt-1">
  <div class="col-lg-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Ringkasan Utama</strong></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="small-text">Status Izin Saat Ini</div>
            <span class="<?php echo $statusIzinClass; ?>"><?php echo html_escape($statusIzin); ?></span>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">Periode Izin Aktif</div>
            <div class="font-weight-bold"><?php echo html_escape($d($profile['izin_aktif_mulai'] ?? NULL)); ?> s/d <?php echo html_escape($d($profile['izin_aktif_selesai'] ?? NULL)); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Riwayat Izin Singkat</strong></div>
      <div class="card-body">
        <div class="mb-3">
          <div class="small-text">Total Pengajuan</div>
          <div class="h4 font-weight-bold mb-0"><?php echo (int) ($profile['total_izin'] ?? 0); ?></div>
        </div>
        <div class="mb-2">
          <div class="small-text">Pengajuan Terakhir</div>
          <div class="font-weight-bold"><?php echo html_escape($d($profile['tanggal_izin_terakhir'] ?? NULL)); ?></div>
        </div>
        <div>
          <div class="small-text">Status Terakhir</div>
          <div class="font-weight-bold"><?php echo html_escape($statusIzinTerakhir); ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Profil Akademik</strong></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="small-text">Angkatan</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['angkatan'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">Semester Masuk</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['semester_masuk'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">Status Mahasiswa</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['status_mahasiswa'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">Jenjang / Kode Prodi</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['jenjang_kode'] ?? NULL)); ?> / <?php echo html_escape($v($profile['prodi_kode'] ?? NULL)); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Profil Pribadi</strong></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="small-text">Tempat, Tanggal Lahir</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['tempat_lahir'] ?? NULL)); ?>, <?php echo html_escape($d($profile['tanggal_lahir'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">Jenis Kelamin</div>
            <div class="font-weight-bold"><?php echo html_escape($jenisKelamin); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">Agama</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['agama'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">Golongan Darah</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['golongan_darah'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">Tinggi Badan</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['tinggi_badan'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">Berat Badan</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['berat_badan'] ?? NULL)); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Kontak & Domisili</strong></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="small-text">Email Kampus</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['email_kampus'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">Email Akun</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['email'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">No HP</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['no_hp'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-12 mb-3">
            <div class="small-text">Alamat</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['alamat'] ?? NULL)); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Orang Tua / Wali & Sekolah Asal</strong></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="small-text">Nama Ortu/Wali</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['nama_ortu_wali'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="small-text">Telp Ortu/Wali</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['telp_ortu_wali'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-12 mb-3">
            <div class="small-text">Pekerjaan Ortu/Wali</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['pekerjaan_ortu_wali'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-8 mb-3">
            <div class="small-text">Sekolah Asal</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['nama_sekolah'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="small-text">Tahun Lulus</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['tahun_lulus'] ?? NULL)); ?></div>
          </div>
          <div class="col-md-12">
            <div class="small-text">Jenjang Sekolah</div>
            <div class="font-weight-bold"><?php echo html_escape($v($profile['pendidikan_sekolah'] ?? NULL)); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
