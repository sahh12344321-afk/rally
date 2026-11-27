-- ==========================================
-- قاعدة بيانات مشروع Rally لحجز ملاعب البادل
-- الاستيراد: phpMyAdmin > Import > اختاري هذا الملف
-- ==========================================

CREATE DATABASE IF NOT EXISTS rally CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rally;

DROP TABLE IF EXISTS players;
DROP TABLE IF EXISTS booking_slots;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS courts;

-- جدول الملاعب
CREATE TABLE courts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type VARCHAR(20) NOT NULL,
  location VARCHAR(150) NOT NULL,
  image VARCHAR(150) NOT NULL,
  price_hour INT NOT NULL,
  open_hour INT NOT NULL,
  close_hour INT NOT NULL
);

-- جدول الحجوزات
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL,
  court_id INT NOT NULL,
  booking_date DATE NOT NULL,
  start_hour INT NOT NULL,
  hours INT NOT NULL,
  owner_name VARCHAR(100) NOT NULL,
  owner_phone VARCHAR(15) NOT NULL,
  total INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول الساعات المحجوزة: صف لكل ساعة
-- UNIQUE يمنع حجز نفس الساعة في نفس الملعب مرتين
CREATE TABLE booking_slots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  court_id INT NOT NULL,
  slot_date DATE NOT NULL,
  slot_hour INT NOT NULL,
  UNIQUE KEY unique_slot (court_id, slot_date, slot_hour)
);

-- جدول اللاعبين وتقسيم المبلغ
CREATE TABLE players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  share DECIMAL(8,2) NOT NULL,
  paid TINYINT NOT NULL DEFAULT 0
);

-- ========== بيانات تجريبية ==========
INSERT INTO courts (name, type, location, image, price_hour, open_hour, close_hour) VALUES
('ملعب الصقر',   'بانوراما', 'حي الحوية، الطائف',     'court1.svg', 160, 16, 24),
('ملعب النخبة',  'داخلي',    'حي شهار، الطائف',       'court2.svg', 140, 14, 24),
('ملعب الواحة',  'خارجي',    'حي الفيصلية، الطائف',   'court3.svg', 100, 16, 23),
('ملعب القمة',   'بانوراما', 'حي الحوية، الطائف',     'court4.svg', 180, 15, 24),
('ملعب الأفق',   'داخلي',    'حي شبرا، الطائف',       'court5.svg', 130, 14, 23),
('ملعب الريان',  'خارجي',    'حي الخالدية، الطائف',   'court6.svg',  90, 17, 23);

-- حجوزات تجريبية (اليوم وغدًا)
INSERT INTO bookings (code, court_id, booking_date, start_hour, hours, owner_name, owner_phone, total) VALUES
('RLY-DEMO-01', 1, CURDATE(),                       22, 2, 'خالد العتيبي', '0551234567', 320),
('RLY-DEMO-02', 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 18, 1, 'سارة الحربي',  '0559876543', 160),
('RLY-DEMO-03', 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 20, 3, 'فهد الثقفي',   '0541112233', 420);

INSERT INTO booking_slots (booking_id, court_id, slot_date, slot_hour) VALUES
(1, 1, CURDATE(), 22), (1, 1, CURDATE(), 23),
(2, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 18),
(3, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 20),
(3, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 21),
(3, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 22);

INSERT INTO players (booking_id, name, share, paid) VALUES
(1, 'خالد العتيبي', 80.00, 1),
(1, 'ماجد',          80.00, 1),
(1, 'سلطان',         80.00, 0),
(1, 'تركي',          80.00, 0),
(2, 'سارة الحربي',  160.00, 0),
(3, 'فهد الثقفي',   210.00, 1),
(3, 'نايف',         210.00, 0);
