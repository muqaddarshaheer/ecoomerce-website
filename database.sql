-- Paperlane shopping cart
-- Re-importing this file drops and recreates the database.

CREATE DATABASE IF NOT EXISTS paperlane_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE paperlane_shop;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS refunds;
DROP TABLE IF EXISTS return_requests;
DROP TABLE IF EXISTS dispatch_records;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS customer_addresses;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS faqs;
DROP TABLE IF EXISTS order_counters;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role ENUM('admin','employee','customer') NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  status ENUM('pending','active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role (role)
) ENGINE=InnoDB;

CREATE TABLE customers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_customers_user (user_id),
  CONSTRAINT fk_customers_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE customer_addresses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address_line VARCHAR(255) NOT NULL,
  city VARCHAR(80) NOT NULL,
  state VARCHAR(80) NOT NULL,
  postal_code VARCHAR(20) NOT NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_addresses_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE employees (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  employee_code VARCHAR(20) NOT NULL,
  designation VARCHAR(80) NOT NULL DEFAULT 'Dispatch executive',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_employees_user (user_id),
  UNIQUE KEY uq_employees_code (employee_code),
  CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  code_prefix CHAR(2) NOT NULL,
  description VARCHAR(500) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_categories_slug (slug),
  UNIQUE KEY uq_categories_prefix (code_prefix)
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_code CHAR(7) NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  warranty_info VARCHAR(255) NOT NULL DEFAULT 'No warranty',
  stock INT NOT NULL DEFAULT 0,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  UNIQUE KEY uq_products_code (product_code),
  KEY idx_products_category (category_id),
  KEY idx_products_price (price),
  KEY idx_products_status (status),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

CREATE TABLE product_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  KEY idx_images_product (product_id),
  CONSTRAINT fk_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  change_qty INT NOT NULL,
  balance_after INT NOT NULL,
  reason ENUM('opening','adjustment','order','cancel','return','replacement') NOT NULL,
  reference_type VARCHAR(30) DEFAULT NULL,
  reference_id INT UNSIGNED DEFAULT NULL,
  note VARCHAR(255) DEFAULT NULL,
  created_by INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_stock_product (product_id),
  KEY idx_stock_created (created_at),
  CONSTRAINT fk_stock_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_stock_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE carts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_carts_user (user_id),
  CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cart_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT NOT NULL,
  UNIQUE KEY uq_cart_product (cart_id, product_id),
  CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- One row is locked while an order number is issued, so two checkouts cannot get the same sequence.
CREATE TABLE order_counters (
  id TINYINT UNSIGNED PRIMARY KEY,
  last_seq INT UNSIGNED NOT NULL
) ENGINE=InnoDB;

INSERT INTO order_counters (id, last_seq) VALUES (1, 0);

-- order_number is 16 digits stored as text so leading zeros are kept:
-- 1 delivery digit (1 card, 2 cheque, 3 VPP) + 7-digit primary product code + 8-digit sequence.
-- The primary product is the lowest product_code in that order.
CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number CHAR(16) NOT NULL,
  customer_id INT UNSIGNED NOT NULL,
  delivery_type CHAR(1) NOT NULL,
  primary_product_code CHAR(7) NOT NULL,
  ship_name VARCHAR(100) NOT NULL,
  ship_phone VARCHAR(20) NOT NULL,
  ship_address VARCHAR(255) NOT NULL,
  ship_city VARCHAR(80) NOT NULL,
  ship_state VARCHAR(80) NOT NULL,
  ship_postal VARCHAR(20) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  shipping_fee DECIMAL(10,2) NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  order_status ENUM('pending','confirmed','dispatched','delivered','cancelled') NOT NULL DEFAULT 'pending',
  payment_status ENUM('pending','verified','cleared','failed','refunded') NOT NULL DEFAULT 'pending',
  dispatch_date DATETIME DEFAULT NULL,
  delivery_date DATETIME DEFAULT NULL,
  cancel_reason VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_orders_number (order_number),
  KEY idx_orders_customer (customer_id),
  KEY idx_orders_status (order_status),
  KEY idx_orders_created (created_at),
  KEY idx_orders_delivery (delivery_type),
  CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  product_code CHAR(7) NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  KEY idx_order_items_order (order_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  method ENUM('credit_card','cheque','vpp') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending','verified','cleared','failed','refunded') NOT NULL DEFAULT 'pending',
  card_last4 CHAR(4) DEFAULT NULL,
  card_brand VARCHAR(20) DEFAULT NULL,
  txn_reference VARCHAR(40) DEFAULT NULL,
  cheque_number VARCHAR(30) DEFAULT NULL,
  cheque_bank VARCHAR(100) DEFAULT NULL,
  cheque_date DATE DEFAULT NULL,
  verified_by INT UNSIGNED DEFAULT NULL,
  verified_at DATETIME DEFAULT NULL,
  notes VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_payments_order (order_id),
  KEY idx_payments_status (status),
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_payments_user FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE dispatch_records (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  employee_id INT UNSIGNED DEFAULT NULL,
  actor_user_id INT UNSIGNED NOT NULL,
  action ENUM('dispatched','delivered','delivery_failed') NOT NULL,
  notes VARCHAR(500) DEFAULT NULL,
  action_date DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_dispatch_order (order_id),
  KEY idx_dispatch_actor (actor_user_id),
  CONSTRAINT fk_dispatch_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_dispatch_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_dispatch_actor FOREIGN KEY (actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE return_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED NOT NULL,
  request_type ENUM('return','replacement') NOT NULL,
  reason TEXT NOT NULL,
  status ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  admin_notes VARCHAR(500) DEFAULT NULL,
  processed_by INT UNSIGNED DEFAULT NULL,
  requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  processed_at DATETIME DEFAULT NULL,
  KEY idx_returns_order (order_id),
  KEY idx_returns_status (status),
  CONSTRAINT fk_returns_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_returns_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
  CONSTRAINT fk_returns_admin FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE refunds (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  return_id INT UNSIGNED DEFAULT NULL,
  order_id INT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending','issued') NOT NULL DEFAULT 'pending',
  reason VARCHAR(255) NOT NULL,
  issued_by INT UNSIGNED DEFAULT NULL,
  issued_at DATETIME DEFAULT NULL,
  notes VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_refunds_order (order_id),
  KEY idx_refunds_status (status),
  CONSTRAINT fk_refunds_return FOREIGN KEY (return_id) REFERENCES return_requests(id),
  CONSTRAINT fk_refunds_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_refunds_user FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE feedback (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  subject VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  rating TINYINT UNSIGNED DEFAULT NULL,
  status ENUM('new','reviewed') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_feedback_status (status),
  CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE faqs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB;

-- Default administrator. Password: Admin@123  (change this after the first login)
INSERT INTO users (role, full_name, email, password_hash, phone, status)
VALUES (
  'admin',
  'Paperlane Admin',
  'admin@paperlane.local',
  '$2y$10$rJm6ulQ3LyRyjkzbyvdljuRduRMUkvbTyb/EWQtuEcnFZJCPCi57m',
  '03000000000',
  'active'
);

INSERT INTO categories (name, slug, code_prefix, description, image, status) VALUES
('Greeting Cards & Gifts', 'greeting-cards-gifts', '01', 'Cards, wraps and small gift boxes for birthdays, weddings and everyday notes.', 'cards.jpg', 'active'),
('Dolls, Files & Stationery', 'dolls-files-stationery', '02', 'Dolls, files, notebooks and pens for school, work and gifting.', 'stationery.jpg', 'active'),
('Handbags & Wallets', 'handbags-wallets', '03', 'Shoulder bags and wallets chosen for daily use.', 'bags.jpg', 'active'),
('Beauty & Accessories', 'beauty-accessories', '04', 'Necklaces, earrings, jhumkas, bracelets, watches, rings, perfume, makeup, skincare, shawls, ties, wallets, handbags and gift baskets.', 'beauty.jpg', 'active');

INSERT INTO faqs (question, answer, sort_order, status) VALUES
('Do I need an account to look at products?', 'No. Anyone can browse the shop, search and open a product page. An account is required only when you place an order, open your profile or view your own orders.', 1, 'active'),
('How is the order number formed?', 'Every order number has 16 digits. The first digit is the payment type (1 credit card, 2 cheque, 3 cash on delivery). The next 7 digits are the product ID of the lowest product code in that order. The last 8 digits are the shop sequence for that order.', 2, 'active'),
('Which payment methods can I use?', 'Credit card, cheque, and VPP (cash on delivery). The card page is a clearly labelled simulation for this project. It does not charge a real bank and it never stores the full card number or CVV. Cheque orders wait until a staff member verifies the cheque and marks it cleared. Cash is collected when a VPP order is delivered.', 3, 'active'),
('When will my order be dispatched?', 'Credit-card and cheque orders stay in the shop until payment is cleared. VPP orders can be dispatched before the cash is collected. You can follow each step from your order page.', 4, 'active'),
('Can I cancel an order?', 'Yes, but only before the order is dispatched. Open the order and use Cancel order. If the payment was already cleared, a refund is created for an administrator to issue. Stock is returned to the shelf when the cancellation succeeds.', 5, 'active'),
('How do returns and replacements work?', 'After delivery you have 7 days to request a return or a replacement. An administrator approves or rejects the request. A refund is created only after a return is approved and only if the payment had been cleared. The refund is marked issued by an administrator; it is not sent to a real bank in this project.', 6, 'active'),
('How much is delivery?', 'Delivery is Rs 99. It is free when the goods total is Rs 1,500 or more. The total is calculated on the server from the current database prices.', 7, 'active');
