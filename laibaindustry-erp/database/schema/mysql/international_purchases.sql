-- =============================================================================
-- Live / manual MySQL: international purchases (separate from domestic purchases)
--
-- Run in phpMyAdmin or mysql CLI against your database after deploy.
-- No Laravel migration ships for this table; local dev may import this file or
-- rely on schema helpers for PHPUnit (SQLite).
--
-- Requires `suppliers` table (see suppliers.sql) for the optional supplier_id FK.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `international_purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `international_purchases_supplier_id_index` (`supplier_id`),
  KEY `international_purchases_date_index` (`date`),
  KEY `international_purchases_product_name_index` (`product_name`),
  CONSTRAINT `international_purchases_supplier_id_foreign`
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
