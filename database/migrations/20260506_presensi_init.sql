-- Presensi / Kehadiran Santri (May 2026)
-- Tabel 1: jadwal periode waktu presensi per kegiatan
-- Tabel 2: record kehadiran santri (hadir & izin saja, alpha on-the-fly)
-- Tabel 3: rekap kartu mingguan per santri

-- ============================================================
-- 1. presensi_jadwal
-- ============================================================
CREATE TABLE IF NOT EXISTS `presensi_jadwal` (
  `id`         TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kegiatan`   ENUM('jamaah_maghrib','jamaah_isya','jamaah_subuh','ngaji_maghrib','ngaji_subuh') NOT NULL,
  `jam_mulai`  TIME NOT NULL,
  `jam_selesai` TIME NOT NULL,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME DEFAULT NULL,
  `updated_by` VARCHAR(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kegiatan` (`kegiatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed jadwal default
INSERT IGNORE INTO `presensi_jadwal` (`kegiatan`, `jam_mulai`, `jam_selesai`) VALUES
  ('jamaah_maghrib', '17:45:00', '19:00:00'),
  ('jamaah_isya',    '19:00:00', '20:30:00'),
  ('jamaah_subuh',   '04:00:00', '05:30:00'),
  ('ngaji_maghrib',  '18:30:00', '19:30:00'),
  ('ngaji_subuh',    '04:30:00', '05:30:00');

-- ============================================================
-- 2. presensi
-- ============================================================
CREATE TABLE IF NOT EXISTS `presensi` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nim`         VARCHAR(20) NOT NULL,
  `kegiatan`    ENUM('jamaah_maghrib','jamaah_isya','jamaah_subuh','ngaji_maghrib','ngaji_subuh') NOT NULL,
  `tanggal`     DATE NOT NULL,
  `status`      ENUM('hadir','izin') NOT NULL DEFAULT 'hadir',
  `id_izin`     VARCHAR(30) DEFAULT NULL COMMENT 'FK ke ijin.id jika status=izin',
  `presensi_at` DATETIME DEFAULT NULL,
  `created_by`  VARCHAR(20) DEFAULT NULL COMMENT 'NULL=santri sendiri, isi=NIM admin (manual)',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_presensi` (`nim`, `kegiatan`, `tanggal`),
  KEY `idx_nim_tanggal` (`nim`, `tanggal`),
  KEY `idx_tanggal_kegiatan` (`tanggal`, `kegiatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 3. presensi_kartu
-- ============================================================
CREATE TABLE IF NOT EXISTS `presensi_kartu` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nim`                 VARCHAR(20) NOT NULL,
  `minggu_mulai`        DATE NOT NULL COMMENT 'Hari Minggu',
  `minggu_selesai`      DATE NOT NULL COMMENT 'Hari Sabtu',
  `alpha_jamaah`        TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `alpha_ngaji`         TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `kartu_jamaah`        ENUM('putih','kuning','orange','merah','hitam') NOT NULL DEFAULT 'putih',
  `kartu_ngaji`         ENUM('putih','kuning','merah') NOT NULL DEFAULT 'putih',
  `is_final`            TINYINT(1) NOT NULL DEFAULT 0,
  `finalized_at`        DATETIME DEFAULT NULL,
  `finalized_by`        VARCHAR(20) DEFAULT NULL,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kartu` (`nim`, `minggu_mulai`),
  KEY `idx_minggu` (`minggu_mulai`, `minggu_selesai`),
  KEY `idx_nim` (`nim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
