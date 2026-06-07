-- ============================================================================
-- Live site patch — 2026-06-07
-- Fixes 500 errors caused by deploying new code before the database was updated
--
-- Run via phpMyAdmin → SQL tab, or via SSH:
--   mysql -u USER -p DATABASE < database/schema/live_site_patch_2026_06_07.sql
--
-- Sections:
--   1. customer_payable_sales_offsets  — safe, CREATE IF NOT EXISTS
--   2. quotations / quotation_items    — ⚠ drops old tables (no live data to keep)
--   3. expenses category migration     — safe, skips automatically if already done
-- ============================================================================


-- ============================================================================
-- PATCH 1: customer_payable_sales_offsets
-- Needed by: SaleController, PurchaseController, PayableController (write ops)
-- Safe — skipped automatically if table already exists.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `customer_payable_sales_offsets` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `sale_id`     BIGINT UNSIGNED NOT NULL,
  `payable_id`  BIGINT UNSIGNED NOT NULL,
  `amount`      DECIMAL(10,2)   NOT NULL,
  `offset_date` DATETIME        NOT NULL,
  `created_at`  TIMESTAMP NULL  DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL  DEFAULT NULL,
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

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_06_16_000001_create_customer_payable_sales_offsets_table',
       COALESCE((SELECT MAX(`batch`) FROM `migrations`), 0) + 1
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_06_16_000001_create_customer_payable_sales_offsets_table'
);


-- ============================================================================
-- PATCH 2: quotations + quotation_items rebuild
-- Needed by: QuotationController (all pages)
-- ⚠ WARNING: drops both tables and recreates with new schema.
--   All existing quotation records will be permanently deleted.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `quotation_items`;
DROP TABLE IF EXISTS `quotations`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `quotations` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quotation_number`     VARCHAR(40)     NOT NULL,
  `quotation_date`       DATE            NOT NULL,
  `expiration_date`      DATE            DEFAULT NULL,
  `salesperson`          VARCHAR(255)    DEFAULT NULL,
  `customer_name`        VARCHAR(255)    NOT NULL,
  `customer_vat_number`  VARCHAR(255)    DEFAULT NULL,
  `customer_cr_number`   VARCHAR(255)    DEFAULT NULL,
  `customer_phone`       VARCHAR(255)    DEFAULT NULL,
  `customer_email`       VARCHAR(255)    DEFAULT NULL,
  `customer_address`     TEXT,
  `untaxed_amount`       DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `vat_amount`           DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `total_amount`         DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `status`               ENUM('draft','sent','accepted','rejected','expired') NOT NULL DEFAULT 'draft',
  `notes`                TEXT,
  `created_at`           TIMESTAMP NULL  DEFAULT NULL,
  `updated_at`           TIMESTAMP NULL  DEFAULT NULL,
  `deleted_at`           TIMESTAMP NULL  DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotations_quotation_number_unique` (`quotation_number`),
  KEY `quotations_quotation_date_index` (`quotation_date`),
  KEY `quotations_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quotation_items` (
  `id`           BIGINT UNSIGNED       NOT NULL AUTO_INCREMENT,
  `quotation_id` BIGINT UNSIGNED       NOT NULL,
  `sort_order`   SMALLINT UNSIGNED     NOT NULL DEFAULT 0,
  `description`  TEXT                  NOT NULL,
  `quantity`     DECIMAL(12,3)         NOT NULL DEFAULT 1.000,
  `unit_price`   DECIMAL(12,2)         NOT NULL DEFAULT 0.00,
  `tax_rate`     DECIMAL(5,2)          NOT NULL DEFAULT 15.00,
  `tax_amount`   DECIMAL(12,2)         NOT NULL DEFAULT 0.00,
  `amount`       DECIMAL(12,2)         NOT NULL DEFAULT 0.00,
  `created_at`   TIMESTAMP NULL        DEFAULT NULL,
  `updated_at`   TIMESTAMP NULL        DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_items_quotation_id_sort_order_index` (`quotation_id`, `sort_order`),
  CONSTRAINT `quotation_items_quotation_id_foreign`
    FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT m.migration,
       COALESCE((SELECT MAX(`batch`) FROM `migrations`), 0) + 1
FROM (
    SELECT '2026_04_03_120000_create_quotations_tables'              AS migration UNION ALL
    SELECT '2026_04_03_140000_drop_expiration_date_from_quotations_table'         UNION ALL
    SELECT '2026_04_04_100000_add_customer_vat_to_quotations_table'               UNION ALL
    SELECT '2026_06_15_120000_rebuild_quotations_for_module'
) AS m
WHERE m.migration NOT IN (SELECT `migration` FROM `migrations`);


-- ============================================================================
-- PATCH 3: expenses — replace `type` with `category` + `description`
-- Needed by: ExpenseController (all pages)
-- Safe — uses a stored procedure to detect and skip if already applied.
-- ============================================================================

DROP PROCEDURE IF EXISTS `_patch_expenses_categories`;

DELIMITER $$
CREATE PROCEDURE `_patch_expenses_categories`()
BEGIN
    IF EXISTS (
        SELECT 1
        FROM   INFORMATION_SCHEMA.COLUMNS
        WHERE  TABLE_SCHEMA = DATABASE()
          AND  TABLE_NAME   = 'expenses'
          AND  COLUMN_NAME  = 'type'
    ) THEN
        ALTER TABLE `expenses`
            ADD COLUMN `category`    VARCHAR(255) NULL AFTER `date`,
            ADD COLUMN `description` VARCHAR(255) NULL AFTER `category`;

        UPDATE `expenses`
        SET    `description` = `type`,
               `category`    = 'personal'
        WHERE  `category` IS NULL;

        ALTER TABLE `expenses` DROP INDEX  `expenses_type_index`;
        ALTER TABLE `expenses` DROP COLUMN `type`;
        ALTER TABLE `expenses` ADD  INDEX  `expenses_category_index` (`category`);

        INSERT INTO `migrations` (`migration`, `batch`)
        SELECT '2026_06_06_000001_add_category_and_description_to_expenses_table',
               COALESCE((SELECT MAX(`batch`) FROM `migrations`), 0) + 1
        WHERE NOT EXISTS (
            SELECT 1 FROM `migrations`
            WHERE `migration` = '2026_06_06_000001_add_category_and_description_to_expenses_table'
        );

        SELECT 'Patch 3 (expenses): applied' AS status;
    ELSE
        SELECT 'Patch 3 (expenses): already applied — skipped' AS status;
    END IF;
END$$
DELIMITER ;

CALL `_patch_expenses_categories`();
DROP PROCEDURE IF EXISTS `_patch_expenses_categories`;
