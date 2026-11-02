# Kết quả kiểm tra TĨNH tại môi trường tạo source (16/10/2026)

Đã chạy trên môi trường tạo bản ZIP:

- `php -l` cho 33 tệp PHP: không có lỗi cú pháp.
- `php tests/unit.php`: 22/22 trường hợp logic PASS.
- `node --check public/assets/app.js`: không có lỗi cú pháp.
- Kiểm tra gói bàn giao không chứa `app/local.php` và không chứa giá trị khóa API do người dùng gửi.

**Chưa được xác minh trong môi trường này:**

- Kết nối thực tế với MySQL/MariaDB và kiểm thử CRUD end-to-end. Môi trường hiện tại không có driver PDO MySQL và máy chủ tương ứng.
- Gemini gọi API thực bằng khóa người dùng, vì không nhập khóa vào gói và môi trường này không có phần mở rộng PHP cURL.
- Test đồng thời nhiều phiên đặt lịch trên MySQL.
- Hiển thị và tương tác ở mọi kích thước thiết bị thực tế.

Chỉ sau khi chạy trên XAMPP mới được bổ sung kết quả tương ứng vào báo cáo. Danh sách cần xác minh: `docs/KIEM_THU_THU_CONG.md`.
