<?php
function current_user(): ?array {
    return $_SESSION['auth_user'] ?? null;
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['auth_user'] = [
        'ma_nguoi_dung' => (int)$user['ma_nguoi_dung'],
        'ho_ten' => $user['ho_ten'],
        'email' => $user['email'],
        'vai_tro' => $user['vai_tro'],
    ];
}

function logout_user(): void {
    unset($_SESSION['auth_user']);
    session_regenerate_id(true);
}

function require_login(): array {
    $user = current_user();
    if (!$user) {
        flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('dang-nhap.php');
    }
    // Phiên đăng nhập không thay thế quyền và trạng thái hiện tại trong CSDL.
    $fresh = user_get((int) $user['ma_nguoi_dung']);
    if (!$fresh || ($fresh['trang_thai_tai_khoan'] ?? '') !== 'hoat_dong') {
        logout_user();
        flash('error', 'Tài khoản không còn hoạt động. Vui lòng đăng nhập lại.');
        redirect('dang-nhap.php');
    }
    $_SESSION['auth_user']['vai_tro'] = $fresh['vai_tro'];
    $_SESSION['auth_user']['ho_ten'] = $fresh['ho_ten'];
    return $_SESSION['auth_user'];
}

function require_customer(): array {
    $user = require_login();
    if (($user['vai_tro'] ?? '') !== 'khach_hang') {
        http_response_code(403);
        exit('Bạn không có quyền truy cập chức năng này.');
    }
    return $user;
}

function require_admin(): array {
    $user = require_login();
    if (($user['vai_tro'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Bạn không có quyền truy cập khu vực quản trị.');
    }
    return $user;
}
