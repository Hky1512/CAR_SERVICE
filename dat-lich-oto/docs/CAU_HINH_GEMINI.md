# Cấu hình và kiểm thử Gemini API cho CAR SERVICE

## Cơ chế

1. Trình duyệt gửi `mo_ta` và CSRF token đến `public/api/goi-y-ai.php`.
2. PHP kiểm tra session, giới hạn độ dài mô tả và số lần gọi trong session.
3. PHP lấy các dịch vụ đang hoạt động từ MySQL rồi gọi REST `generateContent` qua cURL với header `x-goog-api-key`.
4. Model được yêu cầu trả JSON hai trường `ma_dich_vu` (integer) và `ly_do` (string).
5. PHP kiểm tra mã có trong danh sách dịch vụ đang hoạt động. Trình duyệt dùng `textContent` để hiển thị kết quả.
6. Không có key, mất mạng, 401, 403, 404, 429, 503 hoặc timeout đều trả cảnh báo và cho phép khách tiếp tục chọn dịch vụ thủ công.

## Thực hiện trên XAMPP

1. Sao chép `app/local.php.example` thành `app/local.php`.
2. Điền `gemini_api_key` bằng key lấy từ Google AI Studio. Không điền vào `config.php`, HTML hay JavaScript.
3. Model mặc định `gemini-2.5-flash-lite` theo ví dụ REST tài liệu Google. Nếu Google đổi danh sách model, cập nhật `gemini_model` cho đúng model hiện có trong tài khoản.
4. Kiểm tra PHP Apache và PHP CLI có `curl` và `pdo_mysql` trong `php.ini`.
5. Chạy `C:\xampp\php\php.exe tests\check_mysql.php` trước, để xác minh MySQL.
6. Chạy `C:\xampp\php\php.exe tests\check_gemini.php`. Lệnh này gửi một mô tả mẫu và chỉ in tên/mã dịch vụ hoặc nhóm lỗi; không in key.
7. Đăng nhập khách và vào Đặt lịch, nhập mô tả trong khu vực gợi ý AI. Kiểm tra dịch vụ gợi ý có trong danh mục.

## Phân biệt lỗi

| Mã/trạng thái | Ý nghĩa có thể gặp | Cách kiểm tra |
|---|---|---|
| Chưa cấu hình | Không đọc được key | Xem `app/local.php` hoặc biến môi trường `GEMINI_API_KEY` |
| HTTP 400 | Dữ liệu hoặc cấu hình model không được chấp nhận | Đối chiếu phiên bản REST, `responseSchema` |
| HTTP 401/403 | Key/quyền truy cập bị từ chối | Kiểm tra khóa, loại khóa và quyền dự án trong AI Studio |
| HTTP 404 | Model/endpoint không khả dụng | Kiểm tra `gemini_model` |
| HTTP 429 | Giới hạn lượt gọi hoặc quota | Kiểm tra usage/quota; chờ thử lại |
| HTTP 503 | Dịch vụ tạm quá tải | Chờ và thử lại, chọn thủ công trong lúc chờ |
| Timeout/cURL | Mạng hoặc TLS hoặc nhà cung cấp phản hồi chậm | Kiểm tra kết nối, `extension=curl`, cấu hình chứng chỉ |

**Không khẳng định key hoạt động chỉ vì có tiền tố `AQ.`.** Khóa phải được kiểm thử với quyền truy cập và quota thực tế. Tránh ghi log nguyên header HTTP hoặc key.

Tài liệu Google:
- https://ai.google.dev/gemini-api/docs/api-key
- https://ai.google.dev/gemini-api/docs/migrate-to-interactions
