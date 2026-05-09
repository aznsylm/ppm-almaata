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

$pdf_template = isset($pdf_template) && is_array($pdf_template) ? $pdf_template : array();
$pdf_title = isset($pdf_template['title']) && $pdf_template['title'] !== '' ? $pdf_template['title'] : 'SURAT IZIN MENINGGALKAN ASRAMA PPM ALMA ATA';
$pdf_subtitle = isset($pdf_template['subtitle']) ? $pdf_template['subtitle'] : '';
$show_footnote = !isset($pdf_template['show_footnote']) || (bool) $pdf_template['show_footnote'];

$is_haid = mb_strtolower(trim((string) $izin['alasan'])) === 'haid';

// Determine template type for body content
$tipe_izin = isset($izin['tipe_izin']) ? (string) $izin['tipe_izin'] : '';
$normalize_list = function ($value) {
  if (is_array($value)) {
    $items = $value;
  } elseif (is_string($value)) {
    $items = strpos($value, ',') !== FALSE ? explode(',', $value) : array($value);
  } else {
    $items = array();
  }

  $normalized = array();
  foreach ($items as $item) {
    $item = trim((string) $item);
    if ($item !== '' && !in_array($item, $normalized, TRUE)) {
      $normalized[] = $item;
    }
  }

  return $normalized;
};

$join_human_list = function (array $items) {
  $count = count($items);
  if ($count === 0) {
    return '-';
  }
  if ($count === 1) {
    return $items[0];
  }
  if ($count === 2) {
    return $items[0] . ' dan ' . $items[1];
  }

  $last = array_pop($items);
  return implode(', ', $items) . ', dan ' . $last;
};

$sub_kategori_list = $normalize_list(isset($izin['sub_kategori']) ? $izin['sub_kategori'] : '');
$alasan_list = $normalize_list(isset($izin['alasan']) ? $izin['alasan'] : '');
$is_meninggalkan = ($tipe_izin === '1' || $tipe_izin === '2');
$is_jamaah_ngaji  = ($tipe_izin === '3');

// Bangun teks sub_kategori yang human-readable
$kategori_map_pdf = array(
  'jamaah_maghrib' => 'Jamaah Sholat Maghrib',
  'jamaah_isya'    => 'Jamaah Sholat Isya',
  'jamaah_subuh'   => 'Jamaah Sholat Subuh',
  'ngaji_maghrib'  => 'Ngaji Ba\'da Maghrib',
  'ngaji_subuh'    => 'Ngaji Ba\'da Subuh',
);
$sub_kategori_labels = array();
foreach ($sub_kategori_list as $item) {
  $label = isset($kategori_map_pdf[$item]) ? $kategori_map_pdf[$item] : ucfirst(str_replace('_', ' ', $item));
  $sub_kategori_labels[] = $label;
}
$sub_kategori_text = $join_human_list($sub_kategori_labels);

