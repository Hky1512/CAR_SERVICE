# CAR SERVICE 

## 1. Chức năng có trong mã nguồn

1. Khách hàng đăng ký, đăng nhập/đăng xuất, sửa hồ sơ, đổi mật khẩu; session và phân quyền.
2. Khách hàng thêm/sửa phương tiện của mình; kiểm tra biển số trùng.
3. Danh mục dịch vụ (14 dịch vụ mẫu), thông tin chi tiết và bảng giá tham khảo.
4. Khách đặt lịch qua 2 bước: nhập thông tin, xem lại, xác nhận. Khung giờ được tải qua API và xác thực lại ở MySQL bằng transaction/`FOR UPDATE` khi lưu.
5. Khách xem lịch, tra mã lịch, hủy lịch trong trạng thái cho phép.
6. Quản trị xem dashboard, lọc/cập nhật lịch, thêm/sửa/ẩn dịch vụ, giá và thời gian dự kiến.
7. Quản trị thêm/sửa/ẩn khung giờ và sức chứa, không cho đổi giờ khung đã có lịch, không giảm sức chứa dưới số lịch đã giữ chỗ.
8. Quản trị xem/tìm kiếm, khóa/mở khóa người dùng (không tự khóa tài khoản hiện tại).
9. Danh mục phụ tùng: giá, ảnh, tình trạng có hàng mẫu; khách liên hệ hoặc chọn đặt lịch tư vấn/thay; quản trị thêm/sửa/ẩn.
10. Cứu hộ: khách (kể cả chưa đăng nhập) tạo yêu cầu, nhận mã, tra cứu bằng mã kèm số điện thoại; quản trị lọc và đổi trạng thái đúng quy trình.
11. Gemini API: hỗ trợ gợi ý một mã dịch vụ đang hoạt động từ mô tả triệu chứng, qua server PHP cURL, JSON schema và xác minh mã dịch vụ. Khi API lỗi, khách vẫn đặt lịch thủ công.
12. Kiểm tra CSRF, escape output HTML, giới hạn đầu vào, kiểm soát quyền sở hữu; giảm nguy cơ xung đột khi cập nhật trạng thái, không đưa HTML từ Gemini trực tiếp vào `innerHTML`.


## 2. CÀI MỚI TRÊN XAMPP

1. Giải nén gói ZIP và chép thư mục `dat-lich-oto` vào `C:\xampp\htdocs\dat-lich-oto`.
2. Mở XAMPP Control Panel; Start **Apache** và **MySQL**.
3. Vào `http://localhost/phpmyadmin` rồi chọn Import và nạp **`database/schema.sql`**.
4. Tiếp tục Import **`database/seed.sql`** để có dữ liệu mẫu và hai tài khoản demo.
5. Sao chép `app/local.php.example` thành `app/local.php` (file này **không được chia sẻ công khai**).
6. Cấu hình `db_host`, `db_name`, `db_user`, `db_pass`; nếu dùng XAMPP mặc định: host `127.0.0.1`, CSDL `dat_lich_oto`, user `root`, mật khẩu rỗng (chỉ trên localhost).
7. Mở `http://localhost/dat-lich-oto/public/`.



## 3. GEMINI API (TÙY CHỌN)

Mở file riêng `app/local.php`, đặt giá trị cho các dòng sau:

```php
'gemini_api_key' => 'DAN_KEY_RIENG_CUA_BAN_VAO_DAY',
'gemini_model' => 'gemini-2.5-flash-lite',
```

Hoặc để `gemini_api_key` trống và đặt biến môi trường `GEMINI_API_KEY` trong server. ** không để key trong các tệp `public`, JavaScript, HTML hoặc ZIP public.**

Trong `C:\xampp\php\php.ini` bật `extension=curl` và `extension=pdo_mysql` (bỏ dấu `;` ở đầu dòng nếu có), lưu rồi Restart Apache. Kiểm tra `php -m` có `curl`, `pdo_mysql`. Một khóa mới trong Google AI Studio có thể bắt đầu bằng `AQ.`; đây là định dạng auth key mới, không cần đổi chuỗi tiền tố. Hiệu lực khóa, quyền, quota và thời gian đáp ứng thực tế chỉ biết sau khi chạy kiểm thử.

