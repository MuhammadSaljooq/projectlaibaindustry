-- ============================================================
--  Stock Additions Table  –  Run once on live MySQL database
-- ============================================================

CREATE TABLE IF NOT EXISTS `stock_additions` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `date`       DATE            NOT NULL,
  `quantity`   INT UNSIGNED    NOT NULL,
  `unit_cost`  DECIMAL(12,2)   DEFAULT NULL,
  `total_cost` DECIMAL(12,2)   DEFAULT NULL,
  `reference`  VARCHAR(255)    DEFAULT NULL,
  `notes`      TEXT            DEFAULT NULL,
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_additions_product_id_index` (`product_id`),
  CONSTRAINT `stock_additions_product_id_foreign`
    FOREIGN KEY (`product_id`)
    REFERENCES `products` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT
  '2026_06_07_000001_create_stock_additions_table',
  COALESCE((SELECT MAX(`batch`) FROM `migrations`), 0) + 1;
