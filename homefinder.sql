CREATE DATABASE IF NOT EXISTS homefinder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE homefinder;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(80) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(80) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS preferences (
    pref_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    transaction_type ENUM('rent','buy') NOT NULL DEFAULT 'rent',
    bhk_type VARCHAR(20) NOT NULL DEFAULT '2BHK',
    preferred_areas VARCHAR(255),
    min_budget DECIMAL(12,2) DEFAULT 0,
    max_budget DECIMAL(12,2) DEFAULT 999999999,
    property_type ENUM('society','standalone','any') DEFAULT 'any',
    floor_preference ENUM('any','ground','low','mid','high') DEFAULT 'any',
    possession_type ENUM('ready','under_construction','any') DEFAULT 'any',
    amenities_needed TEXT,
    additional_notes TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    property_type ENUM('apartment','villa','independent_house','studio') NOT NULL,
    transaction_type ENUM('rent','buy') NOT NULL,
    bhk_type VARCHAR(20) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    area_locality VARCHAR(100) NOT NULL,
    full_address TEXT NOT NULL,
    city VARCHAR(60) NOT NULL DEFAULT 'Pune',
    floor_number TINYINT,
    total_floors TINYINT,
    carpet_area_sqft SMALLINT,
    facing_direction ENUM('east','west','north','south','any') DEFAULT 'any',
    furnishing_status ENUM('furnished','semi_furnished','unfurnished') NOT NULL,
    possession_type ENUM('ready','under_construction') NOT NULL DEFAULT 'ready',
    society_type ENUM('society','standalone') NOT NULL DEFAULT 'society',
    amenities TEXT,
    primary_image VARCHAR(255) DEFAULT 'assets/images/default-property.svg',
    is_featured TINYINT(1) DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    created_by_admin INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by_admin) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS property_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order TINYINT DEFAULT 0,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS search_requests (
    req_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(80) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(120),
    area VARCHAR(100) NOT NULL,
    transaction_type ENUM('rent','buy') NOT NULL,
    bhk_type VARCHAR(20) NOT NULL,
    min_budget DECIMAL(12,2) DEFAULT 0,
    max_budget DECIMAL(12,2) DEFAULT 9999999,
    user_id INT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS interests (
    interest_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    status ENUM('pending','call_scheduled','site_visit_scheduled','completed','cancelled') DEFAULT 'pending',
    scheduled_datetime DATETIME,
    admin_remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_interest (user_id, property_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    interest_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL,
    amount DECIMAL(8,2) NOT NULL DEFAULT 499.00,
    payment_method VARCHAR(30) DEFAULT 'simulated',
    status ENUM('paid','failed','refunded') DEFAULT 'paid',
    transaction_ref VARCHAR(60),
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (interest_id) REFERENCES interests(interest_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO properties (title, description, property_type, transaction_type, bhk_type, price, area_locality, full_address, city, floor_number, total_floors, carpet_area_sqft, facing_direction, furnishing_status, possession_type, society_type, amenities, primary_image, is_featured, status) VALUES
('Serene Heights 2BHK', 'Well-ventilated apartment with modern interiors and easy access to schools and markets.', 'apartment', 'rent', '2BHK', 24000, 'Kothrud', 'Serene Heights, Near Karve Road, Kothrud, Pune', 'Pune', 7, 12, 920, 'east', 'semi_furnished', 'ready', 'society', '["gym","security","lift","parking"]', 'assets/images/default-property.svg', 1, 'active'),
('Baner Garden Residency', 'Premium 3BHK home with garden view and club amenities in a gated community.', 'apartment', 'buy', '3BHK', 9500000, 'Baner', 'Baner Garden Residency, Baner, Pune', 'Pune', 5, 16, 1380, 'west', 'furnished', 'ready', 'society', '["swimming_pool","garden","security","clubhouse","parking"]', 'assets/images/default-property.svg', 1, 'active'),
('Wakad Skyline Villa', 'Spacious villa with private parking and a calm residential environment.', 'villa', 'buy', '4BHK', 18500000, 'Wakad', 'Wakad Skyline Villas, Wakad, Pune', 'Pune', 1, 2, 2200, 'north', 'unfurnished', 'ready', 'standalone', '["garden","parking","security"]', 'assets/images/default-property.svg', 1, 'active'),
('Aundh Comfort Nest', 'Affordable 1BHK with quick access to the main road and local shopping.', 'apartment', 'rent', '1BHK', 14000, 'Aundh', 'Comfort Nest, Aundh, Pune', 'Pune', 3, 8, 550, 'south', 'semi_furnished', 'ready', 'society', '["lift","parking","security"]', 'assets/images/default-property.svg', 0, 'active'),
('Viman Nagar Elite Tower', 'Elegant 3BHK with high-floor city views and premium clubhouse access.', 'apartment', 'rent', '3BHK', 42000, 'Viman Nagar', 'Elite Tower, Viman Nagar, Pune', 'Pune', 11, 18, 1250, 'east', 'furnished', 'ready', 'society', '["swimming_pool","gym","clubhouse","lift","security","parking"]', 'assets/images/default-property.svg', 0, 'active');

INSERT INTO property_images (property_id, image_path, sort_order) VALUES
(1, 'assets/images/default-property.svg', 0),
(2, 'assets/images/default-property.svg', 0),
(3, 'assets/images/default-property.svg', 0),
(4, 'assets/images/default-property.svg', 0),
(5, 'assets/images/default-property.svg', 0);
