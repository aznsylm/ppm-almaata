-- PPM Alma Ata auth initialization (Strategy B)
-- MySQL 8+

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nim` VARCHAR(15) NOT NULL,
  `email` VARCHAR(200) DEFAULT NULL,
  `password_hash` VARCHAR(255) DEFAULT NULL,
  `legacy_password_md5` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `must_reset_password` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME DEFAULT NULL,
  `password_updated_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_nim` (`nim`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_role_status` (`role`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `users` (`nim`, `email`, `legacy_password_md5`, `role`, `status`, `must_reset_password`)
SELECT
  source_data.nim_clean,
  NULLIF(TRIM(msmhs.EMAIL), ''),
  NULLIF(source_data.pass_clean, ''),
  'user',
  'active',
  1
FROM (
  SELECT TRIM(nim) AS nim_clean, MAX(TRIM(pass)) AS pass_clean
  FROM mssantri
  WHERE TRIM(nim) <> ''
  GROUP BY TRIM(nim)
) AS source_data
LEFT JOIN msmhs ON TRIM(msmhs.NIMHSMSMHS) = source_data.nim_clean
ON DUPLICATE KEY UPDATE
  legacy_password_md5 = VALUES(legacy_password_md5),
  updated_at = CURRENT_TIMESTAMP;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'created_at') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'updated_at') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'approved_by') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `approved_by` VARCHAR(15) DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'approved_at') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `approved_at` DATETIME DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND COLUMN_NAME = 'approval_note') = 0,
  'ALTER TABLE `ijin` ADD COLUMN `approval_note` TEXT',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijin'
     AND INDEX_NAME = 'idx_ijin_nim_acc') = 0,
  'CREATE INDEX `idx_ijin_nim_acc` ON `ijin` (`nim`, `acc`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'ijindetail'
     AND INDEX_NAME = 'idx_ijindetail_id_ijin') = 0,
  'CREATE INDEX `idx_ijindetail_id_ijin` ON `ijindetail` (`id_ijin`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
