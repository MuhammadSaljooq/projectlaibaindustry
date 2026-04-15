-- =============================================================================
-- LIVE SITE — Payables grouping + combined payment allocation (idempotent)
-- =============================================================================
-- Purpose:
--   • Support grouped Payables list (same customer code/name in one row)
--   • Support combined payment allocation across grouped invoices (FIFO)
--
-- App changes are mostly controller/view logic; no new tables are required.
-- This script ensures production has efficient indexes used by those queries.
--
-- Safe to run multiple times. Backup first.
-- =============================================================================

SET @db := DATABASE();

-- ---------------------------------------------------------------------------
-- payables: grouping + ordering indexes
-- ---------------------------------------------------------------------------
-- Used by grouped Payables index and group drill-down:
--   - GROUP/lookup by customer_code (normalized)
--   - fallback GROUP/lookup by customer_name
--   - FIFO by date,id within a group

SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'payables'
      AND INDEX_NAME = 'idx_customer_code'
);
SET @sql := IF(
    @idx_exists > 0,
    'SELECT ''skip: payables.idx_customer_code exists'' AS _msg',
    'ALTER TABLE `payables` ADD INDEX `idx_customer_code` (`customer_code`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'payables'
      AND INDEX_NAME = 'idx_customer_name'
);
SET @sql := IF(
    @idx_exists > 0,
    'SELECT ''skip: payables.idx_customer_name exists'' AS _msg',
    'ALTER TABLE `payables` ADD INDEX `idx_customer_name` (`customer_name`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'payables'
      AND INDEX_NAME = 'idx_date'
);
SET @sql := IF(
    @idx_exists > 0,
    'SELECT ''skip: payables.idx_date exists'' AS _msg',
    'ALTER TABLE `payables` ADD INDEX `idx_date` (`date`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- Composite index to help FIFO scans inside customer groups
SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'payables'
      AND INDEX_NAME = 'payables_customer_code_date_id_idx'
);
SET @sql := IF(
    @idx_exists > 0,
    'SELECT ''skip: payables_customer_code_date_id_idx exists'' AS _msg',
    'ALTER TABLE `payables` ADD INDEX `payables_customer_code_date_id_idx` (`customer_code`, `date`, `id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- customer_ledger_entries: payment_made sync/update lookup index
-- ---------------------------------------------------------------------------
-- Used by payable sync/update/delete:
--   WHERE source_type = 'payment_made' AND source_id = ?
SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'customer_ledger_entries'
      AND INDEX_NAME = 'idx_source'
);
SET @sql := IF(
    @idx_exists > 0,
    'SELECT ''skip: customer_ledger_entries.idx_source exists'' AS _msg',
    'ALTER TABLE `customer_ledger_entries` ADD INDEX `idx_source` (`source_type`, `source_id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SELECT 'live_schema_payables_grouping_and_combined_payment.sql completed' AS result;
