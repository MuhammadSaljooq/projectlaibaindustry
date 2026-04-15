-- =============================================================================
-- LIVE SITE — International payables grouping by vendor name (idempotent)
-- =============================================================================
-- Purpose: support grouped listing on International Payables index where
-- invoices with the same vendor name are merged into one row.
--
-- NOTE: This feature is primarily application logic (controller/view routing).
-- No new columns are required. This script adds safe indexes to keep grouping
-- and drill-down queries fast on production datasets.
--
-- Safe to run multiple times. Backup first.
-- =============================================================================

SET @db := DATABASE();

-- ---------------------------------------------------------------------------
-- suppliers.name lookup index (used for LOWER(TRIM(name)) matching flow)
-- ---------------------------------------------------------------------------
-- Existing schema usually has this index already. Add only if missing.
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'suppliers'
    AND INDEX_NAME = 'suppliers_name_index'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: suppliers_name_index exists'' AS _msg',
  'ALTER TABLE `suppliers` ADD INDEX `suppliers_name_index` (`name`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- international_purchase_orders.supplier_id index (group drill-down)
-- ---------------------------------------------------------------------------
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_purchase_orders'
    AND INDEX_NAME = 'international_purchase_orders_supplier_id_index'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: international_purchase_orders_supplier_id_index exists'' AS _msg',
  'ALTER TABLE `international_purchase_orders` ADD INDEX `international_purchase_orders_supplier_id_index` (`supplier_id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- Optional composite index for listing order by date within supplier groups
-- ---------------------------------------------------------------------------
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'international_purchase_orders'
    AND INDEX_NAME = 'ipo_supplier_date_idx'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: ipo_supplier_date_idx exists'' AS _msg',
  'ALTER TABLE `international_purchase_orders` ADD INDEX `ipo_supplier_date_idx` (`supplier_id`, `date`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SELECT 'live_schema_international_payables_grouping.sql completed' AS result;
