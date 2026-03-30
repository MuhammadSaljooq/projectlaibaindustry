-- Receivable combined (group) payments — run on live MySQL after backups if needed.
-- Matches Laravel migrations:
--   2026_03_31_120000_create_receivable_group_payments_table
--   2026_03_31_120001_create_receivable_group_payment_lines_table
--   2026_03_31_120002_add_receivable_group_payment_id_to_customer_ledger_entries

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

CREATE TABLE IF NOT EXISTS `receivable_group_payment_lines` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `receivable_group_payment_id` BIGINT UNSIGNED NOT NULL,
    `receivable_id` BIGINT UNSIGNED NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `customer_ledger_entry_id` INT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `receivable_group_payment_lines_receivable_id_index` (`receivable_id`),
    CONSTRAINT `receivable_group_payment_lines_receivable_group_payment_id_foreign`
        FOREIGN KEY (`receivable_group_payment_id`) REFERENCES `receivable_group_payments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `receivable_group_payment_lines_receivable_id_foreign`
        FOREIGN KEY (`receivable_id`) REFERENCES `receivables` (`id`) ON DELETE CASCADE,
    CONSTRAINT `receivable_group_payment_lines_customer_ledger_entry_id_foreign`
        FOREIGN KEY (`customer_ledger_entry_id`) REFERENCES `customer_ledger_entries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `customer_ledger_entries`
    ADD COLUMN `receivable_group_payment_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `source_id`,
    ADD KEY `customer_ledger_entries_receivable_group_payment_id_index` (`receivable_group_payment_id`),
    ADD CONSTRAINT `customer_ledger_entries_receivable_group_payment_id_foreign`
        FOREIGN KEY (`receivable_group_payment_id`) REFERENCES `receivable_group_payments` (`id`) ON DELETE SET NULL;
