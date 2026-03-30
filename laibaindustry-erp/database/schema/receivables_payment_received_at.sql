-- Matches migration: 2026_03_31_000001_add_payment_received_at_to_receivables_table.php
-- MySQL / MariaDB — run manually on the live database (phpMyAdmin, CLI, etc.) if you do not use `php artisan migrate` there.
-- Run once. If `payment_received_at` already exists, skip the ALTER or you will get a duplicate column error.

ALTER TABLE `receivables`
  ADD COLUMN `payment_received_at` DATETIME NULL DEFAULT NULL AFTER `received`;

-- Backfill from customer ledger (last payment date per receivable) where money was already recorded
UPDATE `receivables` AS `r`
INNER JOIN (
  SELECT `source_id`, MAX(`date`) AS `last_payment_at`
  FROM `customer_ledger_entries`
  WHERE `source_type` = 'payment_received'
  GROUP BY `source_id`
) AS `cle` ON `cle`.`source_id` = `r`.`id`
SET `r`.`payment_received_at` = `cle`.`last_payment_at`
WHERE `r`.`received` > 0;
