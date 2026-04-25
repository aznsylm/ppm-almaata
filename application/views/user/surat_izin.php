<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$bulan = array(
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
);

$format_indo = function ($date) use ($bulan) {
    $ts = strtotime($date);
    if (!$ts) {
        return '-';
    }
    return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
};

$format_indo_datetime = function ($date) use ($bulan) {
    $ts = strtotime($date);
    if (!$ts) {
        return '-';
    }
    return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Surat Keterangan Izin Santri</title>
  <style>
    body {
      font-family: "Times New Roman", serif;
      color: #111;
      margin: 0;
      padding: 24px;
      line-height: 1.4;
    }
    .page {
      max-width: 800px;
      margin: 0 auto;
    }
    .title {
      text-align: center;
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 4px;
      letter-spacing: 0.2px;
    }
    .subtitle {
      text-align: center;
      font-size: 17px;
      font-weight: 700;
      margin: 0 0 18px;
      text-decoration: underline;
    }
    .meta {
      margin-bottom: 14px;
      font-size: 16px;
    }
    table {
      border-collapse: collapse;
      width: 100%;
      margin: 10px 0 16px;
      font-size: 16px;
    }
    td {
      vertical-align: top;
      padding: 3px 4px;
    }
    .col-label {
      width: 210px;
    }
    .col-sep {
      width: 15px;
      text-align: center;
    }
    .paragraph {
      text-align: justify;
      font-size: 16px;
      margin: 12px 0;
    }
    .ttd {
      margin-top: 26px;
      width: 320px;
      margin-left: auto;
      text-align: left;
      font-size: 16px;
    }
    .spacer {
      height: 70px;
    }
    @media print {
      body {
        padding: 0;
      }
      .page {
        max-width: 100%;
      }
    }
  </style>
</head>
<body onload="window.print()">
  <div class="page">
    <div class="title">SURAT KETERANGAN IZIN SANTRI</div>
    <div class="subtitle">PONDOK PESANTREN MAHASISWA ALMA ATA</div>

    <div class="meta">
      Nomor: <?php echo html_escape($izin['id']); ?>/PPM-AA/SK-IZIN
    </div>

    <div class="paragraph">
      Yang bertanda tangan di bawah ini, selaku Pengurus Pondok Pesantren Mahasiswa Alma Ata,
      menerangkan bahwa:
    </div>

    <table>
      <tr>
        <td class="col-label">Nama</td>
        <td class="col-sep">:</td>
        <td><?php echo html_escape($izin['nama']); ?></td>
      </tr>
      <tr>
        <td class="col-label">NIM</td>
        <td class="col-sep">:</td>
        <td><?php echo html_escape($izin['nim']); ?></td>
      </tr>
      <tr>
        <td class="col-label">Kamar</td>
        <td class="col-sep">:</td>
        <td><?php echo html_escape($izin['kamar']); ?></td>
      </tr>
      <tr>
        <td class="col-label">Program Studi</td>
        <td class="col-sep">:</td>
        <td><?php echo html_escape($izin['prodi']); ?></td>
      </tr>
      <tr>
        <td class="col-label">Semester</td>
        <td class="col-sep">:</td>
        <td><?php echo html_escape($izin['smt']); ?></td>
      </tr>
      <tr>
        <td class="col-label">Alamat</td>
        <td class="col-sep">:</td>
        <td><?php echo html_escape($izin['alamat']); ?></td>
      </tr>
    </table>

    <div class="paragraph">
      Adalah benar santri aktif Pondok Pesantren Mahasiswa Alma Ata yang memperoleh izin keluar
      asrama pada tanggal <?php echo html_escape($format_indo($izin['tgl_mulai'])); ?> sampai dengan
      <?php echo html_escape($format_indo($izin['tgl_selesai'])); ?> selama <?php echo (int) $durasi_hari; ?> hari,
      dengan alasan: <?php echo html_escape($izin['alasan']); ?>.
    </div>

    <div class="paragraph">
      Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
    </div>

    <div class="ttd">
      Yogyakarta, <?php echo html_escape($format_indo_datetime($surat_date)); ?><br>
      Pengurus PPM Alma Ata
      <div class="spacer"></div>
      __________________________
    </div>
  </div>
</body>
</html>
