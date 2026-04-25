-- Perizinan manual control (April 15, 2026)
-- Add reversible suspension state for early finished / resumed izin

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'is_suspended') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `is_suspended` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'suspended_at') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `suspended_at` DATETIME DEFAULT NULL AFTER `is_suspended`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'suspended_by') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `suspended_by` VARCHAR(15) DEFAULT NULL AFTER `suspended_at`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'suspended_note') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `suspended_note` TEXT AFTER `suspended_by`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'resumed_at') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `resumed_at` DATETIME DEFAULT NULL AFTER `suspended_note`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'resumed_by') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `resumed_by` VARCHAR(15) DEFAULT NULL AFTER `resumed_at`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'resumed_note') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `resumed_note` TEXT AFTER `resumed_by`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND INDEX_NAME = 'idx_ijin_manual_state') = 0,
  'CREATE INDEX `idx_ijin_manual_state` ON `ijin` (`nim`, `acc`, `is_suspended`, `tgl_mulai`, `tgl_selesai`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
