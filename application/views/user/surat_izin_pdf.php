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

$format_angka_hari = function ($hari) {
    $hari = (int) $hari;
    return $hari > 0 ? (string) $hari : '-';
};

$is_haid = mb_strtolower(trim((string) $izin['alasan'])) === 'haid';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Surat Bebas Biaya Makan Sementara</title>
  <style>
    body {
      font-family: "Times New Roman", serif;
      color: #000;
      margin: 0;
      padding: 0;
      line-height: 1.15;
      font-size: 15.5px;
    }
    .page {
      padding: 28px 30px 18px 30px;
    }
    .title {
      text-align: center;
      font-size: 18px;
      font-weight: 700;
      margin: 0 0 54px 0;
    }
    .intro {
      margin: 0 0 26px 0;
      font-size: 16px;
    }
    table {
      border-collapse: collapse;
      margin-left: 34px;
      margin-bottom: 26px;
      font-size: 15.5px;
    }
    td {
      padding: 2px 0;
      vertical-align: top;
    }
    .label {
      width: 126px;
      padding-right: 10px;
      white-space: nowrap;
    }
    .sep {
      width: 14px;
      white-space: nowrap;
      text-align: left;
    }
    .body-paragraph {
      text-align: justify;
      font-size: 15.5px;
      margin: 0 0 18px 0;
    }
    .sign-block {
      width: 340px;
      margin-left: 52%;
      margin-top: 68px;
      text-align: center;
      font-size: 15.5px;
    }
    .signature-space {
      height: 92px;
    }
    .signature-name {
      white-space: nowrap;
      display: inline-block;
      margin-top: 2px;
    }
    .footnote {
      margin-top: 78px;
      font-size: 14.5px;
      font-style: italic;
      max-width: 620px;
    }
    @media print {
      body {
        padding: 0;
      }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="title">SURAT BEBAS BIAYA MAKAN SEMENTARA DI ASRAMA PPM ALMA ATA</div>

    <div class="intro">
      Dengan ini, pengasuh PPM Alma Ata telah memberikan izin kepada:
    </div>

    <table>
      <tr>
        <td class="label">Nama</td>
        <td class="sep">:</td>
        <td><?php echo html_escape($izin['nama']); ?></td>
      </tr>
      <tr>
        <td class="label">NIM</td>
        <td class="sep">:</td>
        <td><?php echo html_escape($izin['nim']); ?></td>
      </tr>
      <tr>
        <td class="label">No Kamar</td>
        <td class="sep">:</td>
        <td><?php echo html_escape($izin['kamar']); ?></td>
      </tr>
      <tr>
        <td class="label">Program Studi</td>
        <td class="sep">:</td>
        <td><?php echo html_escape($izin['prodi']); ?></td>
      </tr>
      <tr>
        <td class="label">Semester</td>
        <td class="sep">:</td>
        <td><?php echo html_escape($izin['smt']); ?></td>
      </tr>
    </table>

    <div class="body-paragraph">
      Akan meninggalkan asrama dengan keperluan <strong><?php echo html_escape($izin['alasan']); ?></strong> selama <strong><?php echo html_escape($format_angka_hari($durasi_hari)); ?> hari</strong>, mulai dari tanggal <strong><?php echo html_escape($format_indo($izin['tgl_mulai'])); ?></strong> sampai dengan tanggal <strong><?php echo html_escape($format_indo($izin['tgl_selesai'])); ?></strong>.
    </div>

    <div class="body-paragraph">
      Oleh karena itu pula kami telah menyetujui agar santri ini dibebaskan dari biaya makan di asrama selama periode waktu diatas. Demikian keterangan ini kami buat agar dapat dipergunakan sebagaimana mestinya.
    </div>

    <div class="sign-block"> 
      Yogyakarta, <?php echo html_escape($format_indo_datetime($surat_date)); ?><br>
      Pengurus PPM Alma Ata
      <div class="signature-space"></div>
      <span class="signature-name">Prof. dr. H. Hamam Hadi, MS., Sc.D, Sp.GK</span>
    </div>

    <?php if (!$is_haid): ?>
      <div class="footnote">
        *Keterangan: Surat bebas biaya makan diserahkan ke Bagian Keuangan PPM Alma Ata sebelum santri meninggalkan asrama.
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
