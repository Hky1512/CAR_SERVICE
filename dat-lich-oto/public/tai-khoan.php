<?php
require __DIR__ . '/../app/bootstrap.php';

$auth = require_login();
$userId = (int) $auth['ma_nguoi_dung'];
$user = user_get($userId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['doi_mat_khau'])) {
        $current = (string) ($_POST['mat_khau_hien_tai'] ?? '');
        $new = (string) ($_POST['mat_khau_moi'] ?? '');
        $confirm = (string) ($_POST['nhap_lai_mat_khau'] ?? '');
        if (!password_verify($current, $user['mat_khau_bam'])) {
            $errors[] = 'Mật khẩu hiện tại không đúng.';
        }
        if (strlen($new) < 8 || strlen($new) > 72) {
            $errors[] = 'Mật khẩu mới phải từ 8 đến 72 ký tự.';
        }
        if ($new !== $confirm) $errors[] = 'Mật khẩu xác nhận không khớp.';
        if (!$errors) {
            user_change_password($userId, $new);
            session_regenerate_id(true);
            flash('success', 'Đã đổi mật khẩu.');
            redirect('tai-khoan.php');
        }
    } else {
        $name = trim((string) ($_POST['ho_ten'] ?? ''));
        $phone = normalize_phone((string) ($_POST['so_dien_thoai'] ?? ''));
        if (text_len($name) < 2 || text_len($name) > 120) $errors[] = 'Họ tên cần từ 2 đến 120 ký tự.';
        if (strlen($phone) < 9 || strlen($phone) > 11) $errors[] = 'Số điện thoại chưa hợp lệ.';
        if (!$errors) {
            user_update_profile($userId, $name, $phone);
            $user = user_get($userId);
            login_user($user);
            flash('success', 'Đã cập nhật hồ sơ.');
            redirect('tai-khoan.php');
        }
    }
}

render_header('Hồ sơ cá nhân');
?>
<div class="page-head page-head-premium reveal">
    <span class="eyebrow">Tài khoản</span>
    <h1>Hồ sơ cá nhân</h1>
</div>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<section class="form-card narrow reveal">
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <label>Họ tên
            <input name="ho_ten" value="<?= e($user['ho_ten']) ?>" required>
        </label>
        <label>Email
            <input value="<?= e($user['email']) ?>" disabled>
        </label>
        <label>Số điện thoại
            <input name="so_dien_thoai" value="<?= e($user['so_dien_thoai']) ?>" required>
        </label>
        <button class="btn btn-primary">Lưu thay đổi</button>
    </form>
</section>
<section class="form-card narrow reveal" style="margin-top:24px">
    <h2>Đổi mật khẩu</h2>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <label>Mật khẩu hiện tại <input type="password" name="mat_khau_hien_tai" autocomplete="current-password" required></label>
        <label>Mật khẩu mới <input type="password" name="mat_khau_moi" autocomplete="new-password" minlength="8" maxlength="72" required></label>
        <label>Nhập lại mật khẩu mới <input type="password" name="nhap_lai_mat_khau" autocomplete="new-password" minlength="8" maxlength="72" required></label>
        <button class="btn btn-primary" name="doi_mat_khau" value="1">Đổi mật khẩu</button>
    </form>
</section>
<?php render_footer(); ?>
