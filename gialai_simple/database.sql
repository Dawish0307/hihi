-- =====================================================
-- GIALAI TOURISM DATABASE
-- Chạy file này trong phpMyAdmin
-- =====================================================

CREATE DATABASE IF NOT EXISTS gialai_tourism
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gialai_tourism;

-- Xóa bảng cũ nếu tồn tại (đúng thứ tự để tránh lỗi khóa ngoại)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS destinations;
DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- Bảng người dùng
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    phone      VARCHAR(20),
    role       ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng địa điểm (thêm lat, lng cho bản đồ)
CREATE TABLE destinations (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(200) NOT NULL,
    region      ENUM('bien','lichsu','rung') NOT NULL,
    location    VARCHAR(200),
    description TEXT,
    image_url   VARCHAR(500),
    rating      DECIMAL(2,1) DEFAULT 4.5,
    visit_count INT DEFAULT 0,
    lat         DECIMAL(10,7),
    lng         DECIMAL(10,7),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng đánh giá
CREATE TABLE reviews (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    destination_id INT NOT NULL,
    rating         INT CHECK (rating BETWEEN 1 AND 5),
    comment        TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)        REFERENCES users(id)        ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng sự kiện
CREATE TABLE events (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(200) NOT NULL,
    category    VARCHAR(50),
    location    VARCHAR(200),
    event_date  DATE,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng liên hệ
CREATE TABLE contacts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    phone      VARCHAR(20),
    subject    VARCHAR(100),
    message    TEXT NOT NULL,
    is_read    TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- DỮ LIỆU MẪU
-- =====================================================

-- Tài khoản admin (mật khẩu: 123456)
INSERT INTO users (full_name, email, password, role) VALUES
('Quản Trị Viên', 'admin@gialai.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Địa điểm du lịch (có toạ độ lat/lng chính xác)
INSERT INTO destinations (name, region, location, description, image_url, rating, visit_count, lat, lng) VALUES
(
    'Eo Gió - Kỳ Co',
    'bien',
    'Xã Nhơn Lý, Quy Nhơn, Bình Định',
    'Eo Gió và Kỳ Co nổi tiếng với làn nước trong xanh màu ngọc bích, những vách núi đá cao kỳ vĩ và bờ cát trắng mịn. Được ví như Maldives của Việt Nam, đây là điểm đến không thể bỏ qua khi đến Quy Nhơn.',
    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&q=80',
    4.9, 15420,
    13.7470, 109.2430
),
(
    'Ghềnh Ráng Tiên Sa',
    'bien',
    'Cách trung tâm Quy Nhơn 3km, Bình Định',
    'Quần thể bao gồm bãi đá Ghềnh Ráng kỳ thú, bãi tắm Hoàng Hậu thơ mộng và khu mộ thi sĩ Hàn Mặc Tử. Một trong những địa điểm lịch sử và cảnh quan đẹp nhất Quy Nhơn.',
    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80',
    4.7, 12300,
    13.7520, 109.2480
),
(
    'Hòn Khô - Làng Chài Nhơn Hải',
    'bien',
    'Xã Nhơn Hải, Quy Nhơn, Bình Định',
    'Hòn đảo nhỏ hoang sơ với con đường dài 500m giữa biển khi nước rút, kết nối với làng chài Nhơn Hải. Nước biển trong xanh lý tưởng cho lặn ngắm san hô và khám phá đời sống ngư dân.',
    'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&q=80',
    4.8, 9870,
    13.7150, 109.2580
),
(
    'Cù Lao Xanh',
    'bien',
    'Ngoài khơi biển Quy Nhơn, Bình Định',
    'Hòn đảo hoang sơ giữa biển khơi với làn nước trong vắt, bãi cát trắng mịn và cầu cảng gỗ vươn ra biển. Được ví như Maldives thu nhỏ, lý tưởng cho du lịch nghỉ dưỡng và lặn biển.',
    'https://images.unsplash.com/photo-1559494007-9f5847c49d94?w=800&q=80',
    4.8, 8650,
    13.6380, 109.3520
),
(
    'Đồi Cát Phương Mai',
    'bien',
    'Bán đảo Phương Mai, Quy Nhơn, Bình Định',
    'Được mệnh danh là Sahara của Quy Nhơn, đồi cát Phương Mai trải dài hàng chục kilômét với cát vàng óng ánh, phía xa là trời xanh và biển cả bao la. Điểm chụp ảnh cực đẹp.',
    'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=800&q=80',
    4.6, 7200,
    13.8200, 109.2750
),
(
    'Đầm Thị Nại',
    'bien',
    'Quy Nhơn, Bình Định',
    'Vùng nước mặn rộng lớn như một bức tranh thủy mặc sống động. Du khách có thể chèo SUP, thả lưới, ngắm chim trời và thưởng thức hải sản tươi sống giữa thiên nhiên hoang sơ.',
    'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=800&q=80',
    4.7, 6800,
    13.8650, 109.2100
),
(
    'Bãi Xép - Làng Chài',
    'bien',
    'Quy Hòa, Quy Nhơn, Bình Định',
    'Làng chài yên bình với bờ cát vàng mịn và rặng đá tự nhiên nhô lên mặt nước. Hơi thở cuộc sống ngư dân đậm chất miền biển, lý tưởng cho nhiếp ảnh và trải nghiệm văn hóa.',
    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&q=80',
    4.5, 5400,
    13.7300, 109.2350
),
(
    'Tháp Đôi Quy Nhơn',
    'lichsu',
    'Trung tâm thành phố Quy Nhơn, Bình Định',
    'Di tích Chăm Pa cổ kính được xây dựng từ cuối thế kỷ 11 đến đầu thế kỷ 13. Một trong những biểu tượng lịch sử và văn hóa đặc sắc nhất của vùng đất Bình Định, thu hút hàng ngàn du khách mỗi năm.',
    'https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=800&q=80',
    4.5, 11200,
    13.7760, 109.2230
),
(
    'Bảo Tàng Quang Trung - Tây Sơn',
    'lichsu',
    'Huyện Tây Sơn, Bình Định',
    'Nơi lưu giữ hiện vật quý giá tái hiện thời kỳ hào hùng của vua Quang Trung - Nguyễn Huệ. Điểm đến không thể bỏ lỡ cho những ai muốn tìm hiểu về hào khí Tây Sơn và lịch sử dân tộc.',
    'https://images.unsplash.com/photo-1532274402911-5a369e4c4bb5?w=800&q=80',
    4.7, 9300,
    13.9840, 108.8760
),
(
    'Tháp Bánh Ít',
    'lichsu',
    'Huyện Tuy Phước, Bình Định',
    'Cụm tháp Chăm Pa tọa lạc trên đồi cao với tầm nhìn bao quát toàn vùng. Là một trong những công trình kiến trúc cổ ấn tượng nhất còn được bảo tồn tốt, kiến trúc độc đáo và tinh xảo.',
    'https://images.unsplash.com/photo-1548013146-72479768bada?w=800&q=80',
    4.6, 7800,
    13.8580, 109.0640
),
(
    'Biển Hồ - Pleiku',
    'rung',
    'TP. Pleiku, Gia Lai',
    'Hồ nước ngọt rộng lớn nằm trong miệng núi lửa đã tắt, được bao quanh bởi rừng thông xanh mát. Buổi sáng sương mù bảng lảng, mặt hồ phẳng lặng như gương - khung cảnh thơ mộng hiếm có.',
    'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=800&q=80',
    4.7, 8900,
    13.9980, 108.0150
),
(
    'Vườn Quốc Gia Kon Ka Kinh',
    'rung',
    'Huyện Mang Yang, Gia Lai',
    'Khu dự trữ sinh quyển thế giới với hệ sinh thái rừng nguyên sinh phong phú. Nơi sinh sống của nhiều loài động thực vật quý hiếm, lý tưởng cho những chuyến trekking khám phá đại ngàn.',
    'https://images.unsplash.com/photo-1448375240586-882707db888b?w=800&q=80',
    4.8, 5600,
    14.1650, 108.3500
),
(
    'Thác Phú Cường',
    'rung',
    'Huyện Chư Sê, Gia Lai',
    'Thác nước hùng vĩ cao hàng chục mét đổ xuống giữa rừng nguyên sinh xanh thẳm. Không khí trong lành, tiếng nước chảy rì rào và khung cảnh thiên nhiên hoang sơ tạo nên trải nghiệm khó quên.',
    'https://images.unsplash.com/photo-1432405972618-c60b0225b8f9?w=800&q=80',
    4.6, 4300,
    13.6870, 108.0930
);

-- Sự kiện mẫu
INSERT INTO events (name, category, location, event_date, description) VALUES
('Festival Biển Quy Nhơn 2026',   'lehoi',   'Quy Nhơn, Bình Định',  '2026-06-28', 'Lễ hội biển lớn nhất miền Trung với các hoạt động văn hóa, thể thao biển và ẩm thực đặc sắc.'),
('Đêm Cồng Chiêng Tây Nguyên',    'vanhoa',  'Pleiku, Gia Lai',      '2026-07-15', 'Trình diễn di sản UNESCO cồng chiêng dưới bầu trời đêm đại ngàn huyền ảo.'),
('Giải Đua Thuyền Đầm Thị Nại',   'thethao', 'Đầm Thị Nại, QN',     '2026-08-20', 'Giải đua thuyền truyền thống trên Đầm Thị Nại sôi nổi và hấp dẫn.'),
('Tinh Hoa Ẩm Thực Gia Lai',      'amthuc',  'Quy Nhơn, Bình Định',  '2026-09-05', 'Hơn 60 gian hàng giới thiệu đặc sản từ phở khô Pleiku đến bánh xèo tôm nhảy Quy Nhơn.'),
('Tuần Lễ Biển Đảo Bình Định',    'khampha', 'Các đảo Quy Nhơn',    '2026-10-12', 'Tour khám phá Cù Lao Xanh, Hòn Khô, Eo Gió với lặn biển và trải nghiệm làng chài.'),
('Hành Trình Chăm Pa',            'vanhoa',  'Bình Định',            '2026-11-18', 'Hành trình khám phá hệ thống tháp Chăm Pa cổ kính trên đất Bình Định.');
