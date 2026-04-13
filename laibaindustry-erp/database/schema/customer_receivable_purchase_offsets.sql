-- Matches migration: 2026_04_13_120000_create_customer_receivable_purchase_offsets_table.php
-- MySQL / MariaDB — run manually on live DB if you do not run `php artisan migrate` there.
-- Safe to run once. If table already exists, this script will skip creation.

CREATE TABLE IF NOT EXISTS `customer_receivable_purchase_offsets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `purchase_id` BIGINT UNSIGNED NOT NULL,
  `receivable_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `offset_date` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `crpo_purchase_receivable_unique` (`purchase_id`, `receivable_id`),
  KEY `crpo_customer_offset_date_index` (`customer_id`, `offset_date`),
  KEY `crpo_receivable_id_index` (`receivable_id`),
  CONSTRAINT `crpo_customer_fk`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `crpo_purchase_fk`
    FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `crpo_receivable_fk`
    FOREIGN KEY (`receivable_id`) REFERENCES `receivables` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
