-- =============================================================================
-- LIVE SITE — Opening balance → receivable row (idempotent MySQL / MariaDB)
-- =============================================================================
-- Purpose: allow a synthetic receivable per customer so the Receivables page
-- shows customers who have opening_balance but no sales.
--
-- Run after your main ERP schema is in place. Safe to run multiple times.
--
-- Laravel: deploy matching app code, then `php artisan migrate` OR run this
-- file manually if you do not use migrations on production.
-- =============================================================================

SET @db := DATABASE();

-- ---------------------------------------------------------------------------
-- customers.opening_balance (required for the feature; skip if already added)
-- ---------------------------------------------------------------------------
SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'opening_balance'
        ),
        'SELECT ''skip: customers.opening_balance exists'' AS _msg',
        'ALTER TABLE `customers` ADD COLUMN `opening_balance` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT ''AR opening; positive = owes'' AFTER `address`'
    )
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'opening_balance_date'
        ),
        'SELECT ''skip: customers.opening_balance_date exists'' AS _msg',
        'ALTER TABLE `customers` ADD COLUMN `opening_balance_date` DATE NULL COMMENT ''As-of for opening balance'' AFTER `opening_balance`'
    )
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- ---------------------------------------------------------------------------
-- receivables.is_opening_balance + index (marks synthetic opening row)
-- ---------------------------------------------------------------------------
SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'receivables' AND COLUMN_NAME = 'is_opening_balance'
        ),
        'SELECT ''skip: receivables.is_opening_balance exists'' AS _msg',
        'ALTER TABLE `receivables` ADD COLUMN `is_opening_balance` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''1 = from customer opening balance'' AFTER `received`'
    )
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'receivables'
      AND INDEX_NAME = 'receivables_customer_code_is_opening_balance_index'
);
SET @sql := IF(
    @idx_exists > 0,
    'SELECT ''skip: opening index exists'' AS _msg',
    'ALTER TABLE `receivables` ADD INDEX `receivables_customer_code_is_opening_balance_index` (`customer_code`, `is_opening_balance`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SELECT 'live_schema_opening_balance_receivable.sql completed' AS result;
