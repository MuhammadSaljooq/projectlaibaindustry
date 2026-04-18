-- =============================================================================
-- CUSTOMER STATEMENT & ACCOUNT LEDGER — Live Database Schema
-- =============================================================================
-- Copy and run this entire script in phpMyAdmin / TablePlus / MySQL Workbench.
--
-- SAFE TO RUN ON AN EXISTING DATABASE:
--   • ALTER TABLE statements skip columns that already exist
--   • CREATE TABLE statements skip tables that already exist
--   • No data is deleted or modified
--
-- Run ORDER matters — do not rearrange the sections.
-- Requires MySQL 8.0+ or MariaDB 10.3+
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. customers
--    Add opening balance fields (skip if already present)
-- -----------------------------------------------------------------------------

ALTER TABLE `customers`
  ADD COLUMN IF NOT EXISTS `opening_balance`      DECIMAL(10,2) NOT NULL DEFAULT 0.00
    AFTER `address`,
  ADD COLUMN IF NOT EXISTS `opening_balance_date` DATE NULL
    AFTER `opening_balance`;

-- -----------------------------------------------------------------------------
-- 2. receivables
--    Add flag to identify synthetic opening-balance rows
-- -----------------------------------------------------------------------------

ALTER TABLE `receivables`
  ADD COLUMN IF NOT EXISTS `is_opening_balance` TINYINT(1) NOT NULL DEFAULT 0
    AFTER `invoice_number`;

CREATE INDEX IF NOT EXISTS `rec_cust_opening_idx`
  ON `receivables` (`customer_code`, `is_opening_balance`);

-- -----------------------------------------------------------------------------
-- 3. customer_ledger_entries
--    Core ledger table — one row per transaction line
--
--    source_type values:
--      'sale'             → sales invoice posted to customer
--      'purchase'         → purchase invoice posted against customer
--      'payment_received' → cash/payment collected from customer
--      'payment_made'     → payment made on customer's behalf
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `customer_ledger_entries` (
  `id`                          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `customer_id`                 INT UNSIGNED    NOT NULL,
  `date`                        DATETIME        NOT NULL,
  `description`                 VARCHAR(255)    NOT NULL,
  `reference`                   VARCHAR(100)    NULL DEFAULT NULL,
  `debit`                       DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  `credit`                      DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  `source_type`                 VARCHAR(30)     NULL DEFAULT NULL,
  `source_id`                   INT UNSIGNED    NULL DEFAULT NULL,
  `receivable_group_payment_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `payable_group_payment_id`    BIGINT UNSIGNED NULL DEFAULT NULL,
  `notes`                       TEXT            NULL DEFAULT NULL,
  `created_at`                  TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`                  TIMESTAMP       NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `cle_customer_idx`     (`customer_id`),
  KEY `cle_date_idx`         (`date`),
  KEY `cle_source_idx`       (`source_type`, `source_id`),
  KEY `cle_ar_group_pay_idx` (`receivable_group_payment_id`),
  KEY `cle_ap_group_pay_idx` (`payable_group_payment_id`),

  CONSTRAINT `cle_customer_fk`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
    ON DELETE CASCADE,

  CONSTRAINT `cle_ar_group_pay_fk`
    FOREIGN KEY (`receivable_group_payment_id`) REFERENCES `receivable_group_payments` (`id`)
    ON DELETE SET NULL,

  CONSTRAINT `cle_ap_group_pay_fk`
    FOREIGN KEY (`payable_group_payment_id`) REFERENCES `payable_group_payments` (`id`)
    ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add group payment columns to existing table if it was created before these were added
ALTER TABLE `customer_ledger_entries`
  ADD COLUMN IF NOT EXISTS `receivable_group_payment_id` BIGINT UNSIGNED NULL DEFAULT NULL
    AFTER `source_id`,
  ADD COLUMN IF NOT EXISTS `payable_group_payment_id` BIGINT UNSIGNED NULL DEFAULT NULL
    AFTER `receivable_group_payment_id`;

CREATE INDEX IF NOT EXISTS `cle_ar_group_pay_idx`
  ON `customer_ledger_entries` (`receivable_group_payment_id`);

CREATE INDEX IF NOT EXISTS `cle_ap_group_pay_idx`
  ON `customer_ledger_entries` (`payable_group_payment_id`);

-- -----------------------------------------------------------------------------
-- 4. customer_ledger_receivable_group_payments
--    Links a ledger entry to an AR group payment so combined payments
--    appear as a single line on the customer statement
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `customer_ledger_receivable_group_payments` (
  `id`                          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_ledger_entry_id`    INT UNSIGNED    NOT NULL,
  `receivable_group_payment_id` BIGINT UNSIGNED NOT NULL,
  `created_at`                  TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`                  TIMESTAMP       NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `clrgp_ledger_unique` (`customer_ledger_entry_id`),
  KEY        `clrgp_group_idx`     (`receivable_group_payment_id`),

  CONSTRAINT `clrgp_ledger_fk`
    FOREIGN KEY (`customer_ledger_entry_id`) REFERENCES `customer_ledger_entries` (`id`)
    ON DELETE CASCADE,

  CONSTRAINT `clrgp_group_fk`
    FOREIGN KEY (`receivable_group_payment_id`) REFERENCES `receivable_group_payments` (`id`)
    ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. customer_ledger_payable_group_payments
--    Links a ledger entry to an AP group payment
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `customer_ledger_payable_group_payments` (
  `id`                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_ledger_entry_id` INT UNSIGNED    NOT NULL,
  `payable_group_payment_id` BIGINT UNSIGNED NOT NULL,
  `created_at`               TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`               TIMESTAMP       NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `clpgp_unique`     (`customer_ledger_entry_id`, `payable_group_payment_id`),
  KEY        `clpgp_group_idx`  (`payable_group_payment_id`),
  KEY        `clpgp_ledger_idx` (`customer_ledger_entry_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. customer_receivable_purchase_offsets
--    When a purchase is posted for a customer who has an outstanding receivable,
--    the net offset is stored here so the AR balance reduces correctly.
--    One purchase can only offset a given receivable once.
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `customer_receivable_purchase_offsets` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id`   BIGINT UNSIGNED NOT NULL,
  `purchase_id`   BIGINT UNSIGNED NOT NULL,
  `receivable_id` BIGINT UNSIGNED NOT NULL,
  `amount`        DECIMAL(10,2)   NOT NULL,
  `offset_date`   DATETIME        NOT NULL,
  `created_at`    TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP       NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `crpo_purchase_receivable_unique` (`purchase_id`, `receivable_id`),
  KEY        `crpo_customer_date_idx`          (`customer_id`, `offset_date`),
  KEY        `crpo_receivable_idx`             (`receivable_id`),

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

-- =============================================================================
SET FOREIGN_KEY_CHECKS = 1;
-- =============================================================================
