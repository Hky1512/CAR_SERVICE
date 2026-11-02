<?php
require __DIR__ . '/../app/bootstrap.php';

$auth = require_customer();
$userId = (int) $auth['ma_nguoi_dung'];
$errors = [];
$editId = isset($_GET['sua']) ? (int) $_GET['sua'] : null;
$edit = $editId ? vehicle_get_owned($editId, $userId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $vehicleId = ($_POST['ma_phuong_tien'] ?? '') !== ''
        ? (int) $_POST['ma_phuong_tien']
        : null;

    if ($vehicleId !== null && !vehicle_get_owned($vehicleId, $userId)) {
        http_response_code(403);
        exit('Bạn không có quyền sửa phương tiện này.');
    }
    if ($vehicleId !== null) {
        $edit = vehicle_get_owned($vehicleId, $userId);
    }

    $brand = trim($_POST['hang_xe'] ?? '');
    $model = trim($_POST['dong_xe'] ?? '');
    $plate = normalize_plate((string) ($_POST['bien_so'] ?? ''));
    $year = (int) ($_POST['nam_san_xuat'] ?? 0);
    $note = trim($_POST['ghi_chu'] ?? '');

    if ($brand === '' || $model === '' || $plate === '') {
        $errors[] = 'Hãng xe, dòng xe và biển số là bắt buộc.';
    }
    if ($year && ($year < 1980 || $year > (int) date('Y') + 1)) {
        $errors[] = 'Năm sản xuất chưa hợp lệ.';
    }
    if (text_len($plate) > 30 || text_len($note) > 500 || text_len($brand) > 80 || text_len($model) > 100) {
        $errors[] = 'Một số trường vượt quá độ dài cho phép.';
    }
    if ($plate !== '' && vehicle_plate_exists($plate, $vehicleId)) {
        $errors[] = 'Biển số đã tồn tại trong hệ thống.';
    }

    if (!$errors) {
        try {
            vehicle_save($userId, $vehicleId, $brand, $model, $plate, $year ?: null, $note);
            flash('success', $vehicleId ? 'Đã cập nhật phương tiện.' : 'Đã thêm phương tiện.');
            redirect('phuong-tien.php');
        } catch (PDOException $e) {
            $errors[] = 'Không thể lưu xe. Vui lòng kiểm tra biển số có bị trùng hay không.';
        }
    }
}

$items = vehicles_by_user($userId);
render_header('Phương tiện');
?>
<div class="page-head page-head-premium reveal">
    <span class="eyebrow">Garage của tôi</span>
    <h1>Quản lý phương tiện</h1>
    <p>Thêm phương tiện trước khi tạo lịch hẹn.</p>
</div>

<div class="two-col reveal">
    <section class="form-card reveal">
        <h2><?= $edit ? 'Cập nhật phương tiện' : 'Thêm phương tiện' ?></h2>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="ma_phuong_tien" value="<?= e((string) ($edit['ma_phuong_tien'] ?? '')) ?>">
            <label>Hãng xe
                <input name="hang_xe" value="<?= old('hang_xe', (string) ($edit['hang_xe'] ?? '')) ?>" required>
            </label>
            <label>Dòng xe
                <input name="dong_xe" value="<?= old('dong_xe', (string) ($edit['dong_xe'] ?? '')) ?>" required>
            </label>
            <label>Biển số
                <input name="bien_so" value="<?= old('bien_so', (string) ($edit['bien_so'] ?? '')) ?>" required>
            </label>
            <label>Năm sản xuất
                <input type="number" name="nam_san_xuat" min="1980" max="<?= date('Y') + 1 ?>"
                       value="<?= old('nam_san_xuat', (string) ($edit['nam_san_xuat'] ?? '')) ?>">
            </label>
            <label>Ghi chú
                <textarea name="ghi_chu" rows="3"><?= old('ghi_chu', (string) ($edit['ghi_chu'] ?? '')) ?></textarea>
            </label>
            <button class="btn btn-primary"><?= $edit ? 'Lưu thay đổi' : 'Thêm xe' ?></button>
        </form>
    </section>

    <section class="list-stack reveal">
        <?php if (!$items): ?>
            <div class="empty">Chưa có phương tiện.</div>
        <?php endif; ?>

        <?php foreach ($items as $vehicle): ?>
            <article class="item-card">
                <div>
                    <strong><?= e($vehicle['hang_xe'] . ' ' . $vehicle['dong_xe']) ?></strong>
                    <p>
                        <?= e($vehicle['bien_so']) ?>
                        <?= $vehicle['nam_san_xuat'] ? ' · ' . e((string) $vehicle['nam_san_xuat']) : '' ?>
                    </p>
                </div>
                <a class="btn btn-small btn-secondary"
                   href="<?= e(app_url('phuong-tien.php?sua=' . (int) $vehicle['ma_phuong_tien'])) ?>">Sửa</a>
            </article>
        <?php endforeach; ?>
    </section>
</div>
<?php render_footer(); ?>
