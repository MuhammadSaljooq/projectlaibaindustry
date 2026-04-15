-- =============================================================================
-- LIVE SITE — Payables page combined-payment tables (idempotent)
-- =============================================================================
-- Purpose:
--   Receivable-style grouped/combined payments on Payables group page.
--   This is a standalone script for the Payables page updates only.
-- =============================================================================

SET @db := DATABASE();

-- 1) payable_group_payments
CREATE TABLE IF NOT EXISTS `payable_group_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_key` VARCHAR(512) NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payable_group_payments_group_key_index` (`group_key`),
  KEY `payable_group_payments_payment_date_index` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) payable_group_payment_lines
CREATE TABLE IF NOT EXISTS `payable_group_payment_lines` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payable_group_payment_id` BIGINT UNSIGNED NOT NULL,
  `payable_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `customer_ledger_entry_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pgpl_payable_id_idx` (`payable_id`),
  KEY `pgpl_cust_ledger_idx` (`customer_ledger_entry_id`),
  KEY `pgpl_ap_group_pay_fk` (`payable_group_payment_id`),
  CONSTRAINT `pgpl_ap_group_pay_fk`
    FOREIGN KEY (`payable_group_payment_id`) REFERENCES `payable_group_payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) customer_ledger_payable_group_payments (link table)
CREATE TABLE IF NOT EXISTS `customer_ledger_payable_group_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_ledger_entry_id` INT UNSIGNED NOT NULL,
  `payable_group_payment_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cle_pg_unique` (`customer_ledger_entry_id`, `payable_group_payment_id`),
  KEY `cle_pg_group_idx` (`payable_group_payment_id`),
  KEY `cle_pg_ledger_idx` (`customer_ledger_entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) customer_ledger_entries.payable_group_payment_id
SET @col_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'customer_ledger_entries'
    AND COLUMN_NAME = 'payable_group_payment_id'
);
SET @sql := IF(
  @col_exists > 0,
  'SELECT ''skip: customer_ledger_entries.payable_group_payment_id exists'' AS _msg',
  'ALTER TABLE `customer_ledger_entries` ADD COLUMN `payable_group_payment_id` BIGINT UNSIGNED NULL AFTER `receivable_group_payment_id`'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'customer_ledger_entries'
    AND INDEX_NAME = 'cle_ap_group_pay_idx'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: cle_ap_group_pay_idx exists'' AS _msg',
  'ALTER TABLE `customer_ledger_entries` ADD INDEX `cle_ap_group_pay_idx` (`payable_group_payment_id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @fk_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'customer_ledger_entries'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    AND CONSTRAINT_NAME = 'cle_ap_group_pay_fk'
);
SET @sql := IF(
  @fk_exists > 0,
  'SELECT ''skip: cle_ap_group_pay_fk exists'' AS _msg',
  'ALTER TABLE `customer_ledger_entries` ADD CONSTRAINT `cle_ap_group_pay_fk` FOREIGN KEY (`payable_group_payment_id`) REFERENCES `payable_group_payments` (`id`) ON DELETE SET NULL'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SELECT 'live_schema_payables_page_combined_payment.sql completed' AS result;
