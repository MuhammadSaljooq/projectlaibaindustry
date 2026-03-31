-- =============================================================================
-- Live / manual MySQL: link international_purchases to suppliers (existing DBs)
--
-- Run AFTER suppliers.sql if `international_purchases` already exists without
-- `supplier_id`. Skip if you recreated international_purchases from the updated
-- international_purchases.sql that already includes supplier_id.
-- =============================================================================

ALTER TABLE `international_purchases`
  ADD COLUMN `supplier_id` bigint unsigned NULL DEFAULT NULL AFTER `id`,
  ADD KEY `international_purchases_supplier_id_index` (`supplier_id`),
  ADD CONSTRAINT `international_purchases_supplier_id_foreign`
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;
