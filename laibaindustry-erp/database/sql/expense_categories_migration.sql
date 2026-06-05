-- =============================================================================
-- Expense categories migration (MySQL / MariaDB)
-- =============================================================================
-- Replaces free-text `type` with:
--   category    : personal | transport | container
--   description : optional note (migrated from old `type` values)
--
-- Run on your server AFTER backing up the database.
-- Example:
--   mysql -u YOUR_USER -p YOUR_DATABASE < database/sql/expense_categories_migration.sql
--
-- If you deploy with `php artisan migrate` instead, you do NOT need this file.
-- =============================================================================

START TRANSACTION;

-- 1) Add new columns
ALTER TABLE `expenses`
    ADD COLUMN `category` VARCHAR(255) NULL AFTER `date`,
    ADD COLUMN `description` VARCHAR(255) NULL AFTER `category`;

-- 2) Migrate existing data
UPDATE `expenses`
SET
    `description` = `type`,
    `category` = 'personal'
WHERE `category` IS NULL;

-- 3) Drop old type column and its index
-- Laravel default index name is expenses_type_index
ALTER TABLE `expenses`
    DROP INDEX `expenses_type_index`;

ALTER TABLE `expenses`
    DROP COLUMN `type`;

-- 4) Index category for filtering
ALTER TABLE `expenses`
    ADD INDEX `expenses_category_index` (`category`);

-- 5) Optional: record migration in Laravel (skip if you already ran artisan migrate)
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_06_06_000001_add_category_and_description_to_expenses_table', COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`
WHERE NOT EXISTS (
    SELECT 1
    FROM `migrations`
    WHERE `migration` = '2026_06_06_000001_add_category_and_description_to_expenses_table'
);

COMMIT;

-- =============================================================================
-- ROLLBACK (run only if you need to undo the change)
-- =============================================================================
-- START TRANSACTION;
--
-- ALTER TABLE `expenses`
--     ADD COLUMN `type` VARCHAR(255) NULL AFTER `date`;
--
-- UPDATE `expenses`
-- SET `type` = COALESCE(`description`, '');
--
-- ALTER TABLE `expenses`
--     DROP INDEX `expenses_category_index`;
--
-- ALTER TABLE `expenses`
--     DROP COLUMN `category`,
--     DROP COLUMN `description`;
--
-- ALTER TABLE `expenses`
--     ADD INDEX `expenses_type_index` (`type`);
--
-- DELETE FROM `migrations`
-- WHERE `migration` = '2026_06_06_000001_add_category_and_description_to_expenses_table';
--
-- COMMIT;
-- =============================================================================
