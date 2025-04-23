
CREATE DATABASE IF NOT EXISTS vetcare;
USE vetcare;

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_name VARCHAR(255),
    owner_name VARCHAR(255),
    phone VARCHAR(20),
    appointment_date DATETIME,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (name, email, password) VALUES ('Admin', 'admin@vetcare.com', 'admin123');
