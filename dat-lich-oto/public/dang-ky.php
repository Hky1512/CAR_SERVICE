<?php
require __DIR__ . '/../app/bootstrap.php';

if (current_user()) {
    redirect('index.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['ho_ten'] ?? '');
    $email = text_lower(trim($_POST['email'] ?? ''));
    $phone = normalize_phone($_POST['so_dien_thoai'] ?? '');
    $password = (string) ($_POST['mat_khau'] ?? '');

    if (text_len($name) < 2) {
        $errors[] = 'Họ tên cần ít nhất 2 ký tự.';
    }
    if (!is_valid_email($email)) {
        $errors[] = 'Email không hợp lệ.';
    }
    if (strlen($phone) < 9 || strlen($phone) > 11) {
        $errors[] = 'Số điện thoại không hợp lệ.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Mật khẩu cần ít nhất 8 ký tự.';
    }
    if (is_valid_email($email) && user_find_by_email($email)) {
        $errors[] = 'Email đã được sử dụng.';
    }

    if (!$errors) {
        try {
            $id = user_create($name, $email, $phone, $password);
            login_user(user_get($id));
            flash('success', 'Đăng ký tài khoản thành công.');
            redirect('phuong-tien.php');
        } catch (PDOException $e) {
            $errors[] = 'Không thể tạo tài khoản. Email có thể vừa được đăng ký ở nơi khác.';
        }
    }
}

render_header('Đăng ký');
?>
<div class="auth-wrap">
    <section class="form-card reveal">
        <div class="page-head compact reveal">
            <span class="eyebrow">Tài khoản mới</span>
            <h1>Đăng ký</h1>
            <p>Tạo tài khoản để quản lý xe và đặt lịch.</p>
        </div>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <label>Họ và tên
                <input name="ho_ten" value="<?= old('ho_ten') ?>" required>
            </label>
            <label>Email
                <input type="email" name="email" value="<?= old('email') ?>" required>
            </label>
            <label>Số điện thoại
                <input name="so_dien_thoai" value="<?= old('so_dien_thoai') ?>" required>
            </label>
            <label>Mật khẩu
                <input type="password" name="mat_khau" minlength="8" required>
            </label>
            <button class="btn btn-primary" type="submit">Tạo tài khoản</button>
        </form>
    </section>
</div>
<?php render_footer(); ?>
