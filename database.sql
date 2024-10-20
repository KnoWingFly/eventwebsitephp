CREATE DATABASE event_registration;

use event_registration;
-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    location VARCHAR(255),
    event_date DATE,
    event_time TIME,  
    max_participants INT,
    banner VARCHAR(255) NULL,
    status ENUM('open', 'closed', 'canceled') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Registrations table
CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

ALTER TABLE users ADD reset_token VARCHAR(255) NULL, ADD reset_token_expiry DATETIME NULL;

ALTER TABLE registrations 
ADD CONSTRAINT fk_event 
FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE;
