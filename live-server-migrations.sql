-- ============================================================
--  Laiba Safety ERP  –  Live Server Migration
--  Feature: Stock Additions
--  Date: 2026-06-08
--
--  Run this file once on your live MySQL database.
--  If your live server uses SQLite, see the SQLite section
--  at the bottom of this file.
-- ============================================================


-- ─────────────────────────────────────────────────────────────
--  MySQL  (use this for cPanel / shared hosting / VPS MySQL)
-- ─────────────────────────────────────────────────────────────

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

-- Tell Laravel this migration has already been run
INSERT INTO `migrations` (`migration`, `batch`)
SELECT
  '2026_06_07_000001_create_stock_additions_table',
  COALESCE((SELECT MAX(`batch`) FROM `migrations`), 0) + 1;


-- ─────────────────────────────────────────────────────────────
--  SQLite  (only if your live server uses SQLite)
--  Comment out the MySQL block above and uncomment this one.
-- ─────────────────────────────────────────────────────────────

-- CREATE TABLE IF NOT EXISTS "stock_additions" (
--   "id"         INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
--   "product_id" INTEGER  NOT NULL
--                REFERENCES "products"("id") ON DELETE CASCADE,
--   "date"       DATE     NOT NULL,
--   "quantity"   INTEGER  NOT NULL,
--   "unit_cost"  NUMERIC  DEFAULT NULL,
--   "total_cost" NUMERIC  DEFAULT NULL,
--   "reference"  VARCHAR  DEFAULT NULL,
--   "notes"      TEXT     DEFAULT NULL,
--   "created_at" DATETIME DEFAULT NULL,
--   "updated_at" DATETIME DEFAULT NULL
-- );
--
-- INSERT INTO "migrations" ("migration", "batch")
-- SELECT
--   '2026_06_07_000001_create_stock_additions_table',
--   COALESCE((SELECT MAX("batch") FROM "migrations"), 0) + 1;
