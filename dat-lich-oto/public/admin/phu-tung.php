<?php
require __DIR__ . '/../../app/bootstrap.php';
require_admin();
$errors = [];
$editId = isset($_GET['sua']) ? (int)$_GET['sua'] : null;
$edit = $editId ? part_get($editId) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = ($_POST['ma_phu_tung'] ?? '') !== '' ? (int) $_POST['ma_phu_tung'] : null;
    $name = trim((string) ($_POST['ten_phu_tung'] ?? ''));
    $brand = trim((string) ($_POST['hang_san_xuat'] ?? ''));
    $description = trim((string) ($_POST['mo_ta'] ?? ''));
    $priceText = trim((string) ($_POST['gia_tham_khao'] ?? ''));
    $price = $priceText === '' ? null : (ctype_digit($priceText) ? (int)$priceText : -1);
    $image = trim((string) ($_POST['hinh_anh'] ?? ''));
    $stock = isset($_POST['con_hang']) ? 1 : 0;
    $visible = isset($_POST['dang_hien_thi']) ? 1 : 0;
    if (text_len($name) < 3 || text_len($name) > 160) $errors[] = 'Tên phụ tùng cần từ 3 đến 160 ký tự.';
    if (text_len($brand) > 100 || text_len($description) > 2000) $errors[] = 'Thông tin phụ tùng quá dài.';
    if ($price !== null && ($price < 0 || $price > 999999999)) $errors[] = 'Giá phải là số nguyên không âm.';
    if (text_len($image) > 500 || ($image !== '' && (!filter_var($image, FILTER_VALIDATE_URL) || !str_starts_with($image, 'https://')))) $errors[] = 'Ảnh phải là URL HTTPS hợp lệ.';
    if (!$errors) {
        try {
            part_save($id, $name, $brand, $description, $price, $image, $stock, $visible);
            flash('success', 'Đã lưu phụ tùng.');redirect('admin/phu-tung.php');
        } catch (Throwable $e) { $errors[] = 'Không lưu được. Kiểm tra tên phụ tùng có bị trùng không.'; }
    }
}
$parts = parts_all(); render_header('Quản lý phụ tùng');
?>
<div class="admin-head reveal"><div><span class="eyebrow">Quản trị</span><h1>Danh mục phụ tùng</h1></div><a href="<?= e(app_url('admin/index.php')) ?>">Về tổng quan</a></div>
<p class="muted">Giá và tình trạng hàng được nhập để minh họa nghiệp vụ, chưa liên kết hệ thống kho hoặc nhà cung cấp thực tế.</p>
<div class="two-col">
 <section class="form-card"><h2><?= $edit ? 'Sửa thông tin' : 'Thêm phụ tùng' ?></h2>
 <?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
 <form method="post" class="form-grid"><?= csrf_field() ?><input type="hidden" name="ma_phu_tung" value="<?= e((string)($edit['ma_phu_tung'] ?? '')) ?>">
 <label>Tên phụ tùng <input required maxlength="160" name="ten_phu_tung" value="<?= e((string)($edit['ten_phu_tung'] ?? '')) ?>"></label>
 <label>Thương hiệu/loại <input maxlength="100" name="hang_san_xuat" value="<?= e((string)($edit['hang_san_xuat'] ?? '')) ?>"></label>
 <label>Mô tả <textarea name="mo_ta" rows="3"><?= e((string)($edit['mo_ta'] ?? '')) ?></textarea></label>
 <label>Giá tham khảo (đồng) <input name="gia_tham_khao" inputmode="numeric" type="number" min="0" max="999999999" value="<?= e((string)($edit['gia_tham_khao'] ?? '')) ?>"></label>
 <label>URL ảnh HTTPS (tùy chọn) <input name="hinh_anh" type="url" value="<?= e((string)($edit['hinh_anh'] ?? '')) ?>"></label>
 <label class="check"><input type="checkbox" name="con_hang" <?= !$edit || (int)$edit['con_hang'] ? 'checked' : '' ?>>Dự kiến có hàng</label>
 <label class="check"><input type="checkbox" name="dang_hien_thi" <?= !$edit || (int)$edit['dang_hien_thi'] ? 'checked' : '' ?>>Hiển thị trên website</label>
 <button class="btn btn-primary">Lưu phụ tùng</button></form></section>
 <section class="data-list"><h2>Danh sách phụ tùng</h2><?php foreach ($parts as $part): ?><article class="data-row"><div><strong><?= e($part['ten_phu_tung']) ?></strong><p><?= e(format_price($part['gia_tham_khao'])) ?> · <?= e($part['hang_san_xuat']) ?></p></div><div class="row-right"><span class="tag"><?= (int)$part['dang_hien_thi'] ? 'Hiển thị' : 'Đã ẩn' ?></span><a class="btn btn-small btn-secondary" href="<?= e(app_url('admin/phu-tung.php?sua=' . (int)$part['ma_phu_tung'])) ?>">Sửa</a></div></article><?php endforeach; ?></section>
</div>
<?php render_footer(); ?>
