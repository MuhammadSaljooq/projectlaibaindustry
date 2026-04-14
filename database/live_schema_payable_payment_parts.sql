-- =============================================================================
-- LIVE SITE — Payable payment parts (idempotent MySQL / MariaDB)
-- =============================================================================
-- Purpose: support payable payment handling similar to receivables:
--   • record payments in parts by date
--   • edit/delete payable payment rows from ledger-backed entries
--
-- App code uses existing tables (`payables`, `customer_ledger_entries`), so this
-- script ensures required columns/indexes exist on production safely.
--
-- Safe to run multiple times. Backup first.
-- =============================================================================

SET @db := DATABASE();

-- ---------------------------------------------------------------------------
-- payables.received (total paid so far)
-- ---------------------------------------------------------------------------
SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'payables' AND COLUMN_NAME = 'received'
        ),
        'SELECT ''skip: payables.received exists'' AS _msg',
        'ALTER TABLE `payables` ADD COLUMN `received` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT ''Total amount paid so far'' AFTER `amount`'
    )
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- payables.received_date (latest payment date)
-- ---------------------------------------------------------------------------
SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'payables' AND COLUMN_NAME = 'received_date'
        ),
        'SELECT ''skip: payables.received_date exists'' AS _msg',
        'ALTER TABLE `payables` ADD COLUMN `received_date` DATE NULL AFTER `received`'
    )
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- customer_ledger_entries.source_type/source_id (required for payment_made rows)
-- ---------------------------------------------------------------------------
SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'customer_ledger_entries' AND COLUMN_NAME = 'source_type'
        ),
        'SELECT ''skip: customer_ledger_entries.source_type exists'' AS _msg',
        'ALTER TABLE `customer_ledger_entries` ADD COLUMN `source_type` VARCHAR(30) NULL COMMENT ''sale | payment_received | purchase | payment_made'' AFTER `credit`'
    )
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'customer_ledger_entries' AND COLUMN_NAME = 'source_id'
        ),
        'SELECT ''skip: customer_ledger_entries.source_id exists'' AS _msg',
        'ALTER TABLE `customer_ledger_entries` ADD COLUMN `source_id` INT UNSIGNED NULL COMMENT ''Originating model id'' AFTER `source_type`'
    )
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- Composite index used by payable payment sync/update/delete:
--   WHERE source_type = 'payment_made' AND source_id = ?
SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'customer_ledger_entries'
      AND INDEX_NAME = 'idx_source'
);
SET @sql := IF(
    @idx_exists > 0,
    'SELECT ''skip: idx_source exists'' AS _msg',
    'ALTER TABLE `customer_ledger_entries` ADD INDEX `idx_source` (`source_type`, `source_id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SELECT 'live_schema_payable_payment_parts.sql completed' AS result;
