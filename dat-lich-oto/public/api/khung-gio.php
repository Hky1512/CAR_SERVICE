<?php
require __DIR__ . '/../../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Chỉ hỗ trợ GET.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$auth = current_user();
$fresh = $auth ? user_get((int) $auth['ma_nguoi_dung']) : null;
if (!$fresh || $fresh['vai_tro'] !== 'khach_hang' || $fresh['trang_thai_tai_khoan'] !== 'hoat_dong') {
    http_response_code(401);
    echo json_encode(['error' => 'Phiên đã hết hạn. Hãy đăng nhập lại.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$date = is_string($_GET['ngay'] ?? null) ? trim($_GET['ngay']) : '';
if (!is_valid_date_future_or_today($date)) {
    http_response_code(422);
    echo json_encode(['error' => 'Ngày hẹn không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$items = array_map(
    fn(array $slot) => [
        'ma_khung_gio' => (int) $slot['ma_khung_gio'],
        'gio_bat_dau' => substr($slot['gio_bat_dau'], 0, 5),
        'gio_ket_thuc' => substr($slot['gio_ket_thuc'], 0, 5),
        'con_cho' => (bool) $slot['con_cho'],
        'so_luong_da_dat' => (int) $slot['so_luong_da_dat'],
        'suc_chua_toi_da' => (int) $slot['suc_chua_toi_da'],
    ],
    available_slots($date)
);
echo json_encode(['data' => $items], JSON_UNESCAPED_UNICODE);
