<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();

$errors = [];
$items = slots_all();
$editId = isset($_GET['sua']) ? (int) $_GET['sua'] : null;
$edit = null;

foreach ($items as $slot) {
    if ((int) $slot['ma_khung_gio'] === $editId) {
        $edit = $slot;
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $slotId = ($_POST['ma_khung_gio'] ?? '') !== '' ? (int) $_POST['ma_khung_gio'] : null;
    $start = trim($_POST['gio_bat_dau'] ?? '');
    $end = trim($_POST['gio_ket_thuc'] ?? '');
    $capacity = (int) ($_POST['suc_chua_toi_da'] ?? 0);
    $active = isset($_POST['dang_hoat_dong']) ? 1 : 0;

    if (!valid_time_range($start, $end)) {
        $errors[] = 'Giờ bắt đầu và kết thúc chưa hợp lệ.';
    }
    if ($capacity < 1 || $capacity > 20) {
        $errors[] = 'Sức chứa phải từ 1 đến 20.';
    }

    if (!$errors) {
        try {
            slot_save($slotId, $start, $end, $capacity, $active);
            flash('success', 'Đã lưu khung giờ.');
            redirect('admin/khung-gio.php');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        } catch (PDOException $e) {
            $errors[] = 'Không lưu được khung giờ. Có thể giờ đã bị trùng.';
        }
    }
}

render_header('Quản lý khung giờ');
?>
<div class="admin-head reveal">
    <div><span class="eyebrow">Quản trị</span><h1>Khung giờ</h1></div>
    <a href="<?= e(app_url('admin/index.php')) ?>">Về tổng quan</a>
</div>

<div class="two-col">
    <section class="form-card">
        <h2><?= $edit ? 'Cập nhật khung giờ' : 'Thêm khung giờ' ?></h2>
        <p class="muted">Nếu khung giờ đã có lịch, không thể sửa giờ bắt đầu/kết thúc. Hãy tạm ẩn và tạo khung mới để bảo toàn lịch sử.</p>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="ma_khung_gio" value="<?= e((string) ($edit['ma_khung_gio'] ?? '')) ?>">
            <label>Giờ bắt đầu
                <input type="time" name="gio_bat_dau" value="<?= e(substr((string) ($edit['gio_bat_dau'] ?? ''), 0, 5)) ?>" required>
            </label>
            <label>Giờ kết thúc
                <input type="time" name="gio_ket_thuc" value="<?= e(substr((string) ($edit['gio_ket_thuc'] ?? ''), 0, 5)) ?>" required>
            </label>
            <label>Sức chứa tối đa
                <input type="number" name="suc_chua_toi_da" min="1" max="20"
                       value="<?= e((string) ($edit['suc_chua_toi_da'] ?? 2)) ?>" required>
            </label>
            <label class="check">
                <input type="checkbox" name="dang_hoat_dong" value="1"
                    <?= !$edit || (int) $edit['dang_hoat_dong'] === 1 ? 'checked' : '' ?>>
                Đang hoạt động
            </label>
            <button class="btn btn-primary">Lưu khung giờ</button>
        </form>
    </section>

    <section class="data-list">
        <?php foreach ($items as $slot): ?>
            <article class="data-row">
                <div>
                    <strong><?= e(substr($slot['gio_bat_dau'], 0, 5)) ?> - <?= e(substr($slot['gio_ket_thuc'], 0, 5)) ?></strong>
                    <p>Sức chứa tối đa: <?= (int) $slot['suc_chua_toi_da'] ?> lịch</p>
                </div>
                <div class="row-right">
                    <span class="tag"><?= (int) $slot['dang_hoat_dong'] ? 'Hoạt động' : 'Tạm ẩn' ?></span>
                    <a class="btn btn-small btn-secondary"
                       href="<?= e(app_url('admin/khung-gio.php?sua=' . (int) $slot['ma_khung_gio'])) ?>">Sửa</a>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</div>
<?php render_footer(); ?>
