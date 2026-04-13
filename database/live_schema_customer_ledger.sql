-- =============================================================================
-- LIVE SITE — Customer ledger + combined receivable payment linkage
-- =============================================================================
-- Purpose: align production with Laravel migrations for customer AR ledger:
--   • receivable_group_payments / receivable_group_payment_lines (FIFO group pay)
--   • customer_ledger_entries (per-customer statement lines: sale, payment, etc.)
--   • customer_ledger_entries.receivable_group_payment_id (legacy link column)
--   • customer_ledger_receivable_group_payments (junction: one ledger row ↔ group pay)
--
-- Prerequisites: `customers` exists (InnoDB recommended for FKs). `receivables` is
-- not referenced by FK here (lines use receivable_id without DB FK by design).
--
-- Safe to run multiple times. Run after backup. Deploy matching app code.
-- =============================================================================

SET @db := DATABASE();

-- ---------------------------------------------------------------------------
-- receivable_group_payments
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `receivable_group_payments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_key` VARCHAR(512) NOT NULL,
    `payment_date` DATE NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `receivable_group_payments_group_key_index` (`group_key`),
    KEY `receivable_group_payments_payment_date_index` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- receivable_group_payment_lines (no FK to receivables / ledger — app-enforced)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `receivable_group_payment_lines` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `receivable_group_payment_id` BIGINT UNSIGNED NOT NULL,
    `receivable_id` BIGINT UNSIGNED NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `customer_ledger_entry_id` INT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `rgpl_ar_group_pay_idx` (`receivable_group_payment_id`),
    KEY `rgpl_receivable_id_idx` (`receivable_id`),
    KEY `rgpl_cust_ledger_idx` (`customer_ledger_entry_id`),
    CONSTRAINT `rgpl_ar_group_pay_fk`
        FOREIGN KEY (`receivable_group_payment_id`) REFERENCES `receivable_group_payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- customer_ledger_entries (base table; receivable_group_payment_id added below)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customer_ledger_entries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` INT UNSIGNED NOT NULL,
    `date` DATETIME NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `reference` VARCHAR(100) NULL DEFAULT NULL,
    `debit` DECIMAL(10, 2) NOT NULL DEFAULT 0,
    `credit` DECIMAL(10, 2) NOT NULL DEFAULT 0,
    `source_type` VARCHAR(30) NULL DEFAULT NULL,
    `source_id` INT UNSIGNED NULL DEFAULT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `customer_ledger_entries_customer_id_index` (`customer_id`),
    KEY `customer_ledger_entries_date_index` (`date`),
    KEY `customer_ledger_entries_source_type_source_id_index` (`source_type`, `source_id`),
    CONSTRAINT `customer_ledger_entries_customer_id_foreign`
        FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- If CREATE failed earlier because customers was missing, fix that first; do not
-- strip the FK above without understanding your data.

-- ---------------------------------------------------------------------------
-- customer_ledger_entries.receivable_group_payment_id + index + FK
-- ---------------------------------------------------------------------------
SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'customer_ledger_entries'
              AND COLUMN_NAME = 'receivable_group_payment_id'
        ),
        'SELECT ''skip: customer_ledger_entries.receivable_group_payment_id exists'' AS _msg',
        'ALTER TABLE `customer_ledger_entries` ADD COLUMN `receivable_group_payment_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `source_id`'
    )
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'customer_ledger_entries'
      AND INDEX_NAME = 'cle_ar_group_pay_idx'
);
SET @sql := IF(
    @idx_exists > 0,
    'SELECT ''skip: cle_ar_group_pay_idx exists'' AS _msg',
    'ALTER TABLE `customer_ledger_entries` ADD INDEX `cle_ar_group_pay_idx` (`receivable_group_payment_id`)'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @fk_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'customer_ledger_entries'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = 'cle_ar_group_pay_fk'
);
SET @sql := IF(
    @fk_exists > 0,
    'SELECT ''skip: cle_ar_group_pay_fk exists'' AS _msg',
    'ALTER TABLE `customer_ledger_entries` ADD CONSTRAINT `cle_ar_group_pay_fk` FOREIGN KEY (`receivable_group_payment_id`) REFERENCES `receivable_group_payments` (`id`) ON DELETE SET NULL'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- If the FK ALTER fails (e.g. customer_ledger_entries not InnoDB), add the column
-- and index only, then: ALTER TABLE customer_ledger_entries ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- customer_ledger_receivable_group_payments (preferred link for merged statement rows)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customer_ledger_receivable_group_payments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_ledger_entry_id` INT UNSIGNED NOT NULL,
    `receivable_group_payment_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `cl_rgp_ledger_unique` (`customer_ledger_entry_id`),
    KEY `cl_rgp_group_idx` (`receivable_group_payment_id`),
    CONSTRAINT `cl_rgp_ledger_fk` FOREIGN KEY (`customer_ledger_entry_id`) REFERENCES `customer_ledger_entries` (`id`) ON DELETE CASCADE,
    CONSTRAINT `cl_rgp_group_fk` FOREIGN KEY (`receivable_group_payment_id`) REFERENCES `receivable_group_payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Optional one-time backfill: copy legacy column into junction table (uncomment).
-- -----------------------------------------------------------------------------
-- INSERT INTO `customer_ledger_receivable_group_payments`
--   (`customer_ledger_entry_id`, `receivable_group_payment_id`, `created_at`, `updated_at`)
-- SELECT `id`, `receivable_group_payment_id`, NOW(), NOW()
-- FROM `customer_ledger_entries`
-- WHERE `receivable_group_payment_id` IS NOT NULL
--   AND NOT EXISTS (
--     SELECT 1 FROM `customer_ledger_receivable_group_payments` l
--     WHERE l.`customer_ledger_entry_id` = `customer_ledger_entries`.`id`
--   );

SELECT 'live_schema_customer_ledger.sql completed' AS result;
