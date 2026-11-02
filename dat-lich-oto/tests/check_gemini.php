<?php
// Chỉ chạy từ Terminal (CLI). Không đưa khóa API vào đường dẫn hoặc script.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require __DIR__ . '/../app/bootstrap.php';
echo "Gemini model: " . config('gemini_model') . "\n";
if (!ai_is_configured()) { echo "[FAIL] Chưa điền key trong app/local.php hoặc GEMINI_API_KEY.\n"; exit(1); }
if (!extension_loaded('curl')) { echo "[FAIL] PHP CLI chưa bật cURL.\n"; exit(1); }
if (!in_array('mysql', PDO::getAvailableDrivers(), true)) { echo "[FAIL] PHP CLI chưa bật pdo_mysql.\n"; exit(1); }
try {
    $result = ai_suggest_service('Xe rung và có tiếng kêu khi đạp phanh');
    if (!$result) throw new RuntimeException('Không có kết quả hợp lệ.');
    echo "[PASS] Gemini phản hồi dịch vụ trong CSDL: " . $result['ten_dich_vu'] . " (ID: " . $result['ma_dich_vu'] . ")\n";
    exit(0);
} catch (Throwable $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    echo "Lưu ý: 401/403 là xác thực, 404 có thể do model, 429 liên quan quota và 503 có thể do quá tải.\n";
    exit(1);
}
