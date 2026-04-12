-- Create database (run this once)
CREATE DATABASE IF NOT EXISTS medque_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medque_db;

-- Users table (patients & labs)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('patient', 'lab') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Patients table
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone VARCHAR(30),
    date_of_birth DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Labs table
CREATE TABLE labs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lab_name VARCHAR(150) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(30),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tests table (most common tests & scans)
CREATE TABLE tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT
);

-- Appointments / queue
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    lab_id INT NOT NULL,
    test_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    queue_number INT NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (lab_id) REFERENCES labs(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
);

-- Seed tests
INSERT INTO tests (name, category, description) VALUES
('Complete Blood Count (CBC)', 'Laboratory Tests', 'Measures red and white blood cells, platelets, etc.'),
('Fasting Blood Glucose', 'Laboratory Tests', 'Measures blood sugar level after fasting.'),
('Random Blood Glucose', 'Laboratory Tests', 'Measures blood sugar level at any time.'),
('Lipid Profile', 'Laboratory Tests', 'Measures cholesterol and triglycerides.'),
('Liver Function Test', 'Laboratory Tests', 'Evaluates liver enzymes and proteins.'),
('Kidney Function Test', 'Laboratory Tests', 'Evaluates creatinine, urea, and other kidney markers.'),
('Thyroid Function Test', 'Laboratory Tests', 'Measures TSH, T3, T4 hormones.'),
('Urine Analysis', 'Laboratory Tests', 'General urine test.'),
('Pregnancy Test (Blood)', 'Laboratory Tests', 'Detects hCG hormone in blood.'),
('X-Ray Chest', 'Imaging & Scans', 'Radiography of the chest.'),
('X-Ray Hand', 'Imaging & Scans', 'Radiography of the hand/arm.'),
('Abdominal Ultrasound', 'Imaging & Scans', 'Ultrasound of abdominal organs.'),
('Pelvic Ultrasound', 'Imaging & Scans', 'Ultrasound of pelvic organs.'),
('MRI Brain', 'Imaging & Scans', 'Magnetic resonance imaging of the brain.'),
('CT Scan Chest', 'Imaging & Scans', 'Computed tomography of the chest.');
