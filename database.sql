-- ============================================================================
-- HỆ THỐNG QUẢN LÝ TỔNG THỂ HÃNG XE (AUTOHUB DEALERSHIP MANAGEMENT SYSTEM)
-- DATABASE SQL SCRIPT - TỰ ĐỘNG KHỞI TẠO BẢNG & NẠP DỮ LIỆU MẪU
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `autohub` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `autohub`;

-- ----------------------------------------------------------------------------
-- 1. BẢNG NGƯỜI DÙNG & PHÂN QUYỀN (USERS & RBAC ROLES)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT 'Họ tên người dùng',
  `email` VARCHAR(100) UNIQUE NOT NULL COMMENT 'Email đăng nhập',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Mật khẩu mã hóa BCrypt',
  `role` VARCHAR(30) NOT NULL DEFAULT 'BAN_HANG' COMMENT 'Phân quyền: ADMIN, QUAN_LY, BAN_HANG, DICH_VU, KE_TOAN, KHO, NHAN_SU',
  `branch_id` INT DEFAULT 1 COMMENT 'ID chi nhánh',
  `status` VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT 'Trạng thái tài khoản',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tài khoản người dùng hệ thống';

-- Mật khẩu mặc định của tất cả các tài khoản demo là: password
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `branch_id`, `status`) VALUES
(1, 'Quản trị hệ thống', 'admin@autohub.vn', '$2y$10$e.wN0uWpFnqZ.1YJ3.66ue2/S0lQz.e7lQ84b2u5m3/5z5s5t5u5u', 'ADMIN', 1, 'active'),
(2, 'Trần Văn Quản Lý', 'quanly@autohub.vn', '$2y$10$e.wN0uWpFnqZ.1YJ3.66ue2/S0lQz.e7lQ84b2u5m3/5z5s5t5u5u', 'QUAN_LY', 1, 'active'),
(3, 'Nguyễn Văn Nam', 'banhang@autohub.vn', '$2y$10$e.wN0uWpFnqZ.1YJ3.66ue2/S0lQz.e7lQ84b2u5m3/5z5s5t5u5u', 'BAN_HANG', 1, 'active'),
(4, 'Nguyễn Thành Đạt', 'dichvu@autohub.vn', '$2y$10$e.wN0uWpFnqZ.1YJ3.66ue2/S0lQz.e7lQ84b2u5m3/5z5s5t5u5u', 'DICH_VU', 1, 'active'),
(5, 'Lê Thị Kế Toán', 'ketoan@autohub.vn', '$2y$10$e.wN0uWpFnqZ.1YJ3.66ue2/S0lQz.e7lQ84b2u5m3/5z5s5t5u5u', 'KE_TOAN', 1, 'active'),
(6, 'Phạm Văn Kho', 'kho@autohub.vn', '$2y$10$e.wN0uWpFnqZ.1YJ3.66ue2/S0lQz.e7lQ84b2u5m3/5z5s5t5u5u', 'KHO', 1, 'active'),
(7, 'Hoàng Thị Nhân Sự', 'nhansu@autohub.vn', '$2y$10$e.wN0uWpFnqZ.1YJ3.66ue2/S0lQz.e7lQ84b2u5m3/5z5s5t5u5u', 'NHAN_SU', 1, 'active');


-- ----------------------------------------------------------------------------
-- 2. BẢNG KHÁCH HÀNG CRM (CUSTOMERS)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) UNIQUE NOT NULL COMMENT 'Mã khách hàng',
  `name` VARCHAR(100) NOT NULL COMMENT 'Họ tên',
  `phone` VARCHAR(20) NOT NULL COMMENT 'Số điện thoại',
  `email` VARCHAR(100) NULL COMMENT 'Email',
  `address` VARCHAR(255) NULL COMMENT 'Địa chỉ',
  `source` VARCHAR(50) DEFAULT 'Showroom' COMMENT 'Nguồn khách hàng',
  `interested_car` VARCHAR(100) NULL COMMENT 'Xe quan tâm',
  `assigned_to` VARCHAR(100) DEFAULT 'Nguyễn Văn Nam' COMMENT 'Nhân viên phụ trách',
  `status` VARCHAR(30) DEFAULT 'Mới' COMMENT 'Mới, Đang tư vấn, Đã đặt cọc, Đã mua',
  `notes` TEXT NULL COMMENT 'Ghi chú nhu cầu',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hồ sơ khách hàng CRM';

