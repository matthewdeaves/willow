USE `cms`;

CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price_usd DECIMAL(10, 2),
    category_rating VARCHAR(100),
    comments TEXT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS connectors (
    connector_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS product_connectors (
    product_connector_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    connector_id INT NOT NULL,
    position ENUM('end_a', 'end_b') NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (connector_id) REFERENCES connectors(connector_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usages (
    usage_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS product_usages (
    product_usage_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    usage_id INT NOT NULL,
    value VARCHAR(255),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (usage_id) REFERENCES usages(usage_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attributes (
    attribute_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS product_attributes (
    product_attribute_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_id INT NOT NULL,
    value VARCHAR(255),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES attributes(attribute_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS product_links (
    product_link_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    url VARCHAR(2083) NOT NULL,
    last_verification_date DATE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;
-- Uncomment the following lines if you want to create a product_images table
-- This table can be used to store images associated with products.
-- CREATE TABLE IF NOT EXISTS product_images (
--     product_image_id INT AUTO_INCREMENT PRIMARY KEY,
--     product_id INT NOT NULL,
--     image_url VARCHAR(2083) NOT NULL,
--     FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
-- ) ENGINE=InnoDB;