-- =============================================================================
-- Live / manual MySQL: suppliers / vendors (master list for international sourcing)
--
-- Run in phpMyAdmin or mysql CLI once per environment before linking
-- international_purchases.supplier_id (see international_purchases_add_supplier_id.sql
-- if the purchases table already exists without this FK).
-- No Laravel migration ships for this table.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `country` varchar(128) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suppliers_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