INSERT INTO `customers` (`id`, `code`, `name`, `phone`, `email`, `address`, `source`, `interested_car`, `assigned_to`, `status`, `notes`) VALUES
(1, 'KH-1001', 'Trần Minh Hoàng', '0912345678', 'hoang.tm@gmail.com', '123 Nguyễn Huệ, Q.1, TP.HCM', 'Trang web', 'AutoHub Luxe Sedan V8', 'Nguyễn Văn Nam', 'Đã đặt cọc', 'Khách muốn màu đen, giao xe tháng 10/2026'),
(2, 'KH-1002', 'Lê Thị Thu Thủy', '0908765432', 'thuy.le@yahoo.com', '45 Lê Lợi, Q.1, TP.HCM', 'Showroom', 'AutoHub EV Cross', 'Nguyễn Văn Nam', 'Đang tư vấn', 'Quan tâm gói bảo hành pin 8 năm'),
(3, 'KH-1003', 'Phạm Quốc Cường', '0933112233', 'cuong.pq@corp.vn', '88 Nam Kỳ Khởi Nghĩa, Q.3, TP.HCM', 'Giới thiệu', 'AutoHub Sport Coupe R', 'Nguyễn Văn Nam', 'Đã mua', 'Khách hàng VIP mua chiếc thứ 2'),
(4, 'KH-1004', 'Nguyễn Bích Ngọc', '0988776655', 'bichngoc@outlook.com', '12 Thảo Điền, TP. Thủ Đức', 'Mạng xã hội', 'AutoHub Urban Compact', 'Nguyễn Văn Nam', 'Mới', 'Đã đăng ký lái thử cuối tuần'),
(5, 'KH-1005', 'Đặng Anh Tuấn', '0971234888', 'tuan.dang@tech.com', '56 Nguyễn Thị Minh Khai, Q.3', 'Showroom', 'AutoHub Luxe Sedan V8', 'Nguyễn Văn Nam', 'Đang tư vấn', 'Yêu cầu phương án trả góp 70%');


