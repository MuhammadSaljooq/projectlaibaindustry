-- =============================================================================
-- Live / manual MySQL: payments against international purchase ORDERS (invoices)
--
-- Requires `international_purchase_orders` table.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `international_payable_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `international_purchase_order_id` bigint unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `int_pay_pay_order_id_index` (`international_purchase_order_id`),
  KEY `int_pay_pay_payment_date_index` (`payment_date`),
  CONSTRAINT `int_pay_pay_order_id_foreign`
    FOREIGN KEY (`international_purchase_order_id`) REFERENCES `international_purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
