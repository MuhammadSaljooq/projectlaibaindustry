-- =============================================================================
-- LIVE SITE — International payable payment management (idempotent)
-- =============================================================================
-- Purpose: ensure production schema supports recording international payable
-- payments by date and in multiple parts, including edit/delete workflows.
--
-- App changes for this feature are controller/routes/UI only; no new columns
-- were introduced. This script safely ensures required tables/indexes/FKs exist.
--
-- Safe to run multiple times. Backup first. Deploy matching app code.
-- =============================================================================

SET @db := DATABASE();

-- ---------------------------------------------------------------------------
-- 1) international_payable_payments (required)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `international_payable_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `international_purchase_order_id` BIGINT UNSIGNED NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `notes` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `int_pay_pay_order_id_index` (`international_purchase_order_id`),
  KEY `int_pay_pay_payment_date_index` (`payment_date`),
  CONSTRAINT `int_pay_pay_order_id_foreign`
    FOREIGN KEY (`international_purchase_order_id`) REFERENCES `international_purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing index on order id (if table existed without it)
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_payments'
    AND INDEX_NAME = 'int_pay_pay_order_id_index'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: int_pay_pay_order_id_index exists'' AS _msg',
  'ALTER TABLE `international_payable_payments` ADD INDEX `int_pay_pay_order_id_index` (`international_purchase_order_id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- Add missing index on payment_date
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_payments'
    AND INDEX_NAME = 'int_pay_pay_payment_date_index'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: int_pay_pay_payment_date_index exists'' AS _msg',
  'ALTER TABLE `international_payable_payments` ADD INDEX `int_pay_pay_payment_date_index` (`payment_date`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- Add FK to order if missing (skip if FK already present)
SET @fk_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_payments'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    AND CONSTRAINT_NAME = 'int_pay_pay_order_id_foreign'
);
SET @sql := IF(
  @fk_exists > 0,
  'SELECT ''skip: int_pay_pay_order_id_foreign exists'' AS _msg',
  'ALTER TABLE `international_payable_payments` ADD CONSTRAINT `int_pay_pay_order_id_foreign` FOREIGN KEY (`international_purchase_order_id`) REFERENCES `international_purchase_orders` (`id`) ON DELETE CASCADE'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- 2) supplier_ledger_entries source lookup index (performance for edit/delete)
-- ---------------------------------------------------------------------------
-- International payment update/delete uses:
--   WHERE source_type = ''international_payable_payment'' AND source_id = ?
-- Ensure a composite index exists (matches SupplierLedgerSchema defaults).
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'supplier_ledger_entries'
    AND INDEX_NAME = 'supplier_ledger_source_index'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: supplier_ledger_source_index exists'' AS _msg',
  'ALTER TABLE `supplier_ledger_entries` ADD INDEX `supplier_ledger_source_index` (`source_type`, `source_id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SELECT 'live_schema_international_payable_payment_management.sql completed' AS result;
