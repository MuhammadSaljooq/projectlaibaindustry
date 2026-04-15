-- =============================================================================
-- LIVE SITE — International payables combined payment (idempotent)
-- =============================================================================
-- Purpose:
--   Support "record one combined payment for a vendor group" flow on
--   International Payables (FIFO allocation across open invoices).
--
-- Notes:
--   - No new columns/tables are required by this feature.
--   - This script adds safe indexes for production performance.
--   - Safe to run multiple times.
-- =============================================================================

SET @db := DATABASE();

-- ---------------------------------------------------------------------------
-- 1) international_purchase_orders: supplier/date/id traversal for FIFO
-- ---------------------------------------------------------------------------
-- Combined payment gathers vendor-group invoices oldest-first:
--   WHERE supplier_id = ? ORDER BY date, id
-- Add composite index if missing.
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_purchase_orders'
    AND INDEX_NAME = 'ipo_supplier_date_id_idx'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: ipo_supplier_date_id_idx exists'' AS _msg',
  'ALTER TABLE `international_purchase_orders` ADD INDEX `ipo_supplier_date_id_idx` (`supplier_id`, `date`, `id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- 2) international_payable_payments: order/date/id scans
-- ---------------------------------------------------------------------------
-- Payment history and aggregate flows frequently use:
--   WHERE international_purchase_order_id = ?
--   ORDER BY payment_date DESC, id DESC
-- Add composite index if missing.
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_payable_payments'
    AND INDEX_NAME = 'int_pay_pay_order_date_id_idx'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: int_pay_pay_order_date_id_idx exists'' AS _msg',
  'ALTER TABLE `international_payable_payments` ADD INDEX `int_pay_pay_order_date_id_idx` (`international_purchase_order_id`, `payment_date`, `id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- 3) supplier_ledger_entries: source/date lookup for audit trails
-- ---------------------------------------------------------------------------
-- Existing source index may already exist; this adds a date-extended variant
-- used by timeline-style reporting while filtering by source tuple.
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'supplier_ledger_entries'
    AND INDEX_NAME = 'supplier_ledger_source_date_idx'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: supplier_ledger_source_date_idx exists'' AS _msg',
  'ALTER TABLE `supplier_ledger_entries` ADD INDEX `supplier_ledger_source_date_idx` (`source_type`, `source_id`, `date`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SELECT 'live_schema_international_payables_combined_payment.sql completed' AS result;
