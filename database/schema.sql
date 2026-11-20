-- Nano Kids Wholesale schema + seed
-- Import via setup.php or: mysql -u USER -p DATABASE < database/schema.sql
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS quote_items;
DROP TABLE IF EXISTS quotes;
DROP TABLE IF EXISTS private_label_requests;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS price_tiers;
DROP TABLE IF EXISTS product_colors;
DROP TABLE IF EXISTS product_sizes;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS age_groups;
DROP TABLE IF EXISTS settings;

CREATE TABLE settings (
  setting_key VARCHAR(80) NOT NULL,
  setting_value LONGTEXT NULL,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL UNIQUE,
  label VARCHAR(120) NOT NULL,
  image_url VARCHAR(500) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE age_groups (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL UNIQUE,
  label VARCHAR(120) NOT NULL,
  range_label VARCHAR(80) NULL,
  image_url VARCHAR(500) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(40) NOT NULL UNIQUE,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  category_id INT UNSIGNED NOT NULL,
  age_group_id INT UNSIGNED NOT NULL,
  season VARCHAR(40) NOT NULL DEFAULT 'core',
  fabric VARCHAR(120) NULL,
  gsm INT UNSIGNED NULL,
  moq INT UNSIGNED NOT NULL DEFAULT 12,
  base_price DECIMAL(10,2) NOT NULL,
  lead_time_days INT UNSIGNED NOT NULL DEFAULT 7,
  stock_status ENUM('in_stock','limited','made_to_order') NOT NULL DEFAULT 'in_stock',
  description TEXT NULL,
  tag VARCHAR(40) NULL,
  pack_units INT UNSIGNED NULL,
  pack_ratio VARCHAR(80) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_cat FOREIGN KEY (category_id) REFERENCES categories(id),
  CONSTRAINT fk_products_age FOREIGN KEY (age_group_id) REFERENCES age_groups(id),
  INDEX idx_products_active (is_active, category_id, age_group_id),
  INDEX idx_products_price (base_price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  url VARCHAR(600) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_pimg_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_sizes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  size_label VARCHAR(40) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_psize_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_colors (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  color_key VARCHAR(40) NOT NULL,
  color_name VARCHAR(80) NOT NULL,
  hex VARCHAR(16) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_pcolor_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE price_tiers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  min_qty INT UNSIGNED NOT NULL,
  max_qty INT UNSIGNED NULL,
  discount_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_ptier_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_tier_qty (product_id, min_qty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role ENUM('admin','dealer') NOT NULL DEFAULT 'dealer',
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(160) NOT NULL,
  business_name VARCHAR(190) NULL,
  phone VARCHAR(40) NULL,
  city VARCHAR(80) NULL,
  ntn VARCHAR(40) NULL,
  shop_type VARCHAR(80) NULL,
  monthly_volume VARCHAR(80) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  application_id INT UNSIGNED NULL,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_name VARCHAR(190) NOT NULL,
  contact_name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  city VARCHAR(80) NOT NULL,
  ntn VARCHAR(40) NOT NULL,
  shop_type VARCHAR(80) NOT NULL,
  monthly_volume VARCHAR(80) NOT NULL,
  notes TEXT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  reviewed_at DATETIME NULL,
  reviewed_by INT UNSIGNED NULL,
  user_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_app_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quotes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(24) NOT NULL UNIQUE,
  user_id INT UNSIGNED NULL,
  status ENUM('submitted','reviewed','quoted','accepted','declined','converted') NOT NULL DEFAULT 'submitted',
  business_name VARCHAR(190) NOT NULL,
  contact_name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  city VARCHAR(80) NOT NULL,
  ntn VARCHAR(40) NULL,
  notes TEXT NULL,
  pieces INT UNSIGNED NOT NULL DEFAULT 0,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_quotes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_quotes_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quote_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quote_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  sku VARCHAR(40) NULL,
  name VARCHAR(190) NOT NULL,
  color VARCHAR(80) NULL,
  sizes_json TEXT NULL,
  qty INT UNSIGNED NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  discount_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
  line_total DECIMAL(12,2) NOT NULL,
  moq INT UNSIGNED NOT NULL DEFAULT 0,
  is_custom TINYINT(1) NOT NULL DEFAULT 0,
  custom_meta TEXT NULL,
  CONSTRAINT fk_qitem_quote FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE,
  CONSTRAINT fk_qitem_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(24) NOT NULL UNIQUE,
  quote_id INT UNSIGNED NULL,
  user_id INT UNSIGNED NULL,
  status ENUM('pending','confirmed','in_packing','dispatched','delivered','cancelled') NOT NULL DEFAULT 'pending',
  pieces INT UNSIGNED NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_quote FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE SET NULL,
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_orders_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  sku VARCHAR(40) NULL,
  name VARCHAR(190) NOT NULL,
  color VARCHAR(80) NULL,
  sizes_json TEXT NULL,
  qty INT UNSIGNED NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  discount_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
  line_total DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_oitem_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE private_label_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  shop_name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(40) NULL,
  garment VARCHAR(40) NOT NULL,
  mark VARCHAR(40) NOT NULL,
  label_type VARCHAR(40) NOT NULL,
  pack VARCHAR(40) NOT NULL,
  color VARCHAR(40) NULL,
  qty INT UNSIGNED NOT NULL,
  notes TEXT NULL,
  status ENUM('new','reviewing','quoted','accepted','declined') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pl_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_name VARCHAR(190) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


-- Seed data

INSERT INTO age_groups (id, slug, label, range_label, image_url, sort_order) VALUES (1, 'newborn', 'Newborn', '0 – 12 months', 'https://images.pexels.com/photos/16567792/pexels-photo-16567792.jpeg?auto=compress&cs=tinysrgb&w=800', 1);
INSERT INTO age_groups (id, slug, label, range_label, image_url, sort_order) VALUES (2, 'baby', 'Baby', '1 – 3 years', 'https://images.pexels.com/photos/18820092/pexels-photo-18820092.jpeg?auto=compress&cs=tinysrgb&w=800', 2);
INSERT INTO age_groups (id, slug, label, range_label, image_url, sort_order) VALUES (3, 'kids', 'Kids', '4 – 8 years', 'https://images.pexels.com/photos/28506036/pexels-photo-28506036.jpeg?auto=compress&cs=tinysrgb&w=800', 3);
INSERT INTO age_groups (id, slug, label, range_label, image_url, sort_order) VALUES (4, 'junior', 'Junior', '9 – 14 years', 'https://images.pexels.com/photos/31703288/pexels-photo-31703288.jpeg?auto=compress&cs=tinysrgb&w=800', 4);

INSERT INTO categories (id, slug, label, image_url, sort_order) VALUES (1, 'dresses', 'Dresses & Frocks', 'https://images.pexels.com/photos/17921044/pexels-photo-17921044.jpeg?auto=compress&cs=tinysrgb&w=400', 1);
INSERT INTO categories (id, slug, label, image_url, sort_order) VALUES (2, 'tops', 'Shirts & Tees', 'https://images.pexels.com/photos/15179150/pexels-photo-15179150.jpeg?auto=compress&cs=tinysrgb&w=400', 2);
INSERT INTO categories (id, slug, label, image_url, sort_order) VALUES (3, 'trousers', 'Trousers & Shorts', 'https://images.pexels.com/photos/31703288/pexels-photo-31703288.jpeg?auto=compress&cs=tinysrgb&w=400', 3);
INSERT INTO categories (id, slug, label, image_url, sort_order) VALUES (4, 'sets', 'Co-ord & Sets', 'https://images.pexels.com/photos/1620760/pexels-photo-1620760.jpeg?auto=compress&cs=tinysrgb&w=400', 4);
INSERT INTO categories (id, slug, label, image_url, sort_order) VALUES (5, 'outerwear', 'Outerwear', 'https://images.pexels.com/photos/5560031/pexels-photo-5560031.jpeg?auto=compress&cs=tinysrgb&w=400', 5);
INSERT INTO categories (id, slug, label, image_url, sort_order) VALUES (6, 'nightwear', 'Nightwear', 'https://images.pexels.com/photos/18820092/pexels-photo-18820092.jpeg?auto=compress&cs=tinysrgb&w=400', 6);
INSERT INTO categories (id, slug, label, image_url, sort_order) VALUES (7, 'ethnic', 'Ethnic Wear', 'https://images.pexels.com/photos/29668580/pexels-photo-29668580.jpeg?auto=compress&cs=tinysrgb&w=400', 7);
INSERT INTO categories (id, slug, label, image_url, sort_order) VALUES (8, 'accessories', 'Accessories', 'https://images.pexels.com/photos/16567792/pexels-photo-16567792.jpeg?auto=compress&cs=tinysrgb&w=400', 8);

INSERT INTO settings (setting_key, setting_value) VALUES ('trade_contact', '{"email": "trade@nanokids.pk", "phone": "+92 42 3577 6400", "whatsapp": "+92 300 855 2140", "warehouse": "14-A, Quaid-e-Azam Industrial Estate, Kot Lakhpat, Lahore", "showroom": "12-B, Main Boulevard, Gulberg III, Lahore"}');
INSERT INTO settings (setting_key, setting_value) VALUES ('account_manager', '{"name": "Ayesha Malik", "role": "Trade account manager", "phone": "+92 300 855 2140", "email": "ayesha.malik@nanokids.pk", "hours": "Mon – Sat, 10 am – 6 pm"}');
INSERT INTO settings (setting_key, setting_value) VALUES ('campaign', '{"knit": 5560031, "everyday": 1620760, "festive": 17921044, "baby": 16567792, "jumps": 6725093, "trio": 14571367, "formal": 29668580, "hoodie": 28506036, "jeans": 31703288, "pyjama": 18820092, "cube": 15179150, "taupe": 30147132}');
INSERT INTO settings (setting_key, setting_value) VALUES ('colors', '{"ivory": {"n": "Ivory", "h": "#F1EADD"}, "sand": {"n": "Sand", "h": "#D8C6A5"}, "terracotta": {"n": "Terracotta", "h": "#B8654A"}, "olive": {"n": "Olive", "h": "#7D7F5A"}, "sage": {"n": "Sage", "h": "#A3AE95"}, "navy": {"n": "Navy", "h": "#2C3A52"}, "charcoal": {"n": "Charcoal", "h": "#3A3835"}, "rose": {"n": "Dusty Rose", "h": "#D3A9A0"}, "mustard": {"n": "Mustard", "h": "#C9A04A"}, "denim": {"n": "Denim", "h": "#6F8BA4"}, "brick": {"n": "Brick", "h": "#9A4331"}, "white": {"n": "White", "h": "#F7F4EF"}, "black": {"n": "Black", "h": "#22252B"}, "sky": {"n": "Sky", "h": "#8EB8D4"}}');
INSERT INTO settings (setting_key, setting_value) VALUES ('seasons', '[{"key": "summer", "label": "Summer", "note": "Jerseys, linens and lawn for warm floors and long days."}, {"key": "winter", "label": "Winter", "note": "Fleece, knits and quilted layers, packed for the season."}, {"key": "spring", "label": "Spring / Autumn", "note": "Light layers and everyday programmes."}, {"key": "festive", "label": "Festive / Eid", "note": "Embroidered and occasion programmes, booked early."}, {"key": "core", "label": "Core / Year-round", "note": "Replenishment styles carried all year."}]');
INSERT INTO settings (setting_key, setting_value) VALUES ('site_name', 'Nano Kids Wholesale');

INSERT INTO users (role, email, password_hash, name, is_active) VALUES ('admin', 'admin@nanokids.pk', '$2y$12$SSeT45yI6TiPrWwfM7jrE.RqDXAhEbh6T1EfBY/scOGWKoeq4eCL6', 'Trade Admin', 1);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (1, 'NKW-TEE-001', 'Boxy Cotton Tee', 'boxy-cotton-tee', 2, 3, 'summer', 'Combed cotton jersey', 180, 24, 890, 7, 'in_stock', 'A relaxed boxy tee in 180 gsm combed cotton jersey. Pre-washed, ribbed crew, built as a replenishment style.', 'Core', NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (1, 'https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (1, 'https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.3&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (1, 'https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.6&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (1, 'https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (1, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (1, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (1, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (1, 'terracotta', 'Terracotta', '#B8654A', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (1, 'ivory', 'Ivory', '#F1EADD', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (1, 'sage', 'Sage', '#A3AE95', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (1, 24, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (1, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (1, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (1, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (2, 'NKW-OUT-014', 'Hooded Rain Mac', 'hooded-rain-mac', 5, 3, 'winter', 'Coated cotton twill', 220, 12, 2490, 14, 'in_stock', 'Water-repellent hooded mac with taped seams, cotton lining and storm cuffs. Packed on a hanger-ready programme.', 'Bestseller', NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (2, 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (2, 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.3&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (2, 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.6&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (2, 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (2, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (2, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (2, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (2, 'mustard', 'Mustard', '#C9A04A', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (2, 'navy', 'Navy', '#2C3A52', 1);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (2, 12, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (2, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (2, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (2, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (3, 'NKW-OUT-022', 'Utility Field Jacket', 'utility-field-jacket', 5, 4, 'spring', 'Washed cotton twill', 280, 12, 2890, 14, 'in_stock', 'Four-pocket field jacket with a corduroy collar. A junior outerwear staple for spring and winter fills.', 'New', NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (3, 'https://images.unsplash.com/photo-1503919005314-30d93d07d823?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (3, 'https://images.unsplash.com/photo-1503919005314-30d93d07d823?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.4&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (3, 'https://images.unsplash.com/photo-1503919005314-30d93d07d823?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.7&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (3, 'https://images.unsplash.com/photo-1503919005314-30d93d07d823?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (3, '9-10y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (3, '11-12y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (3, '13-14y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (3, 'sand', 'Sand', '#D8C6A5', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (3, 'olive', 'Olive', '#7D7F5A', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (3, 'navy', 'Navy', '#2C3A52', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (3, 12, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (3, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (3, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (3, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (4, 'NKW-DRS-008', 'Garden Smock Dress', 'garden-smock-dress', 1, 3, 'spring', 'Cotton voile', 120, 24, 1990, 10, 'in_stock', 'Loose smocked dress in cotton voile with a hand-finished hem. A high-turn spring dress for boutiques.', 'New', NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (4, 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (4, 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.3&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (4, 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.6&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (4, 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (4, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (4, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (4, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (4, 'ivory', 'Ivory', '#F1EADD', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (4, 'rose', 'Dusty Rose', '#D3A9A0', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (4, 'sage', 'Sage', '#A3AE95', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (4, 24, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (4, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (4, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (4, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (5, 'NKW-SET-011', 'Knit Cardigan & Short Set', 'knit-cardigan-short-set', 4, 3, 'spring', 'Fine-gauge cotton knit', 200, 12, 2590, 14, 'in_stock', 'Fine-gauge cardigan with pleated shorts. Sold as a set — a smart programme for school and gatherings.', 'Bestseller', NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (5, 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (5, 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.3&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (5, 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.6&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (5, 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (5, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (5, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (5, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (5, 'navy', 'Navy', '#2C3A52', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (5, 'ivory', 'Ivory', '#F1EADD', 1);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (5, 12, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (5, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (5, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (5, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (6, 'NKW-ETH-031', 'Printed Lawn Frock', 'printed-lawn-frock', 7, 3, 'festive', 'Cotton lawn', 80, 24, 1890, 12, 'in_stock', 'Printed lawn frock with a gathered skirt and contrast piping. Booked as a festive fill-in programme.', 'Festive', NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (6, 'https://images.unsplash.com/photo-1560506840-ec148e82a604?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (6, 'https://images.unsplash.com/photo-1560506840-ec148e82a604?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.35&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (6, 'https://images.unsplash.com/photo-1560506840-ec148e82a604?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.6499999999999999&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (6, 'https://images.unsplash.com/photo-1560506840-ec148e82a604?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (6, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (6, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (6, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (6, 'mustard', 'Mustard', '#C9A04A', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (6, 'rose', 'Dusty Rose', '#D3A9A0', 1);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (6, 24, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (6, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (6, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (6, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (7, 'NKW-BBY-003', 'Essential Body Suit 3-Pack', 'essential-body-suit-3-pack', 4, 1, 'core', 'Organic cotton jersey', 160, 36, 690, 7, 'in_stock', 'Envelope-neck body suits in organic cotton. Sold as a pack of 3. A newborn replenishment essential.', 'Core', 3, '3 assorted', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (7, 'https://images.unsplash.com/photo-1622290319146-7b63df48a635?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (7, 'https://images.unsplash.com/photo-1622290319146-7b63df48a635?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.4&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (7, 'https://images.unsplash.com/photo-1622290319146-7b63df48a635?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.7&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (7, 'https://images.unsplash.com/photo-1622290319146-7b63df48a635?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (7, '0-3m', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (7, '3-6m', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (7, '6-12m', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (7, 'ivory', 'Ivory', '#F1EADD', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (7, 'sand', 'Sand', '#D8C6A5', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (7, 'sage', 'Sage', '#A3AE95', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (7, 36, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (7, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (7, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (7, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (8, 'NKW-OUT-041', 'Quilted Winter Jacket', 'quilted-winter-jacket', 5, 2, 'winter', 'Quilted cotton / recycled fill', 320, 12, 3290, 18, 'in_stock', 'Lightly quilted jacket with a stand collar and snap placket. Packed by size run for winter programmes.', NULL, NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (8, 'https://images.unsplash.com/photo-1621452773781-0f992fd1f5cb?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (8, 'https://images.unsplash.com/photo-1621452773781-0f992fd1f5cb?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.45&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (8, 'https://images.unsplash.com/photo-1621452773781-0f992fd1f5cb?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.75&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (8, 'https://images.unsplash.com/photo-1621452773781-0f992fd1f5cb?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (8, '12-18m', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (8, '18-24m', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (8, '2-3y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (8, 'navy', 'Navy', '#2C3A52', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (8, 'mustard', 'Mustard', '#C9A04A', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (8, 'olive', 'Olive', '#7D7F5A', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (8, 12, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (8, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (8, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (8, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (9, 'NKW-TRS-019', 'Everyday Jersey Leggings', 'everyday-jersey-leggings', 3, 3, 'core', 'Cotton elastane jersey', 220, 36, 590, 7, 'in_stock', 'Everyday leggings with a wide waistband. Suggested pack of 6. High-frequency core trouser.', 'Core', 6, 'S-M-L mixed', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (9, 'https://images.unsplash.com/photo-1571210862729-78a52d3779a2?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (9, 'https://images.unsplash.com/photo-1571210862729-78a52d3779a2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.8&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (9, 'https://images.unsplash.com/photo-1571210862729-78a52d3779a2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=1.1&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (9, 'https://images.unsplash.com/photo-1571210862729-78a52d3779a2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (9, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (9, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (9, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (9, 'navy', 'Navy', '#2C3A52', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (9, 'charcoal', 'Charcoal', '#3A3835', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (9, 'rose', 'Dusty Rose', '#D3A9A0', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (9, 'ivory', 'Ivory', '#F1EADD', 3);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (9, 36, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (9, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (9, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (9, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (10, 'NKW-NIT-016', 'Ribbed Cotton Pyjama Set', 'ribbed-cotton-pyjama-set', 6, 2, 'core', 'Ribbed cotton jersey', 180, 24, 1290, 8, 'in_stock', 'Two-piece ribbed pyjama with a gentle elastic waist. A year-round nightwear programme.', NULL, NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (10, 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (10, 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.35&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (10, 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.6499999999999999&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (10, 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (10, '12-18m', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (10, '18-24m', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (10, '2-3y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (10, 'ivory', 'Ivory', '#F1EADD', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (10, 'sage', 'Sage', '#A3AE95', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (10, 'rose', 'Dusty Rose', '#D3A9A0', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (10, 24, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (10, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (10, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (10, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (11, 'NKW-ETH-044', 'Waistcoat Kurta Set', 'waistcoat-kurta-set', 7, 3, 'festive', 'Cotton silk', 140, 12, 3990, 21, 'made_to_order', 'Cotton-silk kurta with an embroidered waistcoat and straight pyjama. Made to order for Eid programmes.', 'Festive', NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (11, 'https://images.unsplash.com/photo-1485546246426-74dc88dec4d9?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (11, 'https://images.unsplash.com/photo-1485546246426-74dc88dec4d9?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.35&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (11, 'https://images.unsplash.com/photo-1485546246426-74dc88dec4d9?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.6499999999999999&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (11, 'https://images.unsplash.com/photo-1485546246426-74dc88dec4d9?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (11, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (11, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (11, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (11, 'ivory', 'Ivory', '#F1EADD', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (11, 'navy', 'Navy', '#2C3A52', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (11, 'sand', 'Sand', '#D8C6A5', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (11, 12, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (11, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (11, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (11, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (12, 'NKW-ETH-048', 'Chikankari Kurta Set', 'chikankari-kurta-set', 7, 4, 'festive', 'Cotton voile, chikankari', 90, 12, 4290, 21, 'made_to_order', 'Chikankari kurta with trousers and a light dupatta. Hand-finished — book 21 days ahead of the season.', 'Festive', NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (12, 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (12, 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.4&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (12, 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.7&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (12, 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (12, '9-10y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (12, '11-12y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (12, '13-14y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (12, 'ivory', 'Ivory', '#F1EADD', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (12, 'rose', 'Dusty Rose', '#D3A9A0', 1);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (12, 12, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (12, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (12, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (12, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (13, 'NKW-ETH-052', 'Heritage Shalwar Kameez', 'heritage-shalwar-kameez', 7, 4, 'festive', 'Wash-and-wear cotton', 160, 12, 3490, 16, 'in_stock', 'Classic shalwar kameez with a mandarin collar and contrast placket. A reliable festive staple.', 'Festive', NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (13, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (13, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.35&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (13, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.6499999999999999&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (13, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (13, '9-10y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (13, '11-12y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (13, '13-14y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (13, 'ivory', 'Ivory', '#F1EADD', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (13, 'sage', 'Sage', '#A3AE95', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (13, 'sand', 'Sand', '#D8C6A5', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (13, 12, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (13, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (13, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (13, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (14, 'NKW-TRS-027', 'Linen Drawstring Shorts', 'linen-drawstring-shorts', 3, 3, 'summer', 'Linen-cotton blend', 170, 36, 790, 7, 'in_stock', 'Pull-on linen-blend shorts with a drawstring and side pockets. A summer volume style.', NULL, NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (14, 'https://images.unsplash.com/photo-1489710437720-ebb67ec84dd2?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (14, 'https://images.unsplash.com/photo-1489710437720-ebb67ec84dd2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.8&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (14, 'https://images.unsplash.com/photo-1489710437720-ebb67ec84dd2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=1.1&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (14, 'https://images.unsplash.com/photo-1489710437720-ebb67ec84dd2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (14, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (14, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (14, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (14, 'sand', 'Sand', '#D8C6A5', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (14, 'sage', 'Sage', '#A3AE95', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (14, 'terracotta', 'Terracotta', '#B8654A', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (14, 36, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (14, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (14, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (14, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (15, 'NKW-TRS-033', 'Cotton Twill Chinos', 'cotton-twill-chinos', 3, 4, 'core', 'Stretch cotton twill', 240, 24, 1390, 10, 'in_stock', 'Slim chinos with an adjustable inner waist. A core trouser for school-age programmes.', NULL, NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (15, 'https://images.unsplash.com/photo-1472162072942-cd5147eb3902?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (15, 'https://images.unsplash.com/photo-1472162072942-cd5147eb3902?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.8&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (15, 'https://images.unsplash.com/photo-1472162072942-cd5147eb3902?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=1.1&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (15, 'https://images.unsplash.com/photo-1472162072942-cd5147eb3902?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (15, '9-10y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (15, '11-12y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (15, '13-14y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (15, 'sand', 'Sand', '#D8C6A5', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (15, 'olive', 'Olive', '#7D7F5A', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (15, 'navy', 'Navy', '#2C3A52', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (15, 24, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (15, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (15, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (15, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (16, 'NKW-SET-018', 'Sprint Jersey Set', 'sprint-jersey-set', 4, 3, 'summer', 'Cotton-rich jersey', 180, 24, 1590, 8, 'in_stock', 'Top-and-short set with flat seams. Packed as a set for summer floors.', NULL, NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (16, 'https://images.unsplash.com/photo-1571210862729-78a52d3779a2?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (16, 'https://images.unsplash.com/photo-1571210862729-78a52d3779a2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.4&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (16, 'https://images.unsplash.com/photo-1571210862729-78a52d3779a2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.7&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (16, 'https://images.unsplash.com/photo-1571210862729-78a52d3779a2?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (16, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (16, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (16, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (16, 'mustard', 'Mustard', '#C9A04A', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (16, 'olive', 'Olive', '#7D7F5A', 1);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (16, 24, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (16, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (16, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (16, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (17, 'NKW-TOP-025', 'Heavyweight Crewneck', 'heavyweight-crewneck', 2, 4, 'winter', 'Loopback cotton fleece', 380, 24, 1790, 12, 'in_stock', '380 gsm loopback crew with a ribbed neck and double-stitched seams. A winter fleece programme.', NULL, NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (17, 'https://images.unsplash.com/photo-1621452773781-0f992fd1f5cb?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (17, 'https://images.unsplash.com/photo-1621452773781-0f992fd1f5cb?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.45&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (17, 'https://images.unsplash.com/photo-1621452773781-0f992fd1f5cb?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.75&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (17, 'https://images.unsplash.com/photo-1621452773781-0f992fd1f5cb?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (17, '9-10y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (17, '11-12y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (17, '13-14y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (17, 'brick', 'Brick', '#9A4331', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (17, 'charcoal', 'Charcoal', '#3A3835', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (17, 'navy', 'Navy', '#2C3A52', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (17, 24, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (17, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (17, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (17, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (18, 'NKW-DRS-021', 'Ruffle Sun Dress', 'ruffle-sun-dress', 1, 2, 'summer', 'Printed cotton poplin', 110, 24, 1690, 9, 'in_stock', 'Sleeveless printed sun dress with flutter ruffles and a lined skirt. A baby summer bestseller.', 'New', NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (18, 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (18, 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.45&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (18, 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.75&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (18, 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (18, '12-18m', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (18, '18-24m', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (18, '2-3y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (18, 'mustard', 'Mustard', '#C9A04A', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (18, 'ivory', 'Ivory', '#F1EADD', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (18, 'rose', 'Dusty Rose', '#D3A9A0', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (18, 24, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (18, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (18, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (18, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (19, 'NKW-ACC-006', 'Knit Booties & Mitts Set', 'knit-booties-mitts-set', 8, 1, 'winter', 'Merino-blend knit', 240, 50, 490, 10, 'in_stock', 'Hand-finished booties and mitts. Sold with a 12-unit inner pack. A high-MOQ accessory programme.', NULL, 12, '12-unit inner', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (19, 'https://images.unsplash.com/photo-1555252333-9f8e92e65df9?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (19, 'https://images.unsplash.com/photo-1555252333-9f8e92e65df9?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.55&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (19, 'https://images.unsplash.com/photo-1555252333-9f8e92e65df9?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.8500000000000001&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (19, 'https://images.unsplash.com/photo-1555252333-9f8e92e65df9?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (19, '0-3m', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (19, '3-6m', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (19, '6-12m', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (19, 'ivory', 'Ivory', '#F1EADD', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (19, 'sand', 'Sand', '#D8C6A5', 1);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (19, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (19, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (19, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (20, 'NKW-TRS-038', 'Denim Jogger Trousers', 'denim-jogger-trousers', 3, 3, 'spring', 'Stretch denim', 260, 24, 1290, 10, 'in_stock', 'Soft-washed stretch-denim joggers with a rib cuff. Everyday denim without the hardware fuss.', NULL, NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (20, 'https://images.unsplash.com/photo-1514090458221-65bb69cf63e6?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (20, 'https://images.unsplash.com/photo-1514090458221-65bb69cf63e6?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.8&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (20, 'https://images.unsplash.com/photo-1514090458221-65bb69cf63e6?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=1.1&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (20, 'https://images.unsplash.com/photo-1514090458221-65bb69cf63e6?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (20, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (20, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (20, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (20, 'denim', 'Denim', '#6F8BA4', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (20, 'charcoal', 'Charcoal', '#3A3835', 1);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (20, 24, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (20, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (20, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (20, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (21, 'NKW-TOP-012', 'Washed Denim Shirt', 'washed-denim-shirt', 2, 4, 'spring', 'Stonewashed denim', 200, 24, 1490, 11, 'in_stock', 'Relaxed denim shirt with pearl snaps. A year-round junior shirt programme.', NULL, NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (21, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (21, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.45&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (21, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.75&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (21, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (21, '9-10y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (21, '11-12y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (21, '13-14y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (21, 'denim', 'Denim', '#6F8BA4', 0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (21, 24, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (21, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (21, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (21, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (22, 'NKW-NIT-028', 'Printed Jersey Night Suit', 'printed-jersey-night-suit', 6, 3, 'core', 'Cotton jersey', 160, 36, 1190, 8, 'in_stock', 'Printed night suit in breathable jersey. Suggested pack of 6 mixed prints.', NULL, 6, '6 mixed prints', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (22, 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (22, 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.35&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (22, 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.6499999999999999&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (22, 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (22, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (22, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (22, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (22, 'sky', 'Sky', '#8EB8D4', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (22, 'mustard', 'Mustard', '#C9A04A', 1);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (22, 'ivory', 'Ivory', '#F1EADD', 2);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (22, 36, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (22, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (22, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (22, 500, NULL, 22.0);

INSERT INTO products (id, sku, name, slug, category_id, age_group_id, season, fabric, gsm, moq, base_price, lead_time_days, stock_status, description, tag, pack_units, pack_ratio, is_active) VALUES (23, 'NKW-DRS-015', 'Pleated Pinafore', 'pleated-pinafore', 1, 3, 'winter', 'Cotton twill', 200, 24, 1890, 10, 'in_stock', 'Twill pinafore with a pleated skirt and adjustable straps. Layers over tees through the year.', NULL, NULL, NULL, 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (23, 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&q=75&w=1000&h=1250', 0);
INSERT INTO product_images (product_id, url, sort_order) VALUES (23, 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.45&fp-z=1.6', 1);
INSERT INTO product_images (product_id, url, sort_order) VALUES (23, 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&q=75&w=1000&h=1250&crop=focalpoint&fp-x=0.5&fp-y=0.75&fp-z=2.1', 2);
INSERT INTO product_images (product_id, url, sort_order) VALUES (23, 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&q=75&w=1000&h=1250&crop=entropy', 3);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (23, '4-5y', 0);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (23, '6-7y', 1);
INSERT INTO product_sizes (product_id, size_label, sort_order) VALUES (23, '7-8y', 2);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (23, 'navy', 'Navy', '#2C3A52', 0);
INSERT INTO product_colors (product_id, color_key, color_name, hex, sort_order) VALUES (23, 'olive', 'Olive', '#7D7F5A', 1);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (23, 24, 49, 0.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (23, 50, 199, 8.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (23, 200, 499, 15.0);
INSERT INTO price_tiers (product_id, min_qty, max_qty, discount_pct) VALUES (23, 500, NULL, 22.0);
