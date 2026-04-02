-- =============================================================================
-- One-time MySQL upgrade: legacy international_purchases (header per row) →
-- international_purchase_orders + line items. Prefer `php artisan migrate` if
-- possible; use this only for hosts without Laravel migrations.
--
-- Run in order: international_purchase_orders.sql (if missing), then this
-- script, then replace international_payable_payments (or ALTER per below).
-- Backup your database first.
-- =============================================================================

-- 1) Create orders from each legacy line (1:1)
INSERT INTO `international_purchase_orders` (`supplier_id`, `date`, `invoice_number`, `total_amount`, `created_at`, `updated_at`)
SELECT `supplier_id`, `date`, NULL, `total_amount`, `created_at`, `updated_at`
FROM `international_purchases`
ORDER BY `id`;

-- 2) Add line FK column (run once)
-- ALTER TABLE `international_purchases` ADD COLUMN `international_purchase_order_id` bigint unsigned NULL AFTER `id`;

-- 3) Map lines to new orders (assumes same row order / sequential IDs — verify!)
-- Safer: use a temp table or application script. Example when old and new IDs align 1:1:
-- UPDATE `international_purchases` ip
-- INNER JOIN `international_purchase_orders` po ON po.id = ip.id
-- SET ip.international_purchase_order_id = po.id;
-- Adjust JOIN logic to match your actual id assignment from step 1.

-- 4) Drop old columns and enforce FK (after backfill and data validation)
-- ALTER TABLE `international_purchases` DROP FOREIGN KEY `international_purchases_supplier_id_foreign`;
-- ALTER TABLE `international_purchases` DROP COLUMN `supplier_id`, DROP COLUMN `date`;
-- ALTER TABLE `international_purchases` MODIFY `international_purchase_order_id` bigint unsigned NOT NULL;
-- ALTER TABLE `international_purchases` ADD CONSTRAINT `international_purchases_order_id_foreign`
--   FOREIGN KEY (`international_purchase_order_id`) REFERENCES `international_purchase_orders` (`id`) ON DELETE CASCADE;

-- 5) Payable payments: add international_purchase_order_id, populate from line, drop international_purchase_id
-- 6) supplier_ledger_entries: set source_type = 'international_purchase_order', source_id = order id, reference = CONCAT('IPO-', order_id)
--    where source_type = 'international_purchase' and source_id = old line id