-- ----------------------------------------------------------------------------
-- 3. BẢNG NHẬT KÝ HOẠT ĐỘNG CRM (ACTIVITIES)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `activities`;
CREATE TABLE `activities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NULL,
  `type` VARCHAR(30) DEFAULT 'call',
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `created_by` VARCHAR(100) DEFAULT 'Hệ thống',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký tương tác chăm sóc khách hàng';

INSERT INTO `activities` (`id`, `customer_id`, `type`, `title`, `description`, `created_by`) VALUES
(1, 1, 'deposit', 'Nhận đặt cọc mua xe', 'Đã thu cọc 200.000.000 ₫ cho mẫu xe AutoHub Luxe Sedan V8.', 'Nguyễn Văn Nam'),
(2, 4, 'test_drive', 'Đăng ký lái thử thành công', 'Đã xếp lịch lái thử mẫu AutoHub Urban Compact ngày 10/10/2026.', 'Nguyễn Văn Nam'),
(3, 3, 'service', 'Hoàn tất lệnh sửa chữa RO-2026-001', 'Bảo dưỡng 15.000km hoàn tất, xe đã giao lại cho khách hàng.', 'Nguyễn Thành Đạt'),
(4, 2, 'consulting', 'Gửi báo giá AutoHub EV Cross', 'Đã gửi bảng tính khoản vay trả góp ngân hàng 70%.', 'Nguyễn Văn Nam');


-- ----------------------------------------------------------------------------
-- 4. BẢNG KHO XE & MODEL & MÃ VIN (VEHICLES)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) UNIQUE NOT NULL COMMENT 'Mã xe',
  `brand` VARCHAR(50) NOT NULL COMMENT 'Hãng xe',
  `model` VARCHAR(100) NOT NULL COMMENT 'Model xe',
  `version` VARCHAR(50) NULL COMMENT 'Phiên bản',
  `year` INT DEFAULT 2026 COMMENT 'Năm sản xuất',
  `color` VARCHAR(30) DEFAULT 'Đen' COMMENT 'Màu sắc',
  `vin` VARCHAR(50) UNIQUE NOT NULL COMMENT 'Mã số khung VIN',
  `engine_no` VARCHAR(50) NULL COMMENT 'Số máy',
  `price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Giá niêm yết',
  `cost_price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Giá vốn nhập',
  `status` VARCHAR(30) DEFAULT 'Có sẵn' COMMENT 'Có sẵn, Đã giữ chỗ, Đã bán, Đang vận chuyển',
  `location` VARCHAR(50) DEFAULT 'Kho A' COMMENT 'Vị trí bãi kho',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kho xe & Thông tin VIN';

INSERT INTO `vehicles` (`id`, `code`, `brand`, `model`, `version`, `year`, `color`, `vin`, `price`, `cost_price`, `status`, `location`) VALUES
(1, 'XE-001', 'AutoHub', 'Luxe Sedan V8', 'Presidential', 2026, 'Đen Obsidian', 'VN8839201920311', 2490000000.00, 1950000000.00, 'Có sẵn', 'Showroom chính'),
(2, 'XE-002', 'AutoHub', 'EV Cross', 'Ultra Long Range', 2026, 'Trắng Pearl', 'VN8839201920312', 1790000000.00, 1420000000.00, 'Có sẵn', 'Kho B'),
(3, 'XE-003', 'AutoHub', 'Sport Coupe R', 'Track Edition', 2026, 'Đỏ Racing', 'VN8839201920313', 3190000000.00, 2500000000.00, 'Đã giữ chỗ', 'Showroom chính'),
(4, 'XE-004', 'AutoHub', 'Urban Compact', 'City Plus', 2026, 'Xanh Satin', 'VN8839201920314', 890000000.00, 680000000.00, 'Có sẵn', 'Kho A'),
(5, 'XE-005', 'AutoHub', 'Luxe SUV 7-Seat', 'Executive', 2026, 'Xám Titan', 'VN8839201920315', 2890000000.00, 2250000000.00, 'Đã bán', 'Khách hàng'),
(6, 'XE-006', 'AutoHub', 'EV Cross', 'Standard Range', 2026, 'Đen Obsidian', 'VN8839201920316', 1490000000.00, 1180000000.00, 'Đang vận chuyển', 'Logistics');


-- ----------------------------------------------------------------------------
-- 5. BẢNG CƠ HỘI BÁN HÀNG & KANBAN (SALES_OPPORTUNITIES)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `sales_opportunities`;
CREATE TABLE `sales_opportunities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `vehicle_id` INT NULL,
  `employee_id` INT NULL,
  `stage` VARCHAR(50) DEFAULT 'Khách hàng mới' COMMENT 'Khách hàng mới, Đã liên hệ / lái thử, Đang thương lượng, Đã chốt',
  `value` DECIMAL(15,2) DEFAULT 0,
  `probability` INT DEFAULT 50,
  `follow_up` DATE NULL,
  `note` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cơ hội bán hàng pipeline Kanban';

