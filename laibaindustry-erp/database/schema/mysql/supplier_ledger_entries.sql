-- =============================================================================
-- Live / manual MySQL: supplier account ledger (international AP)
--
-- Mirrors customer_ledger_entries for suppliers: rows are posted from
-- international purchases (credit = amount owed) and international payable
-- payments (debit = amount paid).
--
-- Prerequisites: `suppliers` must exist. Run after suppliers + international
-- tables are in place (order does not depend on international_purchases FK).
-- No Laravel migration ships for this table.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `supplier_ledger_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint unsigned NOT NULL,
  `date` datetime NOT NULL,
  `description` varchar(255) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `debit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `source_type` varchar(40) DEFAULT NULL,
  `source_id` bigint unsigned DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_ledger_supplier_id_index` (`supplier_id`),
  KEY `supplier_ledger_date_index` (`date`),
  KEY `supplier_ledger_source_index` (`source_type`, `source_id`),
  CONSTRAINT `supplier_ledger_supplier_id_foreign`
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