Mở Terminal tại thư mục source:

```powershell
C:\xampp\php\php.exe tests\check_gemini.php
```

Nếu báo 401/403, xem lại quyền/khóa. Nếu 404, kiểm tra tên model. 429 có thể do quota; 503 có thể do dịch vụ quá tải. Nếu timeout, kiểm tra mạng, chứng chỉ TLS, cURL. Chi tiết: `docs/CAU_HINH_GEMINI.md`.

**Bảo mật:** Key từng được dán vào hội thoại nên cân nhắc thu hồi và tạo khóa mới trong Google AI Studio trước khi sử dụng lâu dài.

## 5. KIỂM THỬ

Trong PowerShell tại thư mục `dat-lich-oto`:

```powershell
powershell -ExecutionPolicy Bypass -File .\kiem-tra.ps1
C:\xampp\php\php.exe tests\check_mysql.php
C:\xampp\php\php.exe tests\check_gemini.php
```

- Lệnh 1: syntax PHP, unit tests và syntax JavaScript nếu đã cài Node.js.
- Lệnh 2: kiểm tra kết nối MySQL, 7 bảng, cột và FK cứu hộ, dữ liệu mẫu, khung giờ.
- Lệnh 3: gọi Gemini thật; chỉ chạy sau khi cấu hình key và có Internet.

Ngoài ra cần **kiểm thử thao tác thật trên trình duyệt** theo `docs/KIEM_THU_THU_CONG.md`. Kết quả lint/unit không đồng nghĩa với E2E MySQL đã PASS.

## 6. TÀI KHOẢN CHỈ DÙNG TRÊN LOCALHOST

`seed.sql` tạo tài khoản minh họa:

- Khách: `khach@example.com` / `Khach@123`
- Quản trị: `admin@example.com` / `Admin@123`

Không còn hiển thị hai mật khẩu này trên trang đăng nhập. **Đổi mật khẩu cả hai tài khoản ở mục Tài khoản ngay sau khi cài đặt**, trước khi cho người khác truy cập; tránh đưa tài khoản mẫu lên website công khai.

## 7. SOURCE VÀ TÀI LIỆU

- `app/`: cấu hình, DB, session, nghiệp vụ, Gemini.
- `database/schema.sql`: tạo mới; `database/nang-cap.sql`: nâng cấp không xóa dữ liệu; `database/seed.sql`: dữ liệu mẫu.
- `public/`: trang khách, admin, API, CSS và JS.
- `tests/unit.php`: bài test logic không cần MySQL.
- `tests/check_mysql.php`: kiểm tra thật trên MySQL.
- `tests/check_gemini.php`: thử Gemini thật.
- `docs/`: bảng kiểm thử, ghi chú đối chiếu báo cáo và thông tin QA.

## 8. TRẠNG THÁI KIỂM THỬ CỦA GÓI SOURCE NÀY

Tại môi trường tạo bản ZIP, **33 tệp PHP không có lỗi cú pháp, JavaScript qua `node --check` và 22 unit tests PASS**. Môi trường hiện tại không có PDO MySQL/cURL hoặc MySQL Server nên **CHƯA xác minh kiểm thử CRUD end-to-end trên XAMPP hoặc lời gọi Gemini dùng key thực**. Hãy ghi kết quả thật vào bảng kiểm thử sau khi chạy trên máy của bạn, thay vì tự đánh dấu PASS.


## 9. CHỐNG LỘ SOURCE KHI DÙNG XAMPP

Gói đi kèm `.htaccess` tại gốc và các thư mục `app`, `database`, `docs`, `tests` để Apache từ chối truy cập trực tiếp vào file bí mật, SQL và tài khoản demo. Chỉ truy cập giao diện ở `.../public/`. Nếu triển khai trên Nginx hoặc server bỏ qua `.htaccess`, phải cấu hình web root trỏ trực tiếp tới thư mục `public` và không để `app/local.php`, SQL hay README nằm trong vùng public.
