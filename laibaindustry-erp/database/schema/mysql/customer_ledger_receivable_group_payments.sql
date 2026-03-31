-- =============================================================================
-- Live / manual MySQL: link customer ledger lines to combined receivable payments
-- (customer statement merges slices into one row using this table or the legacy
-- customer_ledger_entries.receivable_group_payment_id column).
--
-- Prerequisites: tables `customer_ledger_entries` and `receivable_group_payments`
-- must already exist (same as receivable group payment feature).
--
-- Run in phpMyAdmin / mysql CLI against your production database.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `customer_ledger_receivable_group_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_ledger_entry_id` int unsigned NOT NULL,
  `receivable_group_payment_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cl_rgp_ledger_unique` (`customer_ledger_entry_id`),
  KEY `cl_rgp_group_idx` (`receivable_group_payment_id`),
  CONSTRAINT `cl_rgp_ledger_fk` FOREIGN KEY (`customer_ledger_entry_id`) REFERENCES `customer_ledger_entries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cl_rgp_group_fk` FOREIGN KEY (`receivable_group_payment_id`) REFERENCES `receivable_group_payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Optional: if you already store receivable_group_payment_id on ledger rows,
-- copy into this table once (safe to re-run if no rows to insert).
-- Skip this block if that column does not exist on your server.
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
