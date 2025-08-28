
ALTER TABLE products ADD COLUMN cord_category_id CHAR(36) NULL AFTER description;
ALTER TABLE products ADD COLUMN max_data_transfer_gbps DECIMAL(8,3) NULL AFTER model_number;
ALTER TABLE products ADD COLUMN max_power_watts DECIMAL(6,2) NULL AFTER max_data_transfer_gbps;
ALTER TABLE products ADD COLUMN cord_length_meters DECIMAL(5,2) NULL AFTER max_power_watts;
ALTER TABLE products ADD COLUMN compatibility_rating DECIMAL(3,2) DEFAULT 0.00 AFTER reliability_score;
ALTER TABLE products ADD COLUMN search_keywords TEXT NULL AFTER alt_text;
ALTER TABLE products ADD INDEX idx_products_cord_category (cord_category_id);
ALTER TABLE products ADD INDEX idx_products_data_speed (max_data_transfer_gbps);
ALTER TABLE products ADD INDEX idx_products_power_delivery (max_power_watts);
ALTER TABLE products ADD INDEX idx_products_cord_length (cord_length_meters);
ALTER TABLE products ADD INDEX idx_products_compatibility_rating (compatibility_rating);
ALTER TABLE products ADD FULLTEXT idx_products_cord_search (title, manufacturer, model_number, search_keywords);

