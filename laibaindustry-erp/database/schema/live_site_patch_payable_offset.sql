-- Live site SQL patch for payable sales-offset updates
-- Generated from local feature changes.
-- Run this on MySQL/MariaDB production database.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `customer_payable_sales_offsets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `sale_id` BIGINT UNSIGNED NOT NULL,
  `payable_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `offset_date` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_payable_sales_offsets_sale_payable_unique` (`sale_id`, `payable_id`),
  KEY `customer_payable_sales_offsets_customer_offset_index` (`customer_id`, `offset_date`),
  KEY `customer_payable_sales_offsets_payable_id_index` (`payable_id`),
  KEY `customer_payable_sales_offsets_sale_id_index` (`sale_id`),
  CONSTRAINT `customer_payable_sales_offsets_customer_id_foreign`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_payable_sales_offsets_sale_id_foreign`
    FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_payable_sales_offsets_payable_id_foreign`
    FOREIGN KEY (`payable_id`) REFERENCES `payables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
