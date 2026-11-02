# BẢNG KIỂM THỬ THAO TÁC THỰC TẾ TRÊN XAMPP

Điền Ngày giờ / PASS / FAIL / hình minh chứng sau khi thực sự chạy. Để trống là CHƯA KIỂM THỬ.

| ID | Nhóm | Bước thử / đầu vào | Kết quả mong đợi | Thực tế | Trạng thái |
|---|---|---|---|---|---|
| TC01 | Tài khoản | Đăng ký email mới hợp lệ | Tạo người dùng và đăng nhập | | |
| TC02 | Tài khoản | Đăng ký email trùng | Thông báo trùng, không tạo user | | |
| TC03 | Tài khoản | Đăng nhập đúng và sai mật khẩu | Đúng tạo session; sai từ chối | | |
| TC04 | Tài khoản | Đổi mật khẩu ở Tài khoản | Mật khẩu mới đăng nhập được; cũ không được | | |
| TC05 | Phân quyền | Khách mở URL admin trực tiếp | HTTP 403 hoặc thông báo chặn | | |
| TC06 | Admin | Admin khóa tài khoản khách | Khách bị chặn khi truy cập trang cần đăng nhập | | |
| TC07 | Phương tiện | Khách thêm xe, sửa xe | Thấy dữ liệu đúng trong CSDL | | |
| TC08 | Phương tiện | Thêm biển số trùng | Không tạo xe mới | | |
| TC09 | IDOR | Dùng ID xe của khách khác để sửa | Từ chối quyền | | |
| TC10 | Đặt lịch | Chọn xe/dịch vụ/ngày/giờ rồi xem lại | Hiện đúng màn hình xác nhận, CHƯA ghi lịch | | |
| TC11 | Đặt lịch | Bấm Hoàn tất đặt lịch | Tạo `lich_hen`, mã tra cứu; sức chứa giảm 1 | | |
| TC12 | Đặt lịch | Lấp đầy khung giờ rồi tạo thêm | Lịch cuối bị từ chối; không quá sức chứa | | |
| TC13 | Đặt lịch | Thử ngày quá khứ/giờ đã qua | Bị từ chối cả frontend và backend | | |
| TC14 | Đặt lịch | Dịch vụ Khác, ghi chú dưới 5 ký tự | Không cho xem lại/đặt lịch | | |
| TC15 | Lịch | Hủy lịch ở trạng thái hợp lệ | Chuyển Đã hủy và trả sức chứa | | |
| TC16 | Lịch | Hủy lịch Hoàn thành hoặc Đã hủy | Bị từ chối | | |
| TC17 | Admin lịch | Lọc và đổi trạng thái theo quy tắc | Cập nhật đúng; chặn nhảy trạng thái | | |
| TC18 | Khung giờ | Sửa giờ bắt đầu của khung đã có lịch | Từ chối; giờ lịch cũ giữ nguyên | | |
| TC19 | Khung giờ | Giảm sức chứa dưới số đã đặt | Từ chối | | |
| TC20 | Bảng giá | Admin sửa giá dịch vụ | Giá mới hiển thị tại bảng giá | | |
| TC21 | Phụ tùng | Admin thêm, sửa, tạm ẩn | Public hiển thị/ẩn đúng | | |
| TC22 | Đặt lịch thay | Chọn phụ tùng có hàng, bấm đặt lịch | Đăng nhập nếu cần; ghi chú có tên phụ tùng | | |
| TC23 | Cứu hộ | Gửi yêu cầu khi chưa đăng nhập | Tạo mã CH..., admin nhìn thấy | | |
| TC24 | Cứu hộ | Mã CH + số điện thoại đúng/sai | Đúng thấy trạng thái, sai không lộ dữ liệu | | |
| TC25 | Admin cứu hộ | Chuyển trạng thái theo chuỗi | Chấp nhận bước hợp lệ, chặn bước nhảy | | |
| TC26 | Gemini | Có key hợp lệ, mô tả xe rung khi phanh | Trả một dịch vụ có trong CSDL | | |
| TC27 | Gemini | Bỏ key/mất mạng | Hiện fallback; đặt lịch thủ công vẫn hoạt động | | |
| TC28 | Responsive | Trên mobile và desktop | Các trường, nút, menu sử dụng được | | |
| TC29 | Bảo mật | Form POST thiếu/sai CSRF | Từ chối thay đổi dữ liệu | | |
| TC30 | Đồng thời | Hai khách cùng đặt chỗ cuối gần đồng thời | Tối đa 1 yêu cầu thành công | | |

Khi bảo vệ nên chụp ít nhất các bằng chứng: trang xem lại, hàng mới trong `lich_hen`, lịch khách, admin cập nhật, phụ tùng, cứu hộ và log `tests/check_mysql.php`.

Kết quả lint/unit trong `docs/KET_QUA_KIEM_TRA_SOURCE.md` không được tự động tính là kết quả kiểm thử của các trường hợp trên.
