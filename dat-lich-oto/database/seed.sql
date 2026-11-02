USE dat_lich_oto;

INSERT INTO nguoi_dung
(ho_ten, email, so_dien_thoai, mat_khau_bam, vai_tro, trang_thai_tai_khoan)
VALUES
('Quản trị viên', 'admin@example.com', '0900000001', '$2y$12$W/i7gNqlWEmStOUQkUJks.fRT2F3aTUKCnUNgVLqBsocpQ5R5aVui', 'admin', 'hoat_dong'),
('Nguyễn Văn Khách', 'khach@example.com', '0900000002', '$2y$12$3mfXfRaUGvMKRR124EFIMeeBTvU7juAvN0A/60PpLNqUuio2jRXPq', 'khach_hang', 'hoat_dong')
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO dich_vu (ten_dich_vu, mo_ta, gia_tham_khao, thoi_gian_du_kien, dang_hoat_dong)
VALUES
('Bảo dưỡng định kỳ', 'Kiểm tra tổng quát và thực hiện các hạng mục bảo dưỡng theo định kỳ của xe.', 850000, 120, 1),
('Thay dầu động cơ', 'Thay dầu động cơ và kiểm tra mức dầu, rò rỉ cùng các dấu hiệu bất thường cơ bản.', 550000, 45, 1),
('Kiểm tra hệ thống phanh', 'Kiểm tra má phanh, đĩa phanh, dầu phanh và các dấu hiệu bất thường khi phanh.', 250000, 60, 1),
('Kiểm tra động cơ', 'Kiểm tra tình trạng vận hành, tiếng ồn, rung và các cảnh báo liên quan đến động cơ.', 350000, 90, 1),
('Kiểm tra điện - ắc quy', 'Kiểm tra ắc quy, hệ thống sạc, khởi động và các lỗi điện cơ bản.', 200000, 45, 1),
('Kiểm tra điều hòa', 'Kiểm tra khả năng làm lạnh, lọc gió và các dấu hiệu bất thường của hệ thống điều hòa.', 300000, 60, 1),
('Kiểm tra hệ thống lái và treo', 'Kiểm tra vô lăng, thước lái, giảm xóc và các chi tiết liên quan đến hệ thống treo.', 300000, 75, 1),
('Kiểm tra lốp và cân bằng động', 'Kiểm tra độ mòn lốp, áp suất, cân bằng động và tình trạng bánh xe.', 200000, 45, 1),
('Thay lọc gió và lọc nhiên liệu', 'Kiểm tra và thay thế lọc gió động cơ hoặc lọc nhiên liệu khi cần.', 180000, 30, 1),
('Kiểm tra hộp số', 'Kiểm tra hoạt động hộp số, dầu hộp số và các dấu hiệu chuyển số bất thường.', 400000, 90, 1),
('Chẩn đoán lỗi bằng máy', 'Đọc mã lỗi và dữ liệu cơ bản bằng thiết bị chẩn đoán để hỗ trợ xác định nhóm vấn đề.', 250000, 50, 1),
('Kiểm tra tổng quát xe', 'Kiểm tra nhanh các hệ thống chính trước chuyến đi hoặc khi khách hàng cần đánh giá tổng thể.', 350000, 75, 1),
('Vệ sinh kim phun và buồng đốt', 'Vệ sinh các bộ phận liên quan nhằm hỗ trợ duy trì khả năng vận hành của động cơ.', 650000, 120, 1),
('Khác', 'Dành cho nhu cầu chưa nằm trong danh sách. Khách hàng cần mô tả rõ tình trạng hoặc yêu cầu.', NULL, NULL, 1)
ON DUPLICATE KEY UPDATE
    mo_ta = IF(COALESCE(mo_ta, '') = '', VALUES(mo_ta), mo_ta),
    gia_tham_khao = COALESCE(gia_tham_khao, VALUES(gia_tham_khao)),
    thoi_gian_du_kien = COALESCE(thoi_gian_du_kien, VALUES(thoi_gian_du_kien));

