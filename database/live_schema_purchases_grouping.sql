-- =============================================================================
-- LIVE SITE — Purchases grouping by customer (idempotent MySQL / MariaDB)
-- =============================================================================
-- Purpose:
--   • Support grouped Purchases index (same customer code/name shown as one row)
--   • Support grouped Purchases detail page lookups by code/name
--
-- App change is mainly controller/view logic. No new tables are required.
-- This script ensures production has the indexes used by grouped queries.
--
-- Safe to run multiple times. Backup first.
-- =============================================================================

SET @db := DATABASE();

-- ---------------------------------------------------------------------------
-- purchases.customer_code (primary grouping key when present)
-- ---------------------------------------------------------------------------
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'purchases'
    AND INDEX_NAME = 'idx_customer_code'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: purchases.idx_customer_code exists'' AS _msg',
  'ALTER TABLE `purchases` ADD INDEX `idx_customer_code` (`customer_code`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- purchases.customer_name (fallback grouping key when code is missing)
-- ---------------------------------------------------------------------------
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'purchases'
    AND INDEX_NAME = 'idx_customer_name'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: purchases.idx_customer_name exists'' AS _msg',
  'ALTER TABLE `purchases` ADD INDEX `idx_customer_name` (`customer_name`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- purchases.date (latest invoice ordering in grouped views)
-- ---------------------------------------------------------------------------
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'purchases'
    AND INDEX_NAME = 'idx_date'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: purchases.idx_date exists'' AS _msg',
  'ALTER TABLE `purchases` ADD INDEX `idx_date` (`date`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- Composite index for grouped drill-down and sorting
-- ---------------------------------------------------------------------------
SET @idx_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'purchases'
    AND INDEX_NAME = 'purchases_customer_code_date_id_idx'
);
SET @sql := IF(
  @idx_exists > 0,
  'SELECT ''skip: purchases_customer_code_date_id_idx exists'' AS _msg',
  'ALTER TABLE `purchases` ADD INDEX `purchases_customer_code_date_id_idx` (`customer_code`, `date`, `id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SELECT 'live_schema_purchases_grouping.sql completed' AS result;
