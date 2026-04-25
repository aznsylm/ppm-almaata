-- Perizinan workflow update (April 14, 2026)
-- Tambah status dan file upload tracking

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'status') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `status` ENUM("0","1","2","3","4") NOT NULL DEFAULT "0" AFTER `acc`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'file_upload') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `file_upload` VARCHAR(255) DEFAULT NULL AFTER `status`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'file_upload_at') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `file_upload_at` DATETIME DEFAULT NULL AFTER `file_upload`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND INDEX_NAME = 'idx_ijin_status') = 0,
  'CREATE INDEX `idx_ijin_status` ON `ijin` (`status`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
