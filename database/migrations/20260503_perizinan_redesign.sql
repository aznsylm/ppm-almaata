-- Perizinan Workflow Redesign (May 3, 2026)
-- Add tipe_izin, sub_kategori, dokumentasi columns

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'tipe_izin') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `tipe_izin` ENUM("1","2","3","4") DEFAULT NULL AFTER `id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'sub_kategori') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `sub_kategori` VARCHAR(100) DEFAULT NULL AFTER `tipe_izin`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'dokumentasi') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `dokumentasi` VARCHAR(255) DEFAULT NULL AFTER `file_upload_at`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
