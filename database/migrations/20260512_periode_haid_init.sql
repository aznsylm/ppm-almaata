-- Create periode_haid table
-- Migration: 2026-05-12
-- Purpose: Store periode haid data for each santri

CREATE TABLE IF NOT EXISTS `periode_haid` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nim` VARCHAR(15) UNIQUE NOT NULL,
  `rata_rata_hari` INT NOT NULL COMMENT 'Rata-rata hari haid (1-50)',
  `paling_lama_hari` INT NOT NULL COMMENT 'Paling lama haid (1-50)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` VARCHAR(15) COMMENT 'Admin yang input',
  `updated_by` VARCHAR(15) COMMENT 'Admin yang update',
  
  FOREIGN KEY (`nim`) REFERENCES `users`(`nim`) ON DELETE CASCADE,
  INDEX `idx_nim` (`nim`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
