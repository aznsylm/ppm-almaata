<?php
// Script untuk cek jadwal presensi
require_once 'index.php';

// Get CI instance
$CI =& get_instance();
$CI->load->database();

echo "<h3>Cek Jadwal Presensi</h3>";

// Cek semua jadwal
$jadwal = $CI->db->get('presensi_jadwal')->result_array();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Kegiatan</th><th>Jam Mulai</th><th>Jam Selesai</th><th>Aktif</th></tr>";

foreach ($jadwal as $j) {
    $aktif = $j['is_active'] ? 'Ya' : 'Tidak';
    echo "<tr>";
    echo "<td>{$j['id']}</td>";
    echo "<td>{$j['kegiatan']}</td>";
    echo "<td>{$j['jam_mulai']}</td>";
    echo "<td>{$j['jam_selesai']}</td>";
    echo "<td>{$aktif}</td>";
    echo "</tr>";
}

echo "</table>";

// Cek jadwal yang aktif sekarang
$now = date('H:i:s');
echo "<br><strong>Waktu sekarang:</strong> $now<br>";

$aktif_sekarang = $CI->db
    ->where('is_active', 1)
    ->where('jam_mulai <=', $now)
    ->where('jam_selesai >=', $now)
    ->get('presensi_jadwal')
    ->result_array();

echo "<br><strong>Jadwal aktif sekarang:</strong><br>";
if (empty($aktif_sekarang)) {
    echo "Tidak ada jadwal aktif sekarang<br>";
} else {
    foreach ($aktif_sekarang as $j) {
        echo "- {$j['kegiatan']} ({$j['jam_mulai']} - {$j['jam_selesai']})<br>";
    }
}

// Cek semua jadwal aktif (tanpa filter waktu)
$semua_aktif = $CI->db
    ->where('is_active', 1)
    ->get('presensi_jadwal')
    ->result_array();

echo "<br><strong>Semua jadwal aktif (untuk admin testing):</strong><br>";
foreach ($semua_aktif as $j) {
    echo "- {$j['kegiatan']} ({$j['jam_mulai']} - {$j['jam_selesai']})<br>";
}
?>