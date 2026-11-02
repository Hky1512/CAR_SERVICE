<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();

$stats = dashboard_stats();
$extra = extra_dashboard_stats();
render_header('Tổng quan quản trị');
?>
<div class="admin-head reveal">
    <div><span class="eyebrow">Khu vực quản trị</span><h1>Tổng quan</h1></div>
    <div class="admin-links">
        <a href="<?= e(app_url('admin/lich-hen.php')) ?>">Lịch hẹn</a>
        <a href="<?= e(app_url('admin/dich-vu.php')) ?>">Dịch vụ</a>
        <a href="<?= e(app_url('admin/khung-gio.php')) ?>">Khung giờ</a>
        <a href="<?= e(app_url('admin/nguoi-dung.php')) ?>">Người dùng</a>
    </div>
</div>

<div class="stats reveal">
    <div><span>Tổng lịch</span><strong><?= $stats['tong'] ?></strong></div>
    <div><span>Lịch hôm nay</span><strong><?= $stats['hom_nay'] ?></strong></div>
    <div><span>Chờ xác nhận</span><strong><?= $stats['cho'] ?></strong></div>
    <div><span>Đang xử lý</span><strong><?= $stats['dang_xu_ly'] ?></strong></div>
    <div><span>Hoàn thành</span><strong><?= $stats['hoan_thanh'] ?></strong></div>
    <div><span>Cứu hộ mới</span><strong><?= $extra['cuu_ho_moi'] ?></strong></div>
    <div><span>Phụ tùng hiển thị</span><strong><?= $extra['phu_tung'] ?></strong></div>
</div>

<section class="soft panel reveal">
    <h2>Thao tác nhanh</h2>
    <div class="quick-actions">
        <a class="btn btn-primary" href="<?= e(app_url('admin/lich-hen.php')) ?>">Xử lý lịch hẹn</a>
        <a class="btn btn-secondary" href="<?= e(app_url('admin/dich-vu.php')) ?>">Quản lý dịch vụ</a>
        <a class="btn btn-secondary" href="<?= e(app_url('admin/khung-gio.php')) ?>">Cấu hình khung giờ</a>
        <a class="btn btn-secondary" href="<?= e(app_url('admin/phu-tung.php')) ?>">Quản lý phụ tùng</a>
        <a class="btn btn-secondary" href="<?= e(app_url('admin/cuu-ho.php')) ?>">Tiếp nhận cứu hộ</a>
    </div>
</section>
<?php render_footer(); ?>
