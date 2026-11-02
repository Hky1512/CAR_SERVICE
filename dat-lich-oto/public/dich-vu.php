<?php
require __DIR__ . '/../app/bootstrap.php';

$services = services_active();
$user = current_user();
render_header('Dịch vụ');
?>
<div class="page-head page-head-premium reveal">
    <span class="eyebrow">Dịch vụ CAR SERVICE</span>
    <h1>Bảo dưỡng và sửa chữa toàn diện</h1>
    <p>Danh mục dịch vụ đang tiếp nhận. Nếu nhu cầu chưa nằm trong danh sách, hãy chọn mục <strong>Khác</strong> và mô tả tình trạng xe.</p>
</div>

<p class="muted">Lưu ý: giá hiển thị chỉ là dữ liệu minh họa cho đồ án, không phải báo giá của garage hoạt động thực tế.</p>
<div class="service-grid service-grid-full">
    <?php foreach ($services as $service): ?>
        <article class="service-card reveal">
            <div class="service-image">
                <img src="<?= e(service_image($service['ten_dich_vu'])) ?>"
                     alt="<?= e($service['ten_dich_vu']) ?>" loading="lazy">
            </div>
            <div class="service-body">
                <span class="service-index">DỊCH VỤ <?= str_pad((string) (int) $service['ma_dich_vu'], 2, '0', STR_PAD_LEFT) ?></span>
                <h3><?= e($service['ten_dich_vu']) ?></h3>
                <p><?= e($service['mo_ta']) ?></p><p class="gold-price">Giá mẫu: <?= e(format_price($service['gia_tham_khao'])) ?><?php if ($service['thoi_gian_du_kien']): ?> · <?= (int)$service['thoi_gian_du_kien'] ?> phút<?php endif; ?></p>

                <?php if ($user && $user['vai_tro'] === 'khach_hang'): ?>
                    <a class="text-link" href="<?= e(app_url('dat-lich.php?dich_vu=' . (int) $service['ma_dich_vu'])) ?>">
                        Đặt lịch dịch vụ này
                    </a>
                <?php elseif (!$user): ?>
                    <a class="text-link" href="<?= e(app_url('dang-nhap.php')) ?>">Đăng nhập để đặt lịch</a>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<section class="cta-banner reveal">
    <div>
        <span class="eyebrow">Chưa chắc nên chọn dịch vụ nào?</span>
        <h2>Mô tả triệu chứng, AI sẽ hỗ trợ gợi ý</h2>
        <p>Chức năng gợi ý chỉ hỗ trợ lựa chọn nhóm dịch vụ, không thay thế chẩn đoán kỹ thuật.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(app_url($user && $user['vai_tro'] === 'khach_hang' ? 'dat-lich.php' : 'dang-nhap.php')) ?>">Bắt đầu</a>
</section>
<?php render_footer(); ?>
