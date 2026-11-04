CREATE DATABASE IF NOT EXISTS diagnostic_lab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE diagnostic_lab;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS reports, test_results, payments, appointments, diagnostic_tests, users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','technician','patient') NOT NULL DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE diagnostic_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category VARCHAR(80) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    duration_minutes INT NOT NULL DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    test_id INT NOT NULL,
    technician_id INT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('Booked','Confirmed','Arrived','In Progress','Completed','Cancelled') DEFAULT 'Booked',
    notes VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES diagnostic_tests(id) ON DELETE RESTRICT,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_patient_slot (patient_id, appointment_date, appointment_time)
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('UPI','Card','Cash') NOT NULL,
    transaction_id VARCHAR(80) NOT NULL UNIQUE,
    status ENUM('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);

CREATE TABLE technicians (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  details TEXT,
  picture VARCHAR(255),
  certificate VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL UNIQUE,
    technician_id INT NOT NULL,
    sample_status ENUM('Pending','Collected','Rejected') DEFAULT 'Pending',
    result_status ENUM('Pending','Processing','Completed') DEFAULT 'Pending',
    result_text TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE RESTRICT
);

CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL UNIQUE,
    report_title VARCHAR(150) NOT NULL,
    report_content TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- Demo accounts. Passwords:
-- admin123 / tech123 / patient123
INSERT INTO users (name,email,phone,password,role) VALUES
('System Admin','admin@diagnosticlab.test','9876543210',SHA2('admin123',256),'admin'),
('Lab Technician','technician@diagnosticlab.test','9876543211',SHA2('tech123',256),'technician'),
('Demo Patient','patient@diagnosticlab.test','9876543212',SHA2('patient123',256),'patient');

INSERT INTO diagnostic_tests (name,category,description,price,duration_minutes) VALUES
('Complete Blood Count (CBC)','Hematology','Measures red cells, white cells, hemoglobin and platelets.',450,30),
('Blood Sugar - Fasting','Biochemistry','Measures fasting blood glucose level.',150,15),
('Lipid Profile','Biochemistry','Checks cholesterol and triglyceride levels.',650,30),
('Thyroid Profile','Hormone','Basic thyroid function screening.',700,45),
('Liver Function Test','Biochemistry','Evaluates major liver function markers.',900,45),
('Kidney Function Test','Biochemistry','Evaluates kidney-related biochemical markers.',850,45),
('Urine Routine','Pathology','Routine microscopic and chemical urine examination.',200,20),
('Vitamin D','Vitamin','Measures vitamin D level in blood.',1000,30);

INSERT INTO appointments (patient_id,test_id,appointment_date,appointment_time,status,notes)
VALUES (3,1,CURDATE(),'10:00:00','Confirmed','Demo appointment');

INSERT INTO payments (appointment_id,amount,method,transaction_id,status,paid_at)
VALUES (1,450,'UPI','TXN-DEMO-1001','Paid',NOW());
