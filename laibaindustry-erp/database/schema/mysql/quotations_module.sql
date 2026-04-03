-- =============================================================================
-- Live / manual MySQL: quotations module (header + line items)
--
-- Matches Laravel models: App\Models\Quotation, App\Models\QuotationItem
-- Run on production (or any MySQL/MariaDB) instead of relying on migrations:
--   mysql -u USER -p DATABASE < database/schema/mysql/quotations_module.sql
--
-- If tables already exist with a different shape, back up data, DROP the old
-- tables, then run this file. `CREATE TABLE IF NOT EXISTS` will skip unchanged
-- installs but will NOT alter existing tables.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `quotations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quotation_number` varchar(40) NOT NULL,
  `quotation_date` date NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `salesperson` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_vat_number` varchar(255) DEFAULT NULL,
  `customer_cr_number` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_address` text,
  `untaxed_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vat_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','sent','accepted','rejected','expired') NOT NULL DEFAULT 'draft',
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotations_quotation_number_unique` (`quotation_number`),
  KEY `quotations_quotation_date_index` (`quotation_date`),
  KEY `quotations_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quotation_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quotation_id` bigint unsigned NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `quantity` decimal(12,3) NOT NULL DEFAULT 1.000,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 15.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_items_quotation_id_sort_order_index` (`quotation_id`,`sort_order`),
  CONSTRAINT `quotation_items_quotation_id_foreign`
    FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