INSERT INTO `sales_opportunities` (`id`, `customer_id`, `vehicle_id`, `employee_id`, `stage`, `value`, `probability`, `follow_up`, `note`) VALUES
(1, 1, 1, 1, 'Đã chốt', 2490000000.00, 100, '2026-10-15', 'Khách đã cọc 200 triệu, chuẩn bị làm hợp đồng mua bán'),
(2, 2, 2, 1, 'Đang thương lượng', 1790000000.00, 75, '2026-10-10', 'Khách yêu cầu hỗ trợ gói bảo dưỡng 3 năm'),
(3, 4, 4, 2, 'Khách hàng mới', 890000000.00, 30, '2026-10-08', 'Hẹn lái thử tại showroom vào thứ 7'),
(4, 5, 1, 2, 'Đã liên hệ / lái thử', 2490000000.00, 60, '2026-10-12', 'Khách hài lòng sau lái thử, cân nhắc màu sơn');


-- ----------------------------------------------------------------------------
-- 6. BẢNG LỊCH LÁI THỬ XE (TEST_DRIVES)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `test_drives`;
CREATE TABLE `test_drives` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `vehicle_id` INT NOT NULL,
  `employee_id` INT NULL,
  `drive_date` DATE NOT NULL,
  `drive_time` TIME NOT NULL,
  `status` VARCHAR(30) DEFAULT 'Đã đăng ký',
  `notes` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lịch hẹn lái thử xe';

INSERT INTO `test_drives` (`id`, `customer_id`, `vehicle_id`, `employee_id`, `drive_date`, `drive_time`, `status`, `notes`) VALUES
(1, 4, 4, 1, '2026-10-10', '09:30:00', 'Đã đăng ký', 'Lái thử tuyến Thảo Điền - Cao tốc'),
(2, 2, 2, 2, '2026-10-05', '14:00:00', 'Đã hoàn tất', 'Khách ấn tượng với khả năng tăng tốc xe điện EV');


-- ----------------------------------------------------------------------------
-- 7. BẢNG KHO PHỤ TÙNG (PARTS) & GIAO DỊCH XUẤT NHẬP KHO
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `parts`;
CREATE TABLE `parts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sku` VARCHAR(50) UNIQUE NOT NULL COMMENT 'Mã SKU',
  `name` VARCHAR(150) NOT NULL COMMENT 'Tên phụ tùng',
  `category` VARCHAR(50) DEFAULT 'Phụ tùng chung',
  `unit` VARCHAR(20) DEFAULT 'Cái',
  `price` DECIMAL(12,2) DEFAULT 0 COMMENT 'Đơn giá niêm yết',
  `cost_price` DECIMAL(12,2) DEFAULT 0 COMMENT 'Giá vốn nhập',
  `stock` INT DEFAULT 0 COMMENT 'Số lượng tồn kho',
  `min_stock` INT DEFAULT 5 COMMENT 'Ngưỡng tồn tối thiểu',
  `supplier` VARCHAR(100) NULL COMMENT 'Nhà cung cấp',
  `location` VARCHAR(50) DEFAULT 'Kệ A1' COMMENT 'Vị trí kệ kho',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kho phụ tùng & Vật tư';

INSERT INTO `parts` (`id`, `sku`, `name`, `category`, `unit`, `price`, `cost_price`, `stock`, `min_stock`, `supplier`, `location`) VALUES
(1, 'PT-OIL-5W30', 'Dầu động cơ tổng hợp AutoHub Synthetic 5W-30 (4L)', 'Dầu nhớt', 'Can', 850000.00, 520000.00, 45, 10, 'AutoHub OEM Parts', 'Kệ A-01'),
(2, 'PT-PAD-FRONT', 'Má phanh trước Ceramic Luxe Series', 'Hệ thống phanh', 'Bộ', 3200000.00, 2100000.00, 3, 5, 'Brembo Việt Nam', 'Kệ B-04'),
(3, 'PT-FLT-AIR', 'Lọc gió động cơ High Performance', 'Hệ thống lọc', 'Cái', 450000.00, 260000.00, 18, 8, 'Denso Auto', 'Kệ C-02'),
(4, 'PT-FLT-CABIN', 'Lọc gió điều hòa than hoạt tính HEPA', 'Hệ thống lọc', 'Cái', 650000.00, 380000.00, 2, 5, 'Denso Auto', 'Kệ C-03'),
(5, 'PT-TIRE-20', 'Lốp xe Performance Pilot Sport 245/45 R20', 'Lốp xe & Mâm', 'Quả', 6800000.00, 4900000.00, 12, 4, 'Michelin VN', 'Kệ D-01'),
(6, 'PT-BAT-12V', 'Bình ắc quy AGM 12V 80Ah AutoHub Power', 'Điện - Ắc quy', 'Bình', 4200000.00, 2900000.00, 6, 3, 'Varta Batteries', 'Kệ E-02');

