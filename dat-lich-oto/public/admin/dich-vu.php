<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();

$errors = [];
$editId = isset($_GET['sua']) ? (int) $_GET['sua'] : null;
$edit = $editId ? service_get($editId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $serviceId = ($_POST['ma_dich_vu'] ?? '') !== '' ? (int) $_POST['ma_dich_vu'] : null;
    $name = trim($_POST['ten_dich_vu'] ?? '');
    $description = trim($_POST['mo_ta'] ?? '');
    $active = isset($_POST['dang_hoat_dong']) ? 1 : 0;
    $priceText = trim((string) ($_POST['gia_tham_khao'] ?? ''));
    $price = $priceText === '' ? null : (ctype_digit($priceText) ? (int)$priceText : -1);
    $minutesText = trim((string) ($_POST['thoi_gian_du_kien'] ?? ''));
    $minutes = $minutesText === '' ? null : (ctype_digit($minutesText) ? (int)$minutesText : -1);
    if ($price !== null && ($price < 0 || $price > 999999999)) $errors[] = 'Giá phải là số nguyên không âm.';
    if ($minutes !== null && ($minutes < 1 || $minutes > 1440)) $errors[] = 'Thời gian từ 1 đến 1440 phút.';

    if ($name === '') {
        $errors[] = 'Tên dịch vụ là bắt buộc.';
    }

    if (!$errors) {
        try {
            service_save($serviceId, $name, $description, $active, $price, $minutes);
            flash('success', 'Đã lưu dịch vụ.');
            redirect('admin/dich-vu.php');
        } catch (PDOException $e) {
            $errors[] = 'Không lưu được dịch vụ. Vui lòng kiểm tra tên đã trùng hoặc dữ liệu không hợp lệ.';
        }
    }
}

$items = services_all();
render_header('Quản lý dịch vụ');
?>
<div class="admin-head reveal">
    <div><span class="eyebrow">Quản trị</span><h1>Dịch vụ</h1></div>
    <a href="<?= e(app_url('admin/index.php')) ?>">Về tổng quan</a>
</div>

<div class="two-col">
    <section class="form-card">
        <h2><?= $edit ? 'Cập nhật dịch vụ' : 'Thêm dịch vụ' ?></h2>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="ma_dich_vu" value="<?= e((string) ($edit['ma_dich_vu'] ?? '')) ?>">
            <label>Tên dịch vụ
                <input name="ten_dich_vu" value="<?= e($edit['ten_dich_vu'] ?? '') ?>" required>
            </label>
            <label>Mô tả
                <textarea name="mo_ta" rows="4"><?= e($edit['mo_ta'] ?? '') ?></textarea>
            </label>
            <label>Giá tham khảo (đồng)
                <input type="number" min="0" max="999999999" name="gia_tham_khao" value="<?= e((string) ($edit['gia_tham_khao'] ?? '')) ?>">
            </label>
            <label>Thời gian dự kiến (phút)
                <input type="number" min="1" max="1440" name="thoi_gian_du_kien" value="<?= e((string) ($edit['thoi_gian_du_kien'] ?? '')) ?>">
            </label>
            <label class="check">
                <input type="checkbox" name="dang_hoat_dong" value="1"
                    <?= !$edit || (int) $edit['dang_hoat_dong'] === 1 ? 'checked' : '' ?>>
                Đang hoạt động
            </label>
            <button class="btn btn-primary">Lưu dịch vụ</button>
        </form>
    </section>

    <section class="data-list">
        <?php foreach ($items as $service): ?>
            <article class="data-row">
                <div>
                    <strong><?= e($service['ten_dich_vu']) ?></strong>
                    <p><?= e($service['mo_ta']) ?></p><p><strong><?= e(format_price($service['gia_tham_khao'])) ?></strong> · Dữ liệu mẫu</p>
                </div>
                <div class="row-right">
                    <span class="tag"><?= (int) $service['dang_hoat_dong'] ? 'Hoạt động' : 'Tạm ẩn' ?></span>
                    <a class="btn btn-small btn-secondary"
                       href="<?= e(app_url('admin/dich-vu.php?sua=' . (int) $service['ma_dich_vu'])) ?>">Sửa</a>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</div>
<?php render_footer(); ?>
