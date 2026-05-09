-- Migration: add dokumentasi_at datetime column to ijin table
ALTER TABLE `ijin`
  ADD COLUMN `dokumentasi_at` DATETIME NULL AFTER `dokumentasi`;
