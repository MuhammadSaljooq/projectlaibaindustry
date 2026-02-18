-- Inventory and Sales Management System - MySQL Schema
-- Created: January 2025

SET FOREIGN_KEY_CHECKS = 0;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Currencies Table
CREATE TABLE IF NOT EXISTS currencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE COMMENT 'ISO 4217 code (USD, EUR, GBP, etc.)',
    name VARCHAR(255) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    decimal_places INT DEFAULT 2,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exchange Rates Table
CREATE TABLE IF NOT EXISTS exchange_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_currency_id INT NOT NULL,
    to_currency_id INT NOT NULL,
    rate DECIMAL(10, 6) NOT NULL COMMENT 'Exchange rate',
    effective_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (from_currency_id) REFERENCES currencies(id) ON DELETE CASCADE,
    FOREIGN KEY (to_currency_id) REFERENCES currencies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rate (from_currency_id, to_currency_id, effective_date),
    INDEX idx_from_currency (from_currency_id),
    INDEX idx_to_currency (to_currency_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NOT NULL UNIQUE,
    category_id INT NOT NULL,
    cost_price DECIMAL(10, 2) NOT NULL,
    selling_price DECIMAL(10, 2) NULL,
    currency_id INT NULL COMMENT 'Product base currency',
    description TEXT,
    stock_quantity INT DEFAULT 0,
    reorder_level INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE SET NULL,
    INDEX idx_name (name),
    INDEX idx_sku (sku),
    INDEX idx_category (category_id),
    INDEX idx_stock (stock_quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tax Settings Table
CREATE TABLE IF NOT EXISTS tax_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    default_rate DECIMAL(5, 2) DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Table
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    customer_code VARCHAR(100) NULL,
    customer_name VARCHAR(255) NULL,
    invoice_number VARCHAR(100) NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    tax_rate DECIMAL(5, 2) NOT NULL,
    currency_id INT NULL COMMENT 'Sale currency',
    exchange_rate DECIMAL(10, 6) NULL COMMENT 'Rate used at time of sale',
    status VARCHAR(50) DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE SET NULL,
    INDEX idx_date (date),
    INDEX idx_customer_code (customer_code),
    INDEX idx_customer_name (customer_name),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_sales_date_customer (date, customer_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Items Table
CREATE TABLE IF NOT EXISTS sales_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    cost_price DECIMAL(10, 2) NOT NULL,
    selling_price DECIMAL(10, 2) NOT NULL,
    profit DECIMAL(10, 2) NOT NULL,
    tax_applied DECIMAL(10, 2) DEFAULT 0,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_sale (sale_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Receivables Table
CREATE TABLE IF NOT EXISTS receivables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    invoice_number VARCHAR(100) NULL,
    customer_name VARCHAR(255) NULL,
    customer_code VARCHAR(100) NULL,
    amount DECIMAL(10, 2) NOT NULL,
    received DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_customer_name (customer_name),
    INDEX idx_customer_code (customer_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchases Table (Purchase Entry header)
CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    customer_code VARCHAR(100) NULL,
    customer_name VARCHAR(255) NULL,
    invoice_number VARCHAR(100) NULL,
    subtotal DECIMAL(10, 2) DEFAULT 0,
    vat_amount DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) DEFAULT 0,
    currency_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE SET NULL,
    INDEX idx_date (date),
    INDEX idx_customer_code (customer_code),
    INDEX idx_invoice_number (invoice_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Items Table
CREATE TABLE IF NOT EXISTS purchase_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    product_name VARCHAR(255) NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 0,
    amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    vat_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
    INDEX idx_purchase (purchase_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users Table (IAM)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'viewer') NOT NULL DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(100) NOT NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_code (customer_code),
    INDEX idx_customer_name (customer_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payables Table
CREATE TABLE IF NOT EXISTS payables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    invoice_number VARCHAR(100) NULL,
    customer_name VARCHAR(255) NULL,
    customer_code VARCHAR(100) NULL,
    amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    received_date DATE NULL,
    remaining_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_customer_name (customer_name),
    INDEX idx_customer_code (customer_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Insert default category
INSERT INTO categories (name, description) VALUES ('General', 'Default category for auto-created products') 
ON DUPLICATE KEY UPDATE name=name;

-- Insert default tax setting
INSERT INTO tax_settings (default_rate, description) VALUES (15.00, 'Default tax rate') 
ON DUPLICATE KEY UPDATE default_rate=default_rate;

-- Insert default currencies (USD, EUR, GBP)
INSERT INTO currencies (code, name, symbol, is_default, is_active) VALUES 
('USD', 'US Dollar', '$', TRUE, TRUE),
('EUR', 'Euro', '€', FALSE, TRUE),
('GBP', 'British Pound', '£', FALSE, TRUE)
ON DUPLICATE KEY UPDATE code=code;

-- Insert default admin user (email: admin@example.com, password: admin123 - change in production)
INSERT INTO users (email, password_hash, name, role) VALUES 
('admin@example.com', '$2y$12$fqt0tvYgS5n15OyqPFclgeW6LOhaRBwsMvENCYjzeNSEzzL391Fe.', 'Admin', 'admin')
ON DUPLICATE KEY UPDATE email=email;