// Alasan text — gabungkan alasan + alasan_lainnya jika ada
$alasan_lainnya_val = isset($izin['alasan_lainnya']) ? trim((string) $izin['alasan_lainnya']) : '';
if ($alasan_lainnya_val !== '') {
  $alasan_list[] = $alasan_lainnya_val;
}
$alasan_text = $join_human_list($alasan_list);
$salam_pembuka = 'Assalamualaikum warahmatullahi wabarakatuh';
$salam_penutup = 'Wassalamualaikum warahmatullahi wabarakatuh';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo html_escape($pdf_title); ?></title>
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
      padding: 30px 48px 22px 54px;
    }
    .title {
      text-align: center;
      font-size: 16px;
      font-weight: 700;
      margin: 0 0 42px 0;
      line-height: 1.3;
    }
    .intro {
      margin: 0 0 26px 0;
      font-size: 16px;
    }
    table {
      border-collapse: collapse;
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
      line-height: 1.16;
    }
    .sign-block {
      width: 100%;
      margin-top: 68px;
      text-align: right;
      padding-right: 34px;
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
      max-width: 100%;
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
    <div class="title"><?php echo html_escape($pdf_title); ?></div>

    <?php if ($pdf_subtitle !== ''): ?>
      <div class="intro" style="text-align:center; margin-top: -34px; margin-bottom: 40px; font-size: 14px; font-style: italic;">
        <?php echo html_escape($pdf_subtitle); ?>
      </div>
    <?php endif; ?>

    <div class="intro">
      <?php echo html_escape($salam_pembuka); ?>
    </div>

    <div class="intro">
      <?php if ($is_meninggalkan): ?>
        Dengan ini, pengasuh PPM Alma Ata telah memberikan izin kepada:
      <?php else: ?>
        Dengan ini, pengurus PPM Alma Ata telah memberikan izin kepada:
      <?php endif; ?>
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

    <?php if ($is_meninggalkan): ?>
      <div class="body-paragraph">
        Untuk meninggalkan asrama dengan keperluan <strong><?php echo html_escape($alasan_text); ?></strong> selama <strong><?php echo html_escape($format_angka_hari($durasi_hari)); ?> hari</strong>, mulai dari tanggal <strong><?php echo html_escape($format_indo($izin['tgl_mulai'])); ?></strong> sampai dengan tanggal <strong><?php echo html_escape($format_indo($izin['tgl_selesai'])); ?></strong>.
      </div>
      <?php if ($tipe_izin === '2'): ?>
        <div class="body-paragraph">
          Oleh karena itu pula kami telah menyetujui untuk meninggalkan asrama dan membebaskan santri ini dari biaya makan sementara selama periode diatas. Demikian keterangan ini kami buat agar dapat dipergunakan sebagaimana mestinya.
        </div>
      <?php else: ?>
        <div class="body-paragraph">
          Oleh karena itu pula kami telah menyetujui untuk meninggalkan asrama selama periode waktu diatas. Demikian keterangan ini kami buat agar dapat dipergunakan sebagaimana mestinya.
        </div>
      <?php endif; ?>
    <?php elseif ($is_jamaah_ngaji): ?>
      <div class="body-paragraph">
        Untuk tidak mengikuti <strong><?php echo html_escape($sub_kategori_text); ?></strong> pada tanggal <strong><?php echo html_escape($format_indo($izin['tgl_mulai'])); ?></strong> karena <strong><?php echo html_escape($alasan_text); ?></strong>.
      </div>
      <div class="body-paragraph">
        Demikian keterangan ini kami buat agar dapat dipergunakan sebagaimana mestinya.
      </div>
    <?php else: ?>
      <div class="body-paragraph">
        Untuk meninggalkan asrama dengan keperluan <strong><?php echo html_escape($alasan_text); ?></strong> selama <strong><?php echo html_escape($format_angka_hari($durasi_hari)); ?> hari</strong>, mulai dari tanggal <strong><?php echo html_escape($format_indo($izin['tgl_mulai'])); ?></strong> sampai dengan tanggal <strong><?php echo html_escape($format_indo($izin['tgl_selesai'])); ?></strong>.
      </div>
      <div class="body-paragraph">
        Demikian keterangan ini kami buat agar dapat dipergunakan sebagaimana mestinya.
      </div>
    <?php endif; ?>

    <div class="intro" style="margin-top: 18px; font-size: 15.5px;">
      <?php echo html_escape($salam_penutup); ?>
    </div>

    <div class="sign-block"> 
      Yogyakarta, <?php echo html_escape($format_indo_datetime($surat_date)); ?><br>
      <?php if ($is_meninggalkan): ?>
        Pengasuh PPM Alma Ata
      <?php else: ?>
        Ketua Pengurus PPM Alma Ata
      <?php endif; ?>
      <div class="signature-space"></div>
      <span class="signature-name">
        <?php if ($is_meninggalkan): ?>
          KH. Hamam Hadi
        <?php else: ?>
          Haifa Inayatun Azizah
        <?php endif; ?>
      </span>
    </div>

    <?php if ($show_footnote && !$is_haid): ?>
      <div class="footnote">
        *Keterangan: Surat izin ini diserahkan/upload untuk pengurus asrama.
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
