<?php
require __DIR__ . '/../app/bootstrap.php';
$errors = [];
$visitor = current_user();
$visitorInfo = $visitor ? user_get((int) $visitor['ma_nguoi_dung']) : null;
$lookup = null;
$issues = ['Xe không khởi động','Hỏng ắc quy','Sự cố lốp','Xe chết máy','Cần kéo xe','Khác'];
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['tra_cuu'])) {
    verify_csrf();
    $code = text_upper(trim((string) ($_POST['ma_tra_cuu'] ?? '')));
    $lookupPhone = normalize_phone((string) ($_POST['so_dien_thoai_tra_cuu'] ?? ''));
    if (preg_match('/^CH[0-9]{6}[A-F0-9]{8}$/D', $code) && preg_match('/^0[0-9]{9,10}$/D', $lookupPhone)) {
        $lookup = rescue_lookup($code, $lookupPhone);
    }
    if (!$lookup) $errors[] = 'Không tìm thấy mã yêu cầu tương ứng với số điện thoại.';
} elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['ho_ten'] ?? ''));
    $phone = normalize_phone((string) ($_POST['so_dien_thoai'] ?? ''));
    $address = trim((string) ($_POST['dia_diem'] ?? ''));
    $issue = trim((string) ($_POST['loai_su_co'] ?? ''));
    $note = trim((string) ($_POST['mo_ta'] ?? ''));
    if (text_len($name) < 2 || text_len($name) > 120) $errors[] = 'Họ tên phải từ 2 đến 120 ký tự.';
    if (!preg_match('/^0\d{9,10}$/', $phone)) $errors[] = 'Số điện thoại chưa hợp lệ.';
    if (text_len($address) < 8 || text_len($address) > 300) $errors[] = 'Cần nhập vị trí xe cụ thể hơn (8 đến 300 ký tự).';
    if (!in_array($issue, $issues, true)) $errors[] = 'Vui lòng chọn loại sự cố.';
    if (text_len($note) > 1000 || ($issue === 'Khác' && text_len($note) < 5)) $errors[] = 'Mô tả tối đa 1.000 ký tự; mục Khác cần mô tả từ 5 ký tự.';
    if (time() - (int)($_SESSION['cuu_ho_vua_gui'] ?? 0) < 30) $errors[] = 'Vui lòng chờ ít nhất 30 giây trước khi gửi yêu cầu tiếp theo.';
    if (!$errors) {
        try {
            $code = rescue_request_create($name, $phone, $address, $issue, $note, $visitorInfo ? (int) $visitorInfo['ma_nguoi_dung'] : null);
            $_SESSION['cuu_ho_vua_gui'] = time();
            flash('success', "Đã ghi nhận yêu cầu {$code}. Vui lòng gọi hotline nếu tình huống khẩn cấp; yêu cầu qua web chưa được xác nhận tự động.");
            redirect('cuu-ho.php');
        } catch (Throwable $e) {
            $errors[] = 'Chưa lưu được yêu cầu. Hãy gọi hotline để được hỗ trợ.';
        }
    }
}
render_header('Yêu cầu cứu hộ');
?>
<div class="rescue-hero reveal"><span class="eyebrow">Hỗ trợ sự cố xe</span><h1>Cần hỗ trợ cứu hộ?</h1><p>Để xe ở vị trí an toàn, bật cảnh báo khi cần và liên hệ trực tiếp nếu có nguy hiểm. Biểu mẫu chỉ giúp garage tiếp nhận yêu cầu, không tự động điều phối xe cứu hộ.</p><a class="btn btn-primary" href="tel:<?= e((string) config('garage_phone')) ?>">Gọi ngay 0983 104 905</a><small>Dịch vụ cứu hộ 24/7</small></div>
<div class="two-col rescue-layout">
 <section class="form-card reveal"><span class="eyebrow">Gửi yêu cầu</span><h2>Thông tin tình huống</h2>
 <?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
 <form method="post" class="form-grid">
 <?= csrf_field() ?>
 <label>Họ và tên <input name="ho_ten" maxlength="120" value="<?= old('ho_ten', (string) ($visitorInfo['ho_ten'] ?? '')) ?>" required></label>
 <label>Số điện thoại <input name="so_dien_thoai" inputmode="tel" maxlength="20" value="<?= old('so_dien_thoai', (string) ($visitorInfo['so_dien_thoai'] ?? '')) ?>" required></label>
 <label>Vị trí xe <textarea name="dia_diem" rows="3" maxlength="300" required placeholder="Số nhà, đường, phường hoặc vị trí dễ nhận biết"><?= old('dia_diem') ?></textarea></label>
 <label>Loại sự cố <select name="loai_su_co" required><option value="">Chọn loại sự cố</option><?php foreach ($issues as $item): ?><option value="<?= e($item) ?>" <?= ($_POST['loai_su_co'] ?? '') === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?></select></label>
 <label>Mô tả thêm <textarea name="mo_ta" rows="4" maxlength="1000" placeholder="Ví dụ: xe đỗ tại vị trí nào, còn di chuyển được không..."><?= old('mo_ta') ?></textarea></label>
 <button class="btn btn-primary">Gửi yêu cầu cứu hộ</button>
 </form></section>
 <section class="panel reveal"><h2>Quy trình tiếp nhận</h2><div class="process-list"><p><strong>01. Khai báo</strong> thông tin liên hệ, vị trí và loại sự cố.</p><p><strong>02. Tiếp nhận</strong> thông tin tại khu vực quản trị.</p><p><strong>03. Liên hệ xác nhận</strong> khả năng hỗ trợ, thời gian và chi phí.</p><p><strong>04. Cập nhật trạng thái</strong> khi tình huống được xử lý.</p></div></section>
</div>
<section class="form-card narrow reveal" style="margin-top:28px">
    <span class="eyebrow">Theo dõi yêu cầu</span><h2>Tra cứu trạng thái cứu hộ</h2>
    <p class="muted">Nhập mã nhận được sau khi gửi cùng số điện thoại đã khai báo.</p>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <label>Mã yêu cầu <input name="ma_tra_cuu" maxlength="32" required placeholder="CH..."></label>
        <label>Số điện thoại <input name="so_dien_thoai_tra_cuu" inputmode="tel" maxlength="20" required></label>
        <button class="btn btn-secondary" type="submit" name="tra_cuu" value="1">Tra cứu</button>
    </form>
    <?php if ($lookup): ?>
        <div class="lookup-result" role="status">
            <strong><?= e($lookup['ma_tra_cuu']) ?></strong>
            <p>Loại sự cố: <?= e($lookup['loai_su_co']) ?></p>
            <p>Trạng thái hiện tại: <strong><?= e($lookup['trang_thai']) ?></strong></p>
            <small>Cập nhật: <?= e($lookup['ngay_cap_nhat']) ?></small>
        </div>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
