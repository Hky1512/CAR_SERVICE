<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cap_nhat_trang_thai'])) {
    verify_csrf();

    try {
        appointment_update_status(
            (int) $_POST['ma_lich_hen'],
            (string) $_POST['trang_thai_moi']
        );
        flash('success', 'Đã cập nhật trạng thái.');
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }

    redirect('admin/lich-hen.php');
}

$q = trim($_GET['q'] ?? '');
$status = trim($_GET['trang_thai'] ?? '');
$date = trim($_GET['ngay'] ?? '');
$items = appointments_admin($q, $status, $date);
render_header('Quản lý lịch hẹn');
?>
<div class="admin-head reveal">
    <div><span class="eyebrow">Quản trị</span><h1>Lịch hẹn</h1></div>
    <a href="<?= e(app_url('admin/index.php')) ?>">Về tổng quan</a>
</div>

<form class="filter-bar" method="get">
    <input name="q" value="<?= e($q) ?>" placeholder="Mã lịch, khách hàng, biển số">
    <select name="trang_thai">
        <option value="">Tất cả trạng thái</option>
        <?php foreach (appointment_statuses() as $itemStatus): ?>
            <option value="<?= e($itemStatus) ?>" <?= $status === $itemStatus ? 'selected' : '' ?>>
                <?= e($itemStatus) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="ngay" value="<?= e($date) ?>">
    <button class="btn btn-secondary">Lọc</button>
</form>

<div class="list-stack reveal">
    <?php if (!$items): ?>
        <div class="empty">Không có lịch phù hợp.</div>
    <?php endif; ?>

    <?php foreach ($items as $appointment): ?>
        <article class="appointment-card">
            <div class="appointment-top">
                <div>
                    <span class="code"><?= e($appointment['ma_tra_cuu']) ?></span>
                    <h3><?= e($appointment['ten_dich_vu']) ?></h3>
                    <p><?= e($appointment['ho_ten'] . ' · ' . $appointment['bien_so']) ?></p>
                </div>
                <span class="<?= e(status_class($appointment['trang_thai'])) ?>">
                    <?= e($appointment['trang_thai']) ?>
                </span>
            </div>

            <div class="appointment-meta">
                <span><?= e($appointment['ngay_hen']) ?></span>
                <span><?= e(substr($appointment['gio_bat_dau'], 0, 5)) ?> - <?= e(substr($appointment['gio_ket_thuc'], 0, 5)) ?></span>
                <span><?= e($appointment['email']) ?></span>
            </div>

            <?php
            $nextStatuses = array_values(array_filter(
                appointment_statuses(),
                fn(string $candidate) => can_transition_status($appointment['trang_thai'], $candidate)
            ));
            ?>

            <?php if ($nextStatuses): ?>
                <form method="post" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ma_lich_hen" value="<?= (int) $appointment['ma_lich_hen'] ?>">
                    <select name="trang_thai_moi">
                        <?php foreach ($nextStatuses as $nextStatus): ?>
                            <option value="<?= e($nextStatus) ?>"><?= e($nextStatus) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-small btn-primary" name="cap_nhat_trang_thai" value="1">Cập nhật</button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>
<?php render_footer(); ?>
