<?php
require __DIR__ . '/../app/bootstrap.php';

$auth = require_customer();
$userId = (int) $auth['ma_nguoi_dung'];
$vehicles = vehicles_by_user($userId);
$services = services_active();
$errors = [];
$preview = null;
$selectedService = (int) ($_POST['ma_dich_vu'] ?? ($_GET['dich_vu'] ?? 0));
$initialNote = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' && isset($_GET['phu_tung'])) {
    $part = part_get((int) $_GET['phu_tung']);
    if ($part && (int) $part['dang_hien_thi'] === 1) {
        $initialNote = 'Tôi muốn tư vấn hoặc đặt lịch thay phụ tùng: ' . $part['ten_phu_tung'];
        foreach ($services as $s) {
            if (is_other_service_name((string) $s['ten_dich_vu'])) {
                $selectedService = (int) $s['ma_dich_vu'];
                break;
            }
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    verify_csrf();
    $vehicleId = (int) ($_POST['ma_phuong_tien'] ?? 0);
    $serviceId = (int) ($_POST['ma_dich_vu'] ?? 0);
    $slotId = (int) ($_POST['ma_khung_gio'] ?? 0);
    $date = trim((string) ($_POST['ngay_hen'] ?? ''));
    $note = trim((string) ($_POST['ghi_chu'] ?? ''));
    try {
        if (isset($_POST['tao_lich'])) {
            // Kiểm tra cuối trong transaction: bản xem lại KHÔNG tự giữ chỗ.
            $appointment = appointment_create($userId, $vehicleId, $serviceId, $slotId, $date, $note);
            flash('success', 'Đặt lịch thành công. Mã lịch: ' . $appointment['ma_tra_cuu']);
            redirect('lich-cua-toi.php');
        }
        if (isset($_POST['xem_lai'])) {
            $preview = appointment_preview($userId, $vehicleId, $serviceId, $slotId, $date, $note);
        }
    } catch (PDOException $e) {
        error_log('Booking database error: ' . $e->getCode());
        $errors[] = 'Chưa thể lưu lịch. Vui lòng thử lại sau.';
    } catch (RuntimeException $e) {
        $errors[] = $e->getMessage();
    }
}

render_header('Đặt lịch');
?>
<div class="page-head page-head-premium reveal">
    <span class="eyebrow">Đặt lịch CAR SERVICE</span>
    <h1>Chọn thời gian phù hợp cho chiếc xe của bạn</h1>
    <p>Kiểm tra lại thông tin trước khi xác nhận. Hệ thống sẽ kiểm tra sức chứa lần cuối ngay khi ghi lịch.</p>
</div>

<?php if (!$vehicles): ?>
    <div class="alert alert-info">Bạn chưa có phương tiện. <a href="<?= e(app_url('phuong-tien.php')) ?>">Thêm phương tiện trước</a>.</div>
<?php endif; ?>
<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<?php if ($preview !== null): ?>
<section class="form-card booking-review reveal">
    <span class="eyebrow">Bước 2/2: Xem lại</span>
    <h2>Kiểm tra lại trước khi đặt lịch</h2>
    <p class="muted">Đây là thông tin dự kiến, khung giờ chỉ được giữ chỗ sau khi bạn bấm Hoàn tất đặt lịch.</p>
    <div class="review-grid">
        <div><small>Phương tiện</small><strong><?= e($preview['vehicle']['hang_xe'] . ' ' . $preview['vehicle']['dong_xe']) ?></strong><span><?= e($preview['vehicle']['bien_so']) ?></span></div>
        <div><small>Dịch vụ</small><strong><?= e($preview['service']['ten_dich_vu']) ?></strong></div>
        <div><small>Ngày hẹn</small><strong><?= e($preview['date']) ?></strong></div>
        <div><small>Khung giờ</small><strong><?= e(substr($preview['slot']['gio_bat_dau'], 0, 5) . ' - ' . substr($preview['slot']['gio_ket_thuc'], 0, 5)) ?></strong></div>
        <?php if ($preview['note'] !== ''): ?><div class="review-note"><small>Ghi chú</small><p><?= e($preview['note']) ?></p></div><?php endif; ?>
    </div>
    <form method="post" class="review-actions">
        <?= csrf_field() ?>
        <input type="hidden" name="ma_phuong_tien" value="<?= (int) $vehicleId ?>">
        <input type="hidden" name="ma_dich_vu" value="<?= (int) $serviceId ?>">
        <input type="hidden" name="ma_khung_gio" value="<?= (int) $slotId ?>">
        <input type="hidden" name="ngay_hen" value="<?= e($date) ?>">
        <input type="hidden" name="ghi_chu" value="<?= e($note) ?>">
        <button class="btn btn-secondary" type="submit" name="sua_lai" value="1">Chỉnh sửa</button>
        <button class="btn btn-primary" type="submit" name="tao_lich" value="1">Hoàn tất đặt lịch</button>
    </form>
</section>
<?php else: ?>
<div class="booking-layout">
    <section class="form-card premium-panel reveal">
        <div class="panel-title"><span class="eyebrow">Bước 1/2: Thông tin lịch</span><h2>Đặt lịch dịch vụ</h2></div>
        <form method="post" class="form-grid" id="bookingForm">
            <?= csrf_field() ?>
            <label>Phương tiện
                <select name="ma_phuong_tien" required>
                    <option value="">Chọn xe</option>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <option value="<?= (int) $vehicle['ma_phuong_tien'] ?>"
                            <?= (int) ($_POST['ma_phuong_tien'] ?? 0) === (int) $vehicle['ma_phuong_tien'] ? 'selected' : '' ?>>
                            <?= e($vehicle['hang_xe'] . ' ' . $vehicle['dong_xe'] . ' - ' . $vehicle['bien_so']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Dịch vụ
                <select name="ma_dich_vu" id="serviceSelect" required>
                    <option value="">Chọn dịch vụ</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= (int) $service['ma_dich_vu'] ?>" data-other="<?= is_other_service_name($service['ten_dich_vu']) ? '1' : '0' ?>"
                            <?= $selectedService === (int) $service['ma_dich_vu'] ? 'selected' : '' ?>><?= e($service['ten_dich_vu']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="field-hint" id="otherServiceHint">Nếu chọn “Khác”, vui lòng mô tả nhu cầu ở phần ghi chú.</small>
            </label>
            <label>Ngày hẹn
                <input type="date" name="ngay_hen" id="dateInput" min="<?= date('Y-m-d') ?>" value="<?= old('ngay_hen') ?>" required>
            </label>
            <div>
                <span class="label-text">Khung giờ</span>
                <div id="slotList" class="slot-grid" role="group" aria-label="Các khung giờ có thể đặt"><span class="muted">Chọn ngày để tải khung giờ.</span></div>
                <input type="hidden" name="ma_khung_gio" id="slotInput" value="<?= (int) ($_POST['ma_khung_gio'] ?? 0) ?>">
                <small id="slotValidation" class="field-hint" aria-live="polite"></small>
            </div>
            <label>Ghi chú / mô tả tình trạng xe
                <textarea name="ghi_chu" id="bookingNote" rows="5" maxlength="1000" placeholder="Ví dụ: Xe rung khi phanh, có tiếng kêu ở bánh trước..."><?= old('ghi_chu', $initialNote) ?></textarea>
            </label>
            <button class="btn btn-primary btn-wide" type="submit" name="xem_lai" value="1" <?= $vehicles ? '' : 'disabled' ?>>Xem lại lịch trước khi gửi</button>
        </form>
    </section>
    <aside class="ai-card reveal">
        <span class="eyebrow">Trợ lý lựa chọn dịch vụ</span><h2>Gợi ý bằng trợ lý ảo</h2>
        <p>Nhập triệu chứng để trợ lý ảo gợi ý một dịch vụ hiện có. Đây không phải chẩn đoán kỹ thuật và kết quả không tự tạo lịch hẹn.</p>
        <textarea id="aiText" maxlength="500" rows="6" placeholder="Ví dụ: Xe rung và có tiếng kêu khi đạp phanh"></textarea>
        <button type="button" class="btn btn-light" id="aiSuggest">Gợi ý dịch vụ</button>
        <div id="aiResult" class="ai-result" role="status" aria-live="polite"></div>
        
    </aside>
</div>
<script>
window.APP_BASE_URL = <?= json_encode(rtrim((string) config('base_url'), '/'), JSON_UNESCAPED_UNICODE) ?>;
window.APP_SELECTED_SLOT = <?= (int) ($_POST['ma_khung_gio'] ?? 0) ?>;
</script>
<?php endif; ?>
<?php render_footer(); ?>
