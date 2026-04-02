-- =============================================================================
-- LEGACY: pre–invoice-header schema. New installs use international_purchase_orders
-- + line items (see international_purchase_orders.sql, international_purchases.sql).
-- Prefer `php artisan migrate` (2026_04_02_120000_...) for upgrades.
-- =============================================================================

ALTER TABLE `international_purchases`
  ADD COLUMN `supplier_id` bigint unsigned NULL DEFAULT NULL AFTER `id`,
  ADD KEY `international_purchases_supplier_id_index` (`supplier_id`),
  ADD CONSTRAINT `international_purchases_supplier_id_foreign`
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;
