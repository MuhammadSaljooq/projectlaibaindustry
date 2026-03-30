-- Receivable combined (group) payments — run on live MySQL after backups if needed.
-- Matches Laravel migrations:
--   2026_03_31_120000_create_receivable_group_payments_table
--   2026_03_31_120001_create_receivable_group_payment_lines_table
--   2026_03_31_120002_add_receivable_group_payment_id_to_customer_ledger_entries
--
-- receivable_group_payment_lines: only FK is to receivable_group_payments (same batch, InnoDB, BIGINT).
-- No FK to receivables / customer_ledger_entries — avoids errno 150 when live `receivables.id` is INT,
-- table is MyISAM, or ledger id type differs. Use indexes below; the app maintains consistency.

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
    KEY `rgpl_ar_group_pay_idx` (`receivable_group_payment_id`),
    KEY `rgpl_receivable_id_idx` (`receivable_id`),
    KEY `rgpl_cust_ledger_idx` (`customer_ledger_entry_id`),
    CONSTRAINT `rgpl_ar_group_pay_fk`
        FOREIGN KEY (`receivable_group_payment_id`) REFERENCES `receivable_group_payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `customer_ledger_entries`
    ADD COLUMN `receivable_group_payment_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `source_id`,
    ADD KEY `cle_ar_group_pay_idx` (`receivable_group_payment_id`),
    ADD CONSTRAINT `cle_ar_group_pay_fk`
        FOREIGN KEY (`receivable_group_payment_id`) REFERENCES `receivable_group_payments` (`id`) ON DELETE SET NULL;

-- If the ALTER above fails (e.g. customer_ledger_entries is not InnoDB), run only:
--   ALTER TABLE `customer_ledger_entries` ADD COLUMN `receivable_group_payment_id` BIGINT UNSIGNED NULL ...;
--   ALTER TABLE `customer_ledger_entries` ADD KEY `cle_ar_group_pay_idx` (`receivable_group_payment_id`);
-- then convert the table to InnoDB if you want FKs: ALTER TABLE customer_ledger_entries ENGINE=InnoDB;
