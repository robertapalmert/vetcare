-- Crearea bazei de date
CREATE DATABASE IF NOT EXISTS vetcare;
USE vetcare;

-- Tabela admin (autentificare și setări cont)
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela programări
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_name VARCHAR(255),
    owner_name VARCHAR(255),
    phone VARCHAR(20),
    appointment_date DATETIME,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela sărbători legale
CREATE TABLE holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    holiday_date DATE NOT NULL,
    name VARCHAR(255)
);

-- Inserare admin implicit (se va modifica ulterior parola din interfață)
INSERT INTO admin (email, password) VALUES ('admin@vetcare.com', 'admin123');