DROP TABLE IF EXISTS `part_transactions`;
CREATE TABLE `part_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `part_id` INT NOT NULL,
  `type` VARCHAR(10) NOT NULL COMMENT 'IN / OUT',
  `qty` INT NOT NULL,
  `unit_price` DECIMAL(12,2) DEFAULT 0,
  `note` TEXT NULL,
  `created_by` VARCHAR(100) DEFAULT 'Hệ thống',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký Nhập / Xuất phụ tùng';


-- ----------------------------------------------------------------------------
-- 8. BẢNG DỊCH VỤ / SỬA CHỮA (REPAIR_ORDERS & REPAIR_ITEMS)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `repair_orders`;
CREATE TABLE `repair_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) UNIQUE NOT NULL COMMENT 'Mã lệnh RO',
  `customer_id` INT NOT NULL,
  `vehicle_id` INT NOT NULL,
  `advisor` VARCHAR(100) DEFAULT 'Nguyễn Thành Đạt' COMMENT 'Cố vấn dịch vụ',
  `technician` VARCHAR(100) DEFAULT 'Trần Quốc Bình' COMMENT 'Kỹ thuật viên',
  `mileage` INT DEFAULT 0 COMMENT 'Số KM',
  `status` VARCHAR(30) DEFAULT 'Mới' COMMENT 'Mới, Đã tiếp nhận, Đang kiểm tra, Đang sửa chữa, Chờ phụ tùng, Hoàn tất, Đã bàn giao',
  `description` TEXT NULL,
  `subtotal` DECIMAL(15,2) DEFAULT 0,
  `vat` DECIMAL(15,2) DEFAULT 0,
  `discount` DECIMAL(15,2) DEFAULT 0,
  `total` DECIMAL(15,2) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phiếu / Lệnh sửa chữa';

INSERT INTO `repair_orders` (`id`, `code`, `customer_id`, `vehicle_id`, `advisor`, `technician`, `mileage`, `status`, `description`, `subtotal`, `vat`, `discount`, `total`) VALUES
(1, 'RO-2026-001', 3, 5, 'Nguyễn Thành Đạt', 'Trần Quốc Bình', 15200, 'Hoàn tất', 'Bảo dưỡng cấp 15.000km, thay dầu nhớt, lọc gió và kiểm tra phanh', 4500000.00, 450000.00, 200000.00, 4750000.00),
(2, 'RO-2026-002', 1, 1, 'Nguyễn Thành Đạt', 'Trần Quốc Bình', 8500, 'Đang sửa chữa', 'Thay má phanh trước và kiểm tra cân chỉnh thước lái', 3650000.00, 365000.00, 0.00, 4015000.00);