INSERT INTO khung_gio (gio_bat_dau, gio_ket_thuc, suc_chua_toi_da, dang_hoat_dong)
VALUES
('07:00:00', '08:00:00', 2, 1),
('08:00:00', '09:00:00', 2, 1),
('09:30:00', '10:30:00', 2, 1),
('11:00:00', '12:00:00', 2, 1),
('13:30:00', '14:30:00', 2, 1),
('15:00:00', '16:00:00', 2, 1),
('17:00:00', '18:00:00', 2, 1),
('20:00:00', '21:00:00', 2, 1)
ON DUPLICATE KEY UPDATE
    -- Không ghi đè trạng thái/định mức do admin sửa ở các lần import sau.
    ma_khung_gio = ma_khung_gio;

INSERT INTO phuong_tien
(ma_nguoi_dung, hang_xe, dong_xe, bien_so, nam_san_xuat, ghi_chu)
SELECT ma_nguoi_dung, 'Toyota', 'Camry', '30A-12345', 2021, 'Phương tiện mẫu'
FROM nguoi_dung
WHERE email = 'khach@example.com'
ON DUPLICATE KEY UPDATE ma_phuong_tien = ma_phuong_tien;

INSERT INTO lich_hen
(ma_tra_cuu, ma_phuong_tien, ma_dich_vu, ma_khung_gio, ngay_hen, ghi_chu_khach_hang, trang_thai)
SELECT
    'LHMau0001',
    pt.ma_phuong_tien,
    dv.ma_dich_vu,
    kg.ma_khung_gio,
    CURDATE() + INTERVAL 1 DAY,
    'Lịch mẫu phục vụ kiểm thử và trình diễn',
    'Chờ xác nhận'
FROM phuong_tien pt
JOIN nguoi_dung nd ON nd.ma_nguoi_dung = pt.ma_nguoi_dung
JOIN dich_vu dv ON dv.ten_dich_vu = 'Bảo dưỡng định kỳ'
JOIN khung_gio kg ON kg.gio_bat_dau = '08:00:00'
WHERE nd.email = 'khach@example.com' AND pt.bien_so = '30A-12345'
ON DUPLICATE KEY UPDATE ma_tra_cuu = ma_tra_cuu;

-- Giá dưới đây là GIÁ MẪU TỰ XÂY DỰNG cho đồ án, không phải báo giá garage thật.
INSERT INTO phu_tung (ten_phu_tung, hang_san_xuat, mo_ta, gia_tham_khao, con_hang, dang_hien_thi) VALUES
('Ắc quy 12V phổ thông', 'Loại tham khảo', 'Cần đối chiếu kích thước, dung lượng và chân cọc trước khi chọn.', 1650000, 1, 1),
('Lọc dầu động cơ', 'Loại tham khảo', 'Lựa chọn theo mã phụ tùng tương thích với từng dòng xe.', 180000, 1, 1),
('Lọc gió động cơ', 'Loại tham khảo', 'Vật tư cần xác nhận theo xe cụ thể.', 230000, 1, 1),
('Má phanh trước', 'Loại tham khảo', 'Bộ phận của hệ thống phanh; cần kiểm tra tương thích.', 850000, 1, 1),
('Bugi đánh lửa', 'Loại tham khảo', 'Chi tiết phục vụ hệ thống đánh lửa của động cơ xăng.', 180000, 1, 1),
('Cần gạt mưa', 'Loại tham khảo', 'Chọn chiều dài và ngàm lắp phù hợp kính xe.', 150000, 1, 1),
('Dầu động cơ tổng hợp', 'Loại tham khảo', 'Chọn đúng cấp độ nhớt được nhà sản xuất xe khuyến cáo.', 420000, 1, 1),
('Nước làm mát', 'Loại tham khảo', 'Kiểm tra chủng loại dung dịch trước khi bổ sung hoặc thay.', 200000, 1, 1),
('Bóng đèn chiếu sáng', 'Loại tham khảo', 'Cần xác định chuẩn chân đèn và thông số kỹ thuật.', 260000, 1, 1),
('Lốp ô tô phổ thông', 'Loại tham khảo', 'Giá tùy kích cỡ, chỉ số tải và thương hiệu.', 1500000, 1, 1)
ON DUPLICATE KEY UPDATE ten_phu_tung = ten_phu_tung;
