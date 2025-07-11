-- Drop and create the new database
DROP DATABASE IF EXISTS `cords_inventory`;
CREATE DATABASE `cords_inventory` DEFAULT CHARACTER SET = `utf8mb4` COLLATE = `utf8mb4_unicode_ci`;

-- Create a dedicated user (optional, change username/password as needed)
CREATE USER IF NOT EXISTS 'cords_user'@'localhost' IDENTIFIED BY 'password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON `cords_inventory`.* TO 'cords_user'@'localhost';
FLUSH PRIVILEGES;

CREATE USER IF NOT EXISTS 'cords_user'@'%' IDENTIFIED BY 'password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES ON `cords_inventory`.* TO 'cords_user'@'%';
FLUSH PRIVILEGES;
