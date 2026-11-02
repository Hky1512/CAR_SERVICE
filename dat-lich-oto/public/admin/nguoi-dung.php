<?php
require __DIR__ . '/../../app/bootstrap.php';
$auth = require_admin();
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['trang_thai_tai_khoan'])) {
    verify_csrf();
    try {
        $state = (string) $_POST['trang_thai_tai_khoan'];
        if (!in_array($state, ['hoat_dong', 'khoa'], true)) throw new RuntimeException('Trạng thái không hợp lệ.');
        user_set_active((int) $auth['ma_nguoi_dung'], (int) ($_POST['ma_nguoi_dung'] ?? 0), $state === 'hoat_dong');
        flash('success', 'Đã cập nhật trạng thái tài khoản.');
    } catch (RuntimeException $e) { flash('error', $e->getMessage()); }
    redirect('admin/nguoi-dung.php');
}

$q = trim($_GET['q'] ?? '');
$items = user_list($q);
render_header('Quản lý người dùng');
?>
<div class="admin-head reveal">
    <div><span class="eyebrow">Quản trị</span><h1>Người dùng</h1></div>
    <a href="<?= e(app_url('admin/index.php')) ?>">Về tổng quan</a>
</div>

<form class="filter-bar" method="get">
    <input name="q" value="<?= e($q) ?>" placeholder="Tìm theo họ tên hoặc email">
    <button class="btn btn-secondary">Tìm kiếm</button>
</form>

<div class="data-list reveal">
    <?php foreach ($items as $user): ?>
        <article class="data-row">
            <div>
                <strong><?= e($user['ho_ten']) ?></strong>
                <p><?= e($user['email']) ?> · <?= e($user['so_dien_thoai']) ?></p>
            </div>
            <div class="row-right">
                <span class="tag"><?= e($user['vai_tro']) ?></span>
                <span><?= e($user['trang_thai_tai_khoan']) ?></span>
                <?php if ((int) $auth['ma_nguoi_dung'] !== (int) $user['ma_nguoi_dung']): ?>
                <form method="post" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ma_nguoi_dung" value="<?= (int) $user['ma_nguoi_dung'] ?>">
                    <button type="submit" name="trang_thai_tai_khoan" class="btn btn-small btn-secondary"
                      value="<?= $user['trang_thai_tai_khoan'] === 'hoat_dong' ? 'khoa' : 'hoat_dong' ?>">
                        <?= $user['trang_thai_tai_khoan'] === 'hoat_dong' ? 'Khóa' : 'Mở khóa' ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php render_footer(); ?>
