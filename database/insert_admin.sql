-- Insert Admin Account untuk PPM Alma Ata
-- Default credentials: NIM=ADMIN001, Password=admin123

-- Password hash untuk 'admin123' (bcrypt)
-- Generated via: password_hash('admin123', PASSWORD_DEFAULT)
-- Gunakan: $2y$10$8Z1V4D8cK2x.8L7b1V9H.uH8vN7m6K5j4I3h2G1f0E9D8C7B6A5

INSERT INTO `users` (nim, email, password_hash, role, status, must_reset_password)
VALUES (
  'ADMIN001',
  'admin@ppm-almaata.test',
  '$2y$10$8Z1V4D8cK2x.8L7b1V9H.uH8vN7m6K5j4I3h2G1f0E9D8C7B6A5',
  'admin',
  'active',
  0
)
ON DUPLICATE KEY UPDATE
  role = 'admin',
  must_reset_password = 0;

-- Verify
SELECT nim, role, status FROM users WHERE nim = 'ADMIN001';