CREATE TABLE IF NOT EXISTS cord_categories (
id CHAR(36) NOT NULL PRIMARY KEY,
parent_category_id CHAR(36) NULL,
category_name VARCHAR(100) NOT NULL,
category_description TEXT NULL,
category_icon VARCHAR(255) NULL,
display_order INT DEFAULT 0,
is_active BOOLEAN DEFAULT TRUE,
created DATETIME NOT NULL,
modified DATETIME NOT NULL,
INDEX idx_cord_categories_parent (parent_category_id),
INDEX idx_cord_categories_display_order (display_order),
INDEX idx_cord_categories_active (is_active),
CONSTRAINT fk__cord_categories__cord_categories FOREIGN KEY (parent_category_id) REFERENCES cord_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE products
ADD CONSTRAINT fk__products__cord_categories
FOREIGN KEY (cord_category_id) REFERENCES cord_categories(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS port_types (
id CHAR(36) NOT NULL PRIMARY KEY,
port_name VARCHAR(100) NOT NULL,
port_family VARCHAR(50) NULL,
form_factor VARCHAR(30) NULL,
connector_gender ENUM('Male','Female','Reversible') NOT NULL,
pin_count INT NULL,
max_voltage DECIMAL(5,2) NULL,
max_current DECIMAL(5,2) NULL,
data_pin_count INT NULL,
power_pin_count INT NULL,
ground_pin_count INT NULL,
electrical_shielding VARCHAR(50) NULL,
durability_cycles INT NULL,
introduced_date DATE NULL,
deprecated_date DATE NULL,
physical_specs VARCHAR(100) NULL,
created DATETIME NOT NULL,
modified DATETIME NOT NULL,
UNIQUE KEY idx_port_types_name (port_name),
INDEX idx_port_types_family (port_family),
INDEX idx_port_types_form_factor (form_factor),
INDEX idx_port_types_max_current (max_current),
INDEX idx_port_types_max_voltage (max_voltage),
INDEX idx_port_types_active_ports (deprecated_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cable_capabilities (
id CHAR(36) NOT NULL PRIMARY KEY,
capability_name VARCHAR(100) NOT NULL,
capability_category VARCHAR(50) NULL,
technical_specifications JSON NULL,
testing_standard VARCHAR(255) NULL,
certifying_organization VARCHAR(100) NULL,
created DATETIME NOT NULL,
modified DATETIME NOT NULL,
UNIQUE KEY idx_cable_capabilities_name (capability_name),
INDEX idx_cable_capabilities_category (capability_category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS physical_specs (
id CHAR(36) NOT NULL PRIMARY KEY,
spec_name VARCHAR(100) NOT NULL,
spec_type ENUM('measurement','material','rating','boolean','text') DEFAULT 'text',
measurement_unit VARCHAR(20) NULL,
spec_description TEXT NULL,
created DATETIME NOT NULL,
modified DATETIME NOT NULL,
UNIQUE KEY idx_physical_specs_name (spec_name),
INDEX idx_physical_specs_type (spec_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS device_compatibility (
id CHAR(36) NOT NULL PRIMARY KEY,
product_id CHAR(36) NOT NULL,
device_category VARCHAR(50) NOT NULL,
device_brand VARCHAR(50) NULL,
device_model VARCHAR(100) NULL,
compatibility_level ENUM('Full','Partial','Limited','Incompatible') NOT NULL,
compatibility_notes TEXT NULL,
performance_rating DECIMAL(3,2) NULL,
verification_date DATE NULL,
verified_by VARCHAR(100) NULL,
user_reported_rating DECIMAL(3,2) NULL,
created DATETIME NOT NULL,
modified DATETIME NOT NULL,
INDEX idx_device_compatibility_product (product_id),
INDEX idx_device_compatibility_device (device_category, device_brand),
INDEX idx_device_compatibility_level (compatibility_level),
INDEX idx_device_compatibility_rating (performance_rating),
CONSTRAINT fk__device_compatibility__products FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS use_case_scenarios (
id CHAR(36) NOT NULL PRIMARY KEY,
scenario_name VARCHAR(100) NOT NULL,
scenario_description TEXT NULL,
cord_category_id CHAR(36) NULL,
required_capabilities JSON NULL,
preferred_length_range VARCHAR(50) NULL,
environment_suitability ENUM('Indoor','Outdoor','Automotive','Marine','Industrial') DEFAULT 'Indoor',
priority_factors JSON NULL,
created DATETIME NOT NULL,
modified DATETIME NOT NULL,
UNIQUE KEY idx_use_case_scenarios_name (scenario_name),
INDEX idx_use_case_scenarios_category (cord_category_id),
INDEX idx_use_case_scenarios_environment (environment_suitability),
CONSTRAINT fk__use_case_scenarios__cord_categories FOREIGN KEY (cord_category_id) REFERENCES cord_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cord_endpoints (
id CHAR(36) NOT NULL PRIMARY KEY,
product_id CHAR(36) NOT NULL,
port_type_id CHAR(36) NOT NULL,
endpoint_position ENUM('end_a','end_b') NOT NULL,
is_detachable BOOLEAN DEFAULT FALSE,
adapter_functionality TEXT NULL,
created DATETIME NOT NULL,
modified DATETIME NOT NULL,
UNIQUE KEY idx_cord_endpoints_unique_position (product_id, port_type_id, endpoint_position),
INDEX idx_cord_endpoints_port_type (port_type_id, product_id),
INDEX idx_cord_endpoints_detachable (is_detachable),
CONSTRAINT fk__cord_endpoints__products FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
CONSTRAINT fk__cord_endpoints__port_types FOREIGN KEY (port_type_id) REFERENCES port_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cord_capabilities (
id CHAR(36) NOT NULL PRIMARY KEY,
product_id CHAR(36) NOT NULL,
cable_capability_id CHAR(36) NOT NULL,
capability_value VARCHAR(255) NULL,
numeric_rating DECIMAL(10,3) NULL,
is_certified BOOLEAN DEFAULT FALSE,
certification_date DATE NULL,
created DATETIME NOT NULL,
modified DATETIME NOT NULL,
UNIQUE KEY idx_cord_capabilities_unique (product_id, cable_capability_id),
INDEX idx_cord_capabilities_numeric_range (cable_capability_id, numeric_rating),
INDEX idx_cord_capabilities_certified (cable_capability_id, is_certified),
CONSTRAINT fk__cord_capabilities__products FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
CONSTRAINT fk__cord_capabilities__cable_capabilities FOREIGN KEY (cable_capability_id) REFERENCES cable_capabilities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cord_physical_specs (
id CHAR(36) NOT NULL PRIMARY KEY,
product_id CHAR(36) NOT NULL,
physical_spec_id CHAR(36) NOT NULL,
spec_value VARCHAR(255) NULL,
numeric_value DECIMAL(10,3) NULL,
created DATETIME NOT NULL,
modified DATETIME NOT NULL,
UNIQUE KEY idx_cord_physical_specs_unique (product_id, physical_spec_id),
INDEX idx_cord_physical_specs_numeric (physical_spec_id, numeric_value),
INDEX idx_cord_physical_specs_text (physical_spec_id, spec_value(50)),
CONSTRAINT fk__cord_physical_specs__products FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
CONSTRAINT fk__cord_physical_specs__physical_specs FOREIGN KEY (physical_spec_id) REFERENCES physical_specs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_links (
id CHAR(36) NOT NULL PRIMARY KEY,
product_id CHAR(36) NOT NULL,
store_url VARCHAR(500) NOT NULL,
link_type VARCHAR(50) DEFAULT 'purchase',
retailer_name VARCHAR(100) NULL,
listed_price DECIMAL(10,2) NULL,
price_currency CHAR(3) DEFAULT 'USD',
last_price_check DATETIME NULL,
link_status ENUM('active','dead','redirect','out_of_stock') DEFAULT 'active',
affiliate_link BOOLEAN DEFAULT FALSE,
created DATETIME NOT NULL,
modified DATETIME NOT NULL,
INDEX idx_purchase_links_product (product_id),
INDEX idx_purchase_links_type (link_type),
INDEX idx_purchase_links_retailer (retailer_name),
INDEX idx_purchase_links_status (link_status),
INDEX idx_purchase_links_price (listed_price, price_currency),
CONSTRAINT fk__purchase_links__products FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