DROP TABLE IF EXISTS `repair_items`;
CREATE TABLE `repair_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `repair_order_id` INT NOT NULL,
  `type` VARCHAR(20) DEFAULT 'Công việc' COMMENT 'Công việc / Phụ tùng',
  `code` VARCHAR(50) NULL,
  `name` VARCHAR(150) NOT NULL,
  `qty` INT DEFAULT 1,
  `unit_price` DECIMAL(12,2) DEFAULT 0,
  `total` DECIMAL(15,2) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chi tiết công việc & phụ tùng sửa chữa';

INSERT INTO `repair_items` (`id`, `repair_order_id`, `type`, `code`, `name`, `qty`, `unit_price`, `total`) VALUES
(1, 1, 'Phụ tùng', 'PT-OIL-5W30', 'Dầu động cơ AutoHub 5W-30', 2, 850000.00, 1700000.00),
(2, 1, 'Phụ tùng', 'PT-FLT-AIR', 'Lọc gió động cơ High Performance', 1, 450000.00, 450000.00),
(3, 1, 'Công việc', 'CV-BD-15K', 'Công bảo dưỡng định kỳ 15.000km', 1, 2350000.00, 2350000.00),
(4, 2, 'Phụ tùng', 'PT-PAD-FRONT', 'Má phanh trước Ceramic Luxe Series', 1, 3200000.00, 3200000.00),
(5, 2, 'Công việc', 'CV-TL-01', 'Công kiểm tra & cân chỉnh góc đặt bánh xe', 1, 450000.00, 450000.00);


-- ----------------------------------------------------------------------------
-- 9. BẢNG KẾ TOÁN & HÓA ĐƠN & PHIẾU THU CHI (INVOICES, PAYMENTS, EXPENSES)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) UNIQUE NOT NULL COMMENT 'Mã hóa đơn',
  `customer_id` INT NOT NULL,
  `type` VARCHAR(30) DEFAULT 'Bán xe' COMMENT 'Bán xe, Dịch vụ, Phụ tùng',
  `subtotal` DECIMAL(15,2) DEFAULT 0,
  `vat` DECIMAL(15,2) DEFAULT 0,
  `discount` DECIMAL(15,2) DEFAULT 0,
  `total` DECIMAL(15,2) DEFAULT 0,
  `payment_status` VARCHAR(30) DEFAULT 'Chưa thanh toán' COMMENT 'Chưa thanh toán, Một phần, Đã thanh toán, Quá hạn',
  `issued_at` DATE NOT NULL,
  `due_date` DATE NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hóa đơn kế toán';

INSERT INTO `invoices` (`id`, `code`, `customer_id`, `type`, `subtotal`, `vat`, `discount`, `total`, `payment_status`, `issued_at`, `due_date`) VALUES
(1, 'INV-2026-001', 3, 'Bán xe', 2890000000.00, 0.00, 50000000.00, 2840000000.00, 'Đã thanh toán', '2026-09-25', '2026-10-05'),
(2, 'INV-2026-002', 1, 'Bán xe', 200000000.00, 0.00, 0.00, 200000000.00, 'Đã thanh toán', '2026-10-01', '2026-10-01'),
(3, 'INV-2026-003', 3, 'Dịch vụ', 4500000.00, 450000.00, 200000.00, 4750000.00, 'Đã thanh toán', '2026-10-04', '2026-10-04'),
(4, 'INV-2026-004', 2, 'Dịch vụ', 3650000.00, 365000.00, 0.00, 4015000.00, 'Chưa thanh toán', '2026-10-06', '2026-10-13');

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT NOT NULL,
  `code` VARCHAR(30) UNIQUE NOT NULL COMMENT 'Mã phiếu thu',
  `amount` DECIMAL(15,2) DEFAULT 0,
  `method` VARCHAR(30) DEFAULT 'Chuyển khoản',
  `reference` VARCHAR(100) NULL,
  `paid_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phiếu thu tiền thanh toán';

INSERT INTO `payments` (`id`, `invoice_id`, `code`, `amount`, `method`, `reference`) VALUES
(1, 1, 'PAY-2026-001', 2840000000.00, 'Chuyển khoản', 'FT26268819201'),
(2, 2, 'PAY-2026-002', 200000000.00, 'Chuyển khoản', 'FT26275019822'),
(3, 3, 'PAY-2026-003', 4750000.00, 'Thẻ', 'POS-882910');

DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) UNIQUE NOT NULL COMMENT 'Mã phiếu chi',
  `category` VARCHAR(50) DEFAULT 'Chi phí vận hành',
  `amount` DECIMAL(15,2) DEFAULT 0,
  `recipient` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phiếu chi kế toán';


-- ----------------------------------------------------------------------------
-- 10. BẢNG NHÂN SỰ & KPI (EMPLOYEES)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) UNIQUE NOT NULL COMMENT 'Mã nhân viên',
  `name` VARCHAR(100) NOT NULL COMMENT 'Họ tên',
  `phone` VARCHAR(20) NULL COMMENT 'Số điện thoại',
  `email` VARCHAR(100) NULL COMMENT 'Email',
  `department` VARCHAR(50) DEFAULT 'Kinh doanh' COMMENT 'Phòng ban',
  `position` VARCHAR(50) DEFAULT 'Tư vấn bán hàng' COMMENT 'Chức vụ',
  `salary` DECIMAL(12,2) DEFAULT 15000000 COMMENT 'Lương cơ bản',
  `target` DECIMAL(15,2) DEFAULT 3000000000 COMMENT 'Mục tiêu KPI (₫)',
  `result` DECIMAL(15,2) DEFAULT 0 COMMENT 'Kết quả đạt được (₫)',
  `status` VARCHAR(20) DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hồ sơ nhân sự & Chỉ tiêu KPI';

INSERT INTO `employees` (`id`, `code`, `name`, `phone`, `email`, `department`, `position`, `salary`, `target`, `result`) VALUES
(1, 'NV-001', 'Nguyễn Văn Nam', '0901112233', 'nam.nv@autohub.vn', 'Kinh doanh', 'Trưởng phòng kinh doanh', 35000000.00, 6000000000.00, 5680000000.00),
(2, 'NV-002', 'Trần Thị Hà', '0902223344', 'ha.tt@autohub.vn', 'Kinh doanh', 'Chuyên viên tư vấn', 20000000.00, 3500000000.00, 3190000000.00),
(3, 'NV-003', 'Nguyễn Thành Đạt', '0903334455', 'dat.nt@autohub.vn', 'Dịch vụ', 'Cố vấn dịch vụ trưởng', 25000000.00, 2500000000.00, 2380000000.00),
(4, 'NV-004', 'Trần Quốc Bình', '0904445566', 'binh.tq@autohub.vn', 'Kỹ thuật', 'Kỹ thuật viên trưởng', 22000000.00, 1500000000.00, 1420000000.00),
(5, 'NV-005', 'Lê Thị Kế Toán', '0905556677', 'toan.lt@autohub.vn', 'Kế toán', 'Kế toán trưởng', 28000000.00, 0.00, 0.00);


-- ----------------------------------------------------------------------------
-- 11. BẢNG THÔNG BÁO & AUDIT LOGS
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông báo hệ thống';

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`) VALUES
(1, 1, 'Khách hàng cọc xe mới', 'Khách hàng Trần Minh Hoàng đã chuyển cọc 200.000.000 ₫ cho xe AutoHub Luxe Sedan V8', 0),
(2, 1, 'Cảnh báo phụ tùng tồn thấp', 'Má phanh trước Ceramic Luxe Series (PT-PAD-FRONT) còn 3 bộ (tối thiểu 5)', 0),
(3, 1, 'Lịch lái thử mới', 'Khách hàng Nguyễn Bích Ngọc đặt lịch lái thử lúc 09:30 ngày 10/10/2026', 1);

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(50) NOT NULL,
  `entity` VARCHAR(50) NOT NULL,
  `entity_id` INT NULL,
  `old_data` JSON NULL,
  `new_data` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký thao tác Audit Logs';

-- ============================================================================
-- HOÀN TẤT KHỞI TẠO CƠ SỞ DỮ LIỆU AUTOHUB
-- ============================================================================
