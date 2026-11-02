<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        rescue_update_status((int)($_POST['ma_yeu_cau'] ?? 0), (string)($_POST['trang_thai'] ?? ''));
        flash('success', 'Đã cập nhật trạng thái yêu cầu cứu hộ.');redirect('admin/cuu-ho.php');
    } catch (Throwable $e) { $errors[] = $e->getMessage(); }
}
$status = trim((string) ($_GET['trang_thai'] ?? ''));
$q = trim((string) ($_GET['q'] ?? ''));
if (!in_array($status, array_merge([''], rescue_statuses()), true)) $status = '';
$items = rescue_requests_admin($status, $q);
render_header('Quản lý cứu hộ');
?>
<div class="admin-head reveal"><div><span class="eyebrow">Quản trị</span><h1>Yêu cầu cứu hộ</h1></div><a href="<?= e(app_url('admin/index.php')) ?>">Về tổng quan</a></div>
<p class="muted">Thông tin từ khách là yêu cầu liên hệ, chưa phải lệnh điều phối cứu hộ được xác nhận.</p>
<?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
<form class="filter-bar" method="get"><input name="q" value="<?= e($q) ?>" placeholder="Mã yêu cầu, tên hoặc số điện thoại"><select name="trang_thai"><option value="">Tất cả trạng thái</option><?php foreach (rescue_statuses() as $s): ?><option <?= $status === $s ? 'selected' : '' ?> value="<?= e($s) ?>"><?= e($s) ?></option><?php endforeach; ?></select><button class="btn btn-secondary">Lọc</button></form>
<div class="data-list">
<?php if (!$items): ?><p class="muted">Chưa có yêu cầu phù hợp.</p><?php endif; ?>
<?php foreach ($items as $r): ?>
 <article class="data-row rescue-row"><div><span class="eyebrow"><?= e($r['ma_tra_cuu']) ?> · <?= e($r['ngay_tao']) ?></span><h3><?= e($r['ho_ten']) ?> · <?= e($r['so_dien_thoai']) ?></h3><p><strong>Sự cố:</strong> <?= e($r['loai_su_co']) ?></p><p><strong>Vị trí:</strong> <?= e($r['dia_diem']) ?></p><?php if ($r['mo_ta']): ?><p><strong>Ghi chú:</strong> <?= e($r['mo_ta']) ?></p><?php endif; ?></div>
 <form method="post" class="compact-form"><?= csrf_field() ?><input type="hidden" name="ma_yeu_cau" value="<?= (int)$r['ma_yeu_cau'] ?>"><label>Trạng thái hiện tại<strong><?= e($r['trang_thai']) ?></strong></label><select name="trang_thai" required><option value="">Chọn bước tiếp theo</option><?php foreach (rescue_statuses() as $s): ?><?php if (rescue_transition_ok($r['trang_thai'], $s)): ?><option value="<?= e($s) ?>"><?= e($s) ?></option><?php endif; ?><?php endforeach; ?></select><button class="btn btn-small btn-primary">Cập nhật</button></form>
 </article>
<?php endforeach; ?>
</div>
<?php render_footer(); ?>
