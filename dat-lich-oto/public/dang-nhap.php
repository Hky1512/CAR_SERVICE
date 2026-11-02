<?php
require __DIR__ . '/../app/bootstrap.php';

if (current_user()) {
    redirect('index.php');
}

$error = '';
$next = safe_after_login_path((string) ($_POST['next'] ?? ($_GET['next'] ?? '')));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = text_lower(trim($_POST['email'] ?? ''));
    $password = (string) ($_POST['mat_khau'] ?? '');
    $user = user_find_by_email($email);

    if (!$user
        || !password_verify($password, $user['mat_khau_bam'])
        || $user['trang_thai_tai_khoan'] !== 'hoat_dong') {
        $error = 'Email hoặc mật khẩu không đúng.';
    } else {
        login_user($user);
        flash('success', 'Đăng nhập thành công.');
        redirect($user['vai_tro'] === 'admin' ? 'admin/index.php' : ($next !== '' ? $next : 'index.php'));
    }
}

render_header('Đăng nhập');
?>
<div class="auth-wrap">
    <section class="form-card reveal">
        <div class="page-head compact reveal">
            <span class="eyebrow">Tài khoản</span>
            <h1>Đăng nhập</h1>
            <p>Truy cập khu vực khách hàng hoặc quản trị.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">
            <label>Email
                <input type="email" name="email" value="<?= old('email') ?>" required>
            </label>
            <label>Mật khẩu
                <input type="password" name="mat_khau" required>
            </label>
            <button class="btn btn-primary" type="submit">Đăng nhập</button>
        </form>

        <p class="muted">- Khách: khach@example.com / Khach@123 <br>

                   - Quản trị: admin@example.com / Admin@123</p>
    </section>
</div>
<?php render_footer(); ?>
