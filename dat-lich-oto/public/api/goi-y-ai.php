<?php
require __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'Chỉ hỗ trợ POST.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$auth = current_user();
$fresh = $auth ? user_get((int) $auth['ma_nguoi_dung']) : null;
if (!$fresh || $fresh['vai_tro'] !== 'khach_hang' || $fresh['trang_thai_tai_khoan'] !== 'hoat_dong') {
    http_response_code(401);
    echo json_encode(['error' => 'Vui lòng đăng nhập lại.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$token = $input['csrf_token'] ?? '';
if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
    http_response_code(419);
    echo json_encode(['error' => 'Phiên biểu mẫu không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$description = is_string($input['mo_ta'] ?? null) ? trim($input['mo_ta']) : '';
if (text_len($description) < 5 || text_len($description) > 500) {
    http_response_code(422);
    echo json_encode(['error' => 'Mô tả phải dài từ 5 đến 500 ký tự.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Giới hạn số lần gọi theo session để tránh thao tác lặp làm tăng quota.
$now = time();
$_SESSION['ai_calls'] = array_values(array_filter(
    (array) ($_SESSION['ai_calls'] ?? []),
    fn($t) => is_int($t) && $now - $t < 60
));
if (count($_SESSION['ai_calls']) >= 5) {
    http_response_code(429);
    echo json_encode(['error' => 'Bạn đã thử nhiều lần. Vui lòng chờ khoảng một phút.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$_SESSION['ai_calls'][] = $now;
// Thả khóa session trước khi chờ Gemini, tránh chặn thao tác đặt lịch ở tab khác.
session_write_close();

try {
    $result = ai_suggest_service($description);
    if (!$result) throw new RuntimeException('Chưa có dịch vụ hoạt động để gợi ý.');
    echo json_encode(['data' => $result], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (RuntimeException $e) {
    http_response_code(503);
    // Lỗi cấu hình và HTTP an toàn cho người dùng; tuyệt đối không trả key.
    echo json_encode([
        'error' => 'Không lấy được gợi ý. ' . $e->getMessage() . ' Bạn vẫn có thể tự chọn dịch vụ.'
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
}
