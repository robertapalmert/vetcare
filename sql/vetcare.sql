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

-- Tabela servicii (serviciile oferite în clinică)

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    duration_minutes INT
);

-- Tabela programări

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_name VARCHAR(255),
    owner_name VARCHAR(255),
    phone VARCHAR(20),
    appointment_date DATETIME,
    service_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- Tabela sărbători legale
CREATE TABLE holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    holiday_date DATE NOT NULL,
    name VARCHAR(255)
);
-- Tabela program clinică
CREATE TABLE working_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_of_week INT,
    open_time TIME,
    close_time TIME,
    is_open BOOLEAN
);

-- Inserare admin implicit (se va modifica ulterior parola din interfață)
INSERT INTO admin (email, password) VALUES ('admin@vetcare.com', 'admin123');

-- Inserare sarbatori legale 2025+2026
INSERT INTO holidays (id, holiday_date, name) VALUES
(1, '2025-01-01', 'Anul Nou'),
(2, '2025-01-02', 'A doua zi de Anul Nou'),
(3, '2025-04-20', 'Paștele Ortodox'),
(4, '2025-04-21', 'A doua zi de Paște'),
(5, '2025-05-01', 'Ziua Muncii'),
(6, '2025-06-08', 'Rusalii'),
(7, '2025-06-09', 'A doua zi de Rusalii'),
(8, '2025-08-15', 'Adormirea Maicii Domnului'),
(9, '2025-11-30', 'Sfântul Andrei'),
(10, '2025-12-01', 'Ziua Națională a României'),
(11, '2025-12-25', 'Crăciunul'),
(12, '2025-12-26', 'A doua zi de Crăciun'),
(13, '2026-01-01', 'Anul Nou'),
(14, '2026-01-02', 'A doua zi de Anul Nou'),
(15, '2026-04-12', 'Paștele Ortodox'),
(16, '2026-04-13', 'A doua zi de Paște'),
(17, '2026-05-01', 'Ziua Muncii'),
(18, '2026-05-31', 'Rusalii'),
(19, '2026-06-01', 'A doua zi de Rusalii'),
(20, '2026-08-15', 'Adormirea Maicii Domnului'),
(21, '2026-11-30', 'Sfântul Andrei'),
(22, '2026-12-01', 'Ziua Națională a României'),
(23, '2026-12-25', 'Crăciunul'),
(24, '2026-12-26', 'A doua zi de Crăciun');

-- Inserare servicii disponibile

INSERT INTO services (id, name, duration_minutes) VALUES
(1, 'Vaccination', 20),
(2, 'Surgery', 120),
(3, 'Consultation', 30),
(4, 'Deworming', 20),
(5, 'Check-up', 30),
(6, 'X-Ray', 30),
(7, 'Ultrasound', 45),
(8, 'Microchipping', 30),
(9, 'Emergency Visit', 60),
(10, 'Travel Certificate', 30),
(11, 'Wound Care', 30),
(12, 'Blood Test', 30),
(13, 'Grooming', 60),
(14, 'Dental Cleaning', 60),
(15, 'Nail Clipping', 20),
(16, 'Euthanasia', 60),
(17, 'Other', 30);

-- Inserare program clinică
INSERT INTO working_hours (day_of_week, open_time, close_time, is_open) VALUES
(0, NULL, NULL, 0),        -- Duminică: închis
(1, '08:00:00', '17:00:00', 1),  -- Luni
(2, '08:00:00', '17:00:00', 1),  -- Marți
(3, '08:00:00', '17:00:00', 1),  -- Miercuri
(4, '08:00:00', '17:00:00', 1),  -- Joi
(5, '08:00:00', '17:00:00', 1),  -- Vineri
(6, '10:00:00', '14:00:00', 1);  -- Sâmbătă


