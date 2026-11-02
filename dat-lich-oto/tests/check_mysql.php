<?php
require __DIR__ . '/../app/bootstrap.php';

function check(bool $condition, string $ok, string $fail): void
{
    if (!$condition) throw new RuntimeException($fail);
    echo "[PASS] {$ok}\n";
}

if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
    echo "[FAIL] PHP đang chạy không có PDO MySQL. Bật pdo_mysql trong php.ini của XAMPP.\n";
    exit(1);
}
try {
    $pdo = db();
    check((bool) $pdo->query('SELECT 1')->fetchColumn(), 'Kết nối MySQL', 'Không kết nối được MySQL');
    $requiredTables = ['nguoi_dung', 'phuong_tien', 'dich_vu', 'khung_gio', 'lich_hen', 'phu_tung', 'yeu_cau_cuu_ho'];
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($requiredTables as $table) {
        check(in_array($table, $tables, true), "Có bảng {$table}", "Thiếu bảng {$table}");
    }
    $columns = $pdo->query('SHOW COLUMNS FROM yeu_cau_cuu_ho')->fetchAll(PDO::FETCH_COLUMN);
    check(in_array('ma_nguoi_dung', $columns, true), 'Cột ma_nguoi_dung ở bảng cứu hộ', 'Thiếu cột ma_nguoi_dung, hãy import nang-cap.sql');
    $fk = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='yeu_cau_cuu_ho' AND CONSTRAINT_NAME='fk_cuu_ho_nguoi_dung' AND CONSTRAINT_TYPE='FOREIGN KEY'")->fetchColumn();
    check((int) $fk === 1, 'Khóa ngoại cứu hộ/người dùng', 'Thiếu khóa ngoại cứu hộ/người dùng');
    check((int) $pdo->query('SELECT COUNT(*) FROM dich_vu')->fetchColumn() >= 14, 'Có dữ liệu mẫu dịch vụ', 'Thiếu dịch vụ từ seed.sql');
    check((int) $pdo->query('SELECT COUNT(*) FROM phu_tung')->fetchColumn() >= 8, 'Có dữ liệu mẫu phụ tùng', 'Thiếu phụ tùng từ seed.sql');
    check((int) $pdo->query('SELECT COUNT(*) FROM khung_gio')->fetchColumn() >= 8, 'Có khung giờ', 'Thiếu khung giờ');
    check((int) $pdo->query('SELECT COUNT(*) FROM dich_vu WHERE gia_tham_khao IS NOT NULL')->fetchColumn() >= 10, 'Có giá tham khảo', 'Chưa có giá dịch vụ');
    $tomorrow = (new DateTime('+1 day'))->format('Y-m-d');
    check(is_array(available_slots($tomorrow)), 'Truy vấn khung giờ', 'Truy vấn khung giờ lỗi');
    if (in_array('--ai', $argv ?? [], true)) {
        check(ai_is_configured(), 'Đã cấu hình Gemini API key', 'Chưa cấu hình Gemini API key');
        check(extension_loaded('curl'), 'PHP đã bật cURL', 'PHP chưa bật cURL');
        $suggestion = ai_suggest_service('Xe rung khi đạp phanh');
        check(is_array($suggestion), 'Gemini phản hồi mã dịch vụ hợp lệ', 'Gemini không trả về mã dịch vụ hợp lệ');
    }
    echo "\nHoàn tất kiểm tra kết nối, cấu trúc và dữ liệu . \n";
    exit(0);
} catch (Throwable $e) {
    echo "[FAIL] {$e->getMessage()}\n";
    exit(1);
}
