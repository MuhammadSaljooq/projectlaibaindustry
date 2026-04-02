-- =============================================================================
-- Live / manual MySQL: international purchase LINE items (per invoice/order)
--
-- Requires `international_purchase_orders` (see international_purchase_orders.sql).
-- For upgrading an old DB that still has supplier_id/date on this table, run
-- international_purchases_migrate_to_orders.sql or use Laravel migrations.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `international_purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `international_purchase_order_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `international_purchases_order_id_index` (`international_purchase_order_id`),
  KEY `international_purchases_product_name_index` (`product_name`),
  CONSTRAINT `international_purchases_order_id_foreign`
    FOREIGN KEY (`international_purchase_order_id`) REFERENCES `international_purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
