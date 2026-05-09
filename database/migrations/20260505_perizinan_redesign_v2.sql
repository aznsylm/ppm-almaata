-- Perizinan Redesign v2 (May 5, 2026)
-- 1. Ubah tipe_izin: tipe 3 = gabungan jamaah+ngaji (tipe 4 deprecated)
-- 2. Tambah status 5 = Menunggu Dokumentasi
-- 3. Tambah kolom alasan_lainnya untuk tipe 1 & 2
-- 4. Update data lama: tipe_izin 4 -> 3

-- Perluas ENUM status agar support nilai '5'
ALTER TABLE `ijin` MODIFY COLUMN `status` ENUM('0','1','2','3','4','5') NOT NULL DEFAULT '0';

-- Tambah kolom alasan_lainnya (teks bebas untuk tipe 1 & 2)
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'alasan_lainnya') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `alasan_lainnya` TEXT DEFAULT NULL AFTER `alasan`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Migrate data lama: tipe_izin '4' -> '3', sub_kategori sesuaikan prefix
-- (ngaji maghrib -> ngaji_maghrib, ngaji subuh -> ngaji_subuh)
UPDATE `ijin`
SET
  `tipe_izin` = '3',
  `sub_kategori` = CASE
    WHEN `sub_kategori` LIKE '%maghrib%' THEN CONCAT(TRIM(`sub_kategori`), '')
    ELSE `sub_kategori`
  END
WHERE `tipe_izin` = '4';
