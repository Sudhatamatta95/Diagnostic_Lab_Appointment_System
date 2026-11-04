-- DiagnosCare V5 upgrade. Run this ONCE in the existing diagnostic_lab database.
USE diagnostic_lab;

ALTER TABLE users MODIFY role ENUM('admin','technician','staff','patient') NOT NULL DEFAULT 'patient';
ALTER TABLE payments MODIFY method ENUM('UPI','Card','Cash','Online') NOT NULL;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS gateway_order_id VARCHAR(80) NULL UNIQUE AFTER transaction_id;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS gateway_payment_id VARCHAR(80) NULL UNIQUE AFTER gateway_order_id;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS gateway_signature VARCHAR(255) NULL AFTER gateway_payment_id;

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    rating TINYINT NOT NULL,
    review_text VARCHAR(600) NOT NULL,
    status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (name,email,phone,password,role)
SELECT 'Lab Front Desk Staff','staff@diagnosticlab.test','9876543213',SHA2('staff123',256),'staff'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email='staff@diagnosticlab.test');
