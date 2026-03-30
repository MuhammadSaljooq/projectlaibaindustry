-- Read-only reporting view: one row per receivable customer group (matches app logic in App\Models\Receivable::groupKeySqlExpression() for MySQL).
-- Run on live MySQL/MariaDB if useful for phpMyAdmin or BI tools. Not required for the Laravel UI.

CREATE OR REPLACE VIEW `customer_receivable_groups` AS
SELECT
  (CASE
    WHEN TRIM(COALESCE(`customer_code`, '')) != '' THEN CONCAT('code:', TRIM(`customer_code`))
    WHEN TRIM(COALESCE(`customer_name`, '')) != '' THEN CONCAT('name:', LOWER(TRIM(`customer_name`)))
    ELSE CONCAT('id:', `id`)
  END) AS `ar_group_key`,
  MAX(`customer_name`) AS `agg_customer_name`,
  MAX(TRIM(COALESCE(`customer_code`, ''))) AS `agg_customer_code`,
  COUNT(*) AS `invoice_count`,
  SUM(`amount`) AS `total_amount`,
  SUM(`received`) AS `total_received`,
  MAX(`date`) AS `latest_invoice_date`,
  MAX(`payment_received_at`) AS `latest_payment_at`
FROM `receivables`
GROUP BY
  (CASE
    WHEN TRIM(COALESCE(`customer_code`, '')) != '' THEN CONCAT('code:', TRIM(`customer_code`))
    WHEN TRIM(COALESCE(`customer_name`, '')) != '' THEN CONCAT('name:', LOWER(TRIM(`customer_name`)))
    ELSE CONCAT('id:', `id`)
  END);
