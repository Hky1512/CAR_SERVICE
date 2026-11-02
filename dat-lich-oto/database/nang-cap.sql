-- Chỉ dành cho người đã import schema của bản CAR SERVICE trước.
-- Sao lưu database trước khi chạy. Chạy một lần sau đó import seed.sql.
USE dat_lich_oto;

SET @has_price = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dich_vu' AND COLUMN_NAME = 'gia_tham_khao');
SET @sql = IF(@has_price = 0, 'ALTER TABLE dich_vu ADD COLUMN gia_tham_khao DECIMAL(12,0) NULL AFTER mo_ta', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_time = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dich_vu' AND COLUMN_NAME = 'thoi_gian_du_kien');
SET @sql = IF(@has_time = 0, 'ALTER TABLE dich_vu ADD COLUMN thoi_gian_du_kien SMALLINT UNSIGNED NULL AFTER gia_tham_khao', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Phụ tùng chỉ là danh mục tham khảo. Không triển khai giỏ hàng hoặc thanh toán.
CREATE TABLE IF NOT EXISTS phu_tung (
  ma_phu_tung INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ten_phu_tung VARCHAR(160) NOT NULL UNIQUE,
  hang_san_xuat VARCHAR(100) NOT NULL DEFAULT '',
  mo_ta TEXT NULL,
  gia_tham_khao DECIMAL(12,0) NULL,
  hinh_anh VARCHAR(500) NULL,
  con_hang TINYINT(1) NOT NULL DEFAULT 1,
  dang_hien_thi TINYINT(1) NOT NULL DEFAULT 1,
  ngay_tao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ngay_cap_nhat TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tiếp nhận yêu cầu hỗ trợ; không phải hệ thống điều phối cứu hộ GPS.
CREATE TABLE IF NOT EXISTS yeu_cau_cuu_ho (
  ma_yeu_cau INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ma_tra_cuu VARCHAR(32) NOT NULL UNIQUE,
  ma_nguoi_dung INT UNSIGNED NULL,
  ho_ten VARCHAR(120) NOT NULL,
  so_dien_thoai VARCHAR(20) NOT NULL,
  dia_diem VARCHAR(300) NOT NULL,
  loai_su_co VARCHAR(120) NOT NULL,
  mo_ta VARCHAR(1000) NULL,
  trang_thai VARCHAR(40) NOT NULL DEFAULT 'Mới tiếp nhận',
  ngay_tao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ngay_cap_nhat TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cuu_ho_nguoi_dung FOREIGN KEY (ma_nguoi_dung)
    REFERENCES nguoi_dung(ma_nguoi_dung) ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_cuu_ho_nguoi_dung (ma_nguoi_dung),
  INDEX idx_cuu_ho_trang_thai (trang_thai),
  INDEX idx_cuu_ho_ngay_tao (ngay_tao)
) ENGINE=InnoDB;

-- Nâng cấp schema 7 bảng cũ: bổ sung liên kết cứu hộ -> người dùng khi có đăng nhập.
SET @has_cuu_ho_user = (SELECT COUNT(*) FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'yeu_cau_cuu_ho' AND COLUMN_NAME = 'ma_nguoi_dung');
SET @sql = IF(@has_cuu_ho_user = 0,
 'ALTER TABLE yeu_cau_cuu_ho ADD COLUMN ma_nguoi_dung INT UNSIGNED NULL AFTER ma_tra_cuu',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_cuu_ho_fk = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'yeu_cau_cuu_ho'
   AND CONSTRAINT_NAME = 'fk_cuu_ho_nguoi_dung' AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql = IF(@has_cuu_ho_fk = 0,
 'ALTER TABLE yeu_cau_cuu_ho ADD CONSTRAINT fk_cuu_ho_nguoi_dung FOREIGN KEY (ma_nguoi_dung) REFERENCES nguoi_dung(ma_nguoi_dung) ON UPDATE CASCADE ON DELETE SET NULL',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
