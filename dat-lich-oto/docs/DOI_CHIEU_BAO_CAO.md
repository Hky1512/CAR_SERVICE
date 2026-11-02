# Nội dung cần đồng bộ vào báo cáo đồ án sau khi chạy kiểm thử thực tế

Bản source này thay đổi các điểm sau so với source được đối chiếu trước:

1. `public/dat-lich.php`: đã thêm bước 2 xem lại thông tin trước khi xác nhận. Không giữ chỗ ở bước xem lại, kiểm tra lại trong transaction trước INSERT.
2. `app/repository.php`: chặn thay đổi giờ bắt đầu/kết thúc khi khung giờ đã có lịch; từ chối giảm sức chứa dưới lượng lịch đang giữ chỗ; từ chối trùng/chồng lấn với khung giờ hoạt động.
3. Trạng thái lịch: lệnh hủy của khách và cập nhật của admin có điều kiện kiểm tra trạng thái tại thời điểm UPDATE.
4. `database/schema.sql`, `database/nang-cap.sql`: bảng `yeu_cau_cuu_ho` có `ma_nguoi_dung` nullable và khóa ngoại về `nguoi_dung`.
5. `public/cuu-ho.php`: thêm tra cứu mã + số điện thoại; nếu khách đăng nhập thì yêu cầu gắn `ma_nguoi_dung`; khách không cần đăng nhập vẫn gửi được.
6. `public/phu-tung.php`: bổ sung liên kết đặt lịch tư vấn/thay, chuyển thông tin phụ tùng sang phần ghi chú lịch.
7. `public/tai-khoan.php`: khách và quản trị có thể đổi mật khẩu.
8. `public/admin/nguoi-dung.php`: admin có thể khóa/mở khóa người dùng; phiên tài khoản khóa bị chặn khi truy cập trang cần quyền.
9. Gemini: mã nguồn `app/ai.php` dùng PHP cURL, model mặc định `gemini-2.5-flash-lite`, JSON schema, timeout, kiểm tra mã dịch vụ trong CSDL, giới hạn thời gian và retry. Client hiển thị bằng `textContent`.
10. Bổ sung `tests/check_gemini.php`; mở rộng unit test từ 13 lên 22 trường hợp.

Lưu ý khi sửa báo cáo:
- Chỉ báo cáo kết quả các bước đã chạy **thật** trên XAMPP/MySQL. Không ghi PASS chỉ vì đã có code.
- Nếu Hình 3.5 mô tả bước xem lại lịch, chụp màn hình mới từ mã nguồn này.
- Sửa phần tự học để thống nhất rằng cURL **đã nằm trong bản source cuối**.
- Thay hình xem trước cho giá, phụ tùng, cứu hộ bằng ảnh của ứng dụng chạy MySQL **sau khi** xác minh.
- Giữ rõ giới hạn: không phải hệ thống cứu hộ vận hành thương mại, giá phụ tùng là dữ liệu mẫu, AI không tự chẩn đoán.
