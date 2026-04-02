-- =============================================================================
-- Live / manual MySQL: international purchase orders (invoice header)
-- Run after suppliers table exists.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `international_purchase_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `invoice_number` varchar(191) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `international_purchase_orders_supplier_id_invoice_number_index` (`supplier_id`,`invoice_number`),
  KEY `international_purchase_orders_date_index` (`date`),
  CONSTRAINT `international_purchase_orders_supplier_id_foreign`
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
