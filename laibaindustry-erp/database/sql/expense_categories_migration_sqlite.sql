-- =============================================================================
-- Expense categories migration (SQLite)
-- =============================================================================
-- Use this file if your server database is SQLite.
-- SQLite does not support DROP COLUMN in older versions; this script rebuilds
-- the table (safe for the small expenses table).
--
-- Example:
--   sqlite3 /path/to/database.sqlite < database/sql/expense_categories_migration_sqlite.sql
-- =============================================================================

PRAGMA foreign_keys = OFF;

BEGIN TRANSACTION;

CREATE TABLE `expenses_new` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    `date` DATE NOT NULL,
    `category` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NULL,
    `amount` NUMERIC(10, 2) NOT NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL
);

INSERT INTO `expenses_new` (`id`, `date`, `category`, `description`, `amount`, `created_at`, `updated_at`)
SELECT
    `id`,
    `date`,
    'personal',
    `type`,
    `amount`,
    `created_at`,
    `updated_at`
FROM `expenses`;

DROP TABLE `expenses`;

ALTER TABLE `expenses_new` RENAME TO `expenses`;

CREATE INDEX `expenses_date_index` ON `expenses` (`date`);
CREATE INDEX `expenses_category_index` ON `expenses` (`category`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_06_06_000001_add_category_and_description_to_expenses_table', COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`
WHERE NOT EXISTS (
    SELECT 1
    FROM `migrations`
    WHERE `migration` = '2026_06_06_000001_add_category_and_description_to_expenses_table'
);

COMMIT;

PRAGMA foreign_keys = ON;
