CREATE DATABASE IF NOT EXISTS dat_lich_oto
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE dat_lich_oto;

CREATE TABLE IF NOT EXISTS nguoi_dung (
  ma_nguoi_dung INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ho_ten VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  so_dien_thoai VARCHAR(20) NOT NULL,
  mat_khau_bam VARCHAR(255) NOT NULL,
  vai_tro VARCHAR(30) NOT NULL DEFAULT 'khach_hang',
  trang_thai_tai_khoan VARCHAR(30) NOT NULL DEFAULT 'hoat_dong',
  ngay_tao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ngay_cap_nhat TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_nguoi_dung_vai_tro (vai_tro),
  INDEX idx_nguoi_dung_trang_thai (trang_thai_tai_khoan)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS phuong_tien (
  ma_phuong_tien INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ma_nguoi_dung INT UNSIGNED NOT NULL,
  hang_xe VARCHAR(80) NOT NULL,
  dong_xe VARCHAR(100) NOT NULL,
  bien_so VARCHAR(30) NOT NULL UNIQUE,
  nam_san_xuat SMALLINT UNSIGNED NULL,
  ghi_chu VARCHAR(500) NULL,
  ngay_tao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ngay_cap_nhat TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_phuong_tien_nguoi_dung FOREIGN KEY (ma_nguoi_dung)
    REFERENCES nguoi_dung(ma_nguoi_dung) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_phuong_tien_nguoi_dung (ma_nguoi_dung)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dich_vu (
  ma_dich_vu INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ten_dich_vu VARCHAR(160) NOT NULL UNIQUE,
  mo_ta TEXT NULL,
  gia_tham_khao DECIMAL(12,0) NULL,
  thoi_gian_du_kien SMALLINT UNSIGNED NULL,
  dang_hoat_dong TINYINT(1) NOT NULL DEFAULT 1,
  ngay_tao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ngay_cap_nhat TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_dich_vu_hoat_dong (dang_hoat_dong)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS khung_gio (
  ma_khung_gio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  gio_bat_dau TIME NOT NULL,
  gio_ket_thuc TIME NOT NULL,
  suc_chua_toi_da SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  dang_hoat_dong TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_khung_gio_thoi_gian (gio_bat_dau, gio_ket_thuc),
  INDEX idx_khung_gio_hoat_dong (dang_hoat_dong)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lich_hen (
  ma_lich_hen INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ma_tra_cuu VARCHAR(32) NOT NULL UNIQUE,
  ma_phuong_tien INT UNSIGNED NOT NULL,
  ma_dich_vu INT UNSIGNED NOT NULL,
  ma_khung_gio INT UNSIGNED NOT NULL,
  ngay_hen DATE NOT NULL,
  ghi_chu_khach_hang VARCHAR(1000) NULL,
  trang_thai VARCHAR(40) NOT NULL DEFAULT 'Chờ xác nhận',
  ngay_tao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ngay_cap_nhat TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_lich_hen_phuong_tien FOREIGN KEY (ma_phuong_tien)
    REFERENCES phuong_tien(ma_phuong_tien) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_lich_hen_dich_vu FOREIGN KEY (ma_dich_vu)
    REFERENCES dich_vu(ma_dich_vu) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_lich_hen_khung_gio FOREIGN KEY (ma_khung_gio)
    REFERENCES khung_gio(ma_khung_gio) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_lich_hen_ngay (ngay_hen),
  INDEX idx_lich_hen_khung_gio (ma_khung_gio),
  INDEX idx_lich_hen_trang_thai (trang_thai),
  INDEX idx_lich_hen_ngay_khung_gio (ngay_hen, ma_khung_gio)
) ENGINE=InnoDB;


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
