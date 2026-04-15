-- =============================================================================
-- LIVE SITE — International payable combined-payment tables (idempotent)
-- =============================================================================
SET @db := DATABASE();

CREATE TABLE IF NOT EXISTS `international_payable_group_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_key` VARCHAR(512) NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `notes` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `international_payable_group_payments_group_key_index` (`group_key`),
  KEY `international_payable_group_payments_payment_date_index` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `international_payable_group_payment_lines` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `international_payable_group_payment_id` BIGINT UNSIGNED NOT NULL,
  `international_purchase_order_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `international_payable_payment_id` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ipgpl_order_idx` (`international_purchase_order_id`),
  KEY `ipgpl_payment_idx` (`international_payable_payment_id`),
  KEY `ipgpl_group_fk` (`international_payable_group_payment_id`),
  CONSTRAINT `ipgpl_group_fk`
    FOREIGN KEY (`international_payable_group_payment_id`) REFERENCES `international_payable_group_payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure indexes/FKs exist even if tables already existed
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_group_payments'
    AND INDEX_NAME = 'international_payable_group_payments_group_key_index'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: international_payable_group_payments_group_key_index exists'' AS _msg',
  'ALTER TABLE `international_payable_group_payments` ADD INDEX `international_payable_group_payments_group_key_index` (`group_key`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_group_payments'
    AND INDEX_NAME = 'international_payable_group_payments_payment_date_index'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: international_payable_group_payments_payment_date_index exists'' AS _msg',
  'ALTER TABLE `international_payable_group_payments` ADD INDEX `international_payable_group_payments_payment_date_index` (`payment_date`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_group_payment_lines'
    AND INDEX_NAME = 'ipgpl_order_idx'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: ipgpl_order_idx exists'' AS _msg',
  'ALTER TABLE `international_payable_group_payment_lines` ADD INDEX `ipgpl_order_idx` (`international_purchase_order_id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_group_payment_lines'
    AND INDEX_NAME = 'ipgpl_payment_idx'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: ipgpl_payment_idx exists'' AS _msg',
  'ALTER TABLE `international_payable_group_payment_lines` ADD INDEX `ipgpl_payment_idx` (`international_payable_payment_id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @fk_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_group_payment_lines'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    AND CONSTRAINT_NAME = 'ipgpl_group_fk'
);
SET @sql := IF(
  @fk_exists > 0,
  'SELECT ''skip: ipgpl_group_fk exists'' AS _msg',
  'ALTER TABLE `international_payable_group_payment_lines` ADD CONSTRAINT `ipgpl_group_fk` FOREIGN KEY (`international_payable_group_payment_id`) REFERENCES `international_payable_group_payments` (`id`) ON DELETE CASCADE'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @col_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_payments'
    AND COLUMN_NAME = 'international_payable_group_payment_id'
);
SET @sql := IF(
  @col_exists > 0,
  'SELECT ''skip: international_payable_payments.international_payable_group_payment_id exists'' AS _msg',
  'ALTER TABLE `international_payable_payments` ADD COLUMN `international_payable_group_payment_id` BIGINT UNSIGNED NULL AFTER `international_purchase_order_id`'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_payments'
    AND INDEX_NAME = 'int_pay_pay_group_idx'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: int_pay_pay_group_idx exists'' AS _msg',
  'ALTER TABLE `international_payable_payments` ADD INDEX `int_pay_pay_group_idx` (`international_payable_group_payment_id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @fk_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_payments'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    AND CONSTRAINT_NAME = 'int_pay_pay_group_fk'
);
SET @sql := IF(
  @fk_exists > 0,
  'SELECT ''skip: int_pay_pay_group_fk exists'' AS _msg',
  'ALTER TABLE `international_payable_payments` ADD CONSTRAINT `int_pay_pay_group_fk` FOREIGN KEY (`international_payable_group_payment_id`) REFERENCES `international_payable_group_payments` (`id`) ON DELETE SET NULL'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SELECT 'live_schema_international_payable_group_payment_tables.sql completed' AS result;
