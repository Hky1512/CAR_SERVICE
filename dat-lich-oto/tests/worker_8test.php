<?php
// Worker used only by the automated CAR SERVICE integration test suite.
// Not for public web access.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
if ($argc < 3) { fwrite(STDERR, "Missing arguments\n"); exit(2); }
$action = $argv[1];
$dbName = $argv[2];
if (!preg_match('/^cs_kiemthu_[a-f0-9]{10}$/', $dbName)) { fwrite(STDERR, "Invalid isolated database name\n"); exit(2); }
require __DIR__ . '/../app/bootstrap.php';
$CONFIG['db_name'] = $dbName;
$CONFIG['gemini_api_key'] = '';

if ($action === 'booking') {
    if ($argc < 10) { fwrite(STDERR, 'Booking worker arguments missing'); exit(2); }
    [$script, $mode, $db, $uid, $vid, $sid, $slot, $date, $barrier, $ready] = $argv;
    file_put_contents($ready, 'ready');
    $deadline = microtime(true) + 8;
    while (!file_exists($barrier) && microtime(true) < $deadline) { usleep(15000); }
    if (!file_exists($barrier)) { echo json_encode(['success' => false, 'message' => 'Start barrier timeout']); exit(2); }
    try {
        $appointment = appointment_create((int)$uid, (int)$vid, (int)$sid, (int)$slot, $date, 'Test dat lich dong thoi');
        echo json_encode(['success' => true, 'id' => (int)$appointment['ma_lich_hen']], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit(0);
}
if ($action === 'csrf') {
    $_SESSION['_csrf'] = 'token_dung_trong_session';
    $_POST = ['ho_ten'=>'Invalid CSRF', 'csrf_token'=>''];
    register_shutdown_function(static function () {
        echo "\nCSRF_HTTP_STATUS=" . http_response_code() . "\n";
    });
    verify_csrf();
    user_create('Invalid CSRF', 'csrf-not-blocked@test.local', '0912345678', 'Demo12345');
    echo "UNBLOCKED_WRITE\n";
    exit(0);
}
if ($action === 'locked') {
    if ($argc < 4) { exit(2); }
    $user = user_get((int)$argv[3]);
    if (!$user) { exit(2); }
    $_SESSION['auth_user'] = ['ma_nguoi_dung'=>(int)$user['ma_nguoi_dung'], 'ho_ten'=>$user['ho_ten'], 'email'=>$user['email'], 'vai_tro'=>$user['vai_tro']];
    register_shutdown_function(static function () {
        echo "\nAUTH_SESSION=" . (current_user() ? 'ACTIVE' : 'CLEARED') . "\n";
    });
    require_login();
    echo "AUTH_ALLOWED\n";
    exit(0);
}
fwrite(STDERR, 'Unknown worker mode');
exit(2);
