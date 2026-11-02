<?php
require __DIR__ . '/../app/bootstrap.php';

$auth = require_customer();
$userId = (int) $auth['ma_nguoi_dung'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['huy'])) {
    verify_csrf();

    try {
        appointment_cancel($userId, (int) $_POST['huy']);
        flash('success', 'Đã hủy lịch.');
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }

    redirect('lich-cua-toi.php');
}

$items = appointments_by_user($userId);
render_header('Lịch của tôi');
?>
<div class="page-head page-head-premium reveal">
    <span class="eyebrow">Theo dõi</span>
    <h1>Lịch của tôi</h1>
    <p>Kiểm tra mã lịch, thời gian và trạng thái xử lý.</p>
</div>

<div class="list-stack reveal">
    <?php if (!$items): ?>
        <div class="empty">Bạn chưa có lịch hẹn. <a href="<?= e(app_url('dat-lich.php')) ?>">Đặt lịch ngay</a>.</div>
    <?php endif; ?>

    <?php foreach ($items as $appointment): ?>
        <article class="appointment-card">
            <div class="appointment-top">
                <div>
                    <span class="code"><?= e($appointment['ma_tra_cuu']) ?></span>
                    <h3><?= e($appointment['ten_dich_vu']) ?></h3>
                    <p><?= e($appointment['hang_xe'] . ' ' . $appointment['dong_xe'] . ' · ' . $appointment['bien_so']) ?></p>
                </div>
                <span class="<?= e(status_class($appointment['trang_thai'])) ?>">
                    <?= e($appointment['trang_thai']) ?>
                </span>
            </div>

            <div class="appointment-meta">
                <span><?= e($appointment['ngay_hen']) ?></span>
                <span><?= e(substr($appointment['gio_bat_dau'], 0, 5)) ?> - <?= e(substr($appointment['gio_ket_thuc'], 0, 5)) ?></span>
            </div>

            <?php if ($appointment['ghi_chu_khach_hang']): ?>
                <p class="note"><?= e($appointment['ghi_chu_khach_hang']) ?></p>
            <?php endif; ?>

            <?php if (can_customer_cancel_status($appointment['trang_thai'])): ?>
                <form method="post" onsubmit="return confirm('Bạn chắc chắn muốn hủy lịch này?')">
                    <?= csrf_field() ?>
                    <button class="btn btn-small btn-danger" name="huy" value="<?= (int) $appointment['ma_lich_hen'] ?>">
                        Hủy lịch
                    </button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>
<?php render_footer(); ?>
