-- Matches migration: 2026_03_25_000001_create_bank_statement_entries_table.php
-- MySQL / MariaDB. For SQLite, use INTEGER PRIMARY KEY and REAL for amount if adapting manually.

CREATE TABLE `bank_statement_entries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `flow_type` VARCHAR(16) NOT NULL COMMENT 'inflow or outflow',
  `transaction_date` DATE NOT NULL,
  `company_name` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_statement_entries_flow_type_transaction_date_index` (`flow_type`, `transaction_date`),
  CONSTRAINT `bank_statement_entries_flow_type_chk` CHECK (`flow_type` IN ('inflow', 'outflow'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
