<?php
require __DIR__ . '/../app/bootstrap.php';

$user = current_user();
$primaryUrl = 'dang-nhap.php';
$primaryText = 'Đặt lịch ngay';

if ($user) {
    if ($user['vai_tro'] === 'admin') {
        $primaryUrl = 'admin/index.php';
        $primaryText = 'Vào quản trị';
    } else {
        $primaryUrl = 'dat-lich.php';
    }
}

$services = array_slice(services_active(), 0, 6);
render_header('Trang chủ');
?>
<section class="hero hero-luxury">
    <div class="hero-overlay"></div>
    <div class="hero-copy reveal">
        <span class="eyebrow">CAR SERVICE · PREMIUM AUTO CARE</span>
        <h1>Chăm sóc xe theo tiêu chuẩn chuyên nghiệp</h1>
        <p>Đặt lịch nhanh, chủ động thời gian và theo dõi toàn bộ quá trình bảo dưỡng, sửa chữa trên một hệ thống thống nhất.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<?= e(app_url($primaryUrl)) ?>"><?= e($primaryText) ?></a>
            <a class="btn btn-ghost" href="<?= e(app_url('dich-vu.php')) ?>">Khám phá dịch vụ</a>
        </div>
        <div class="hero-points">
            <span>Đặt lịch trực tuyến</span>
            <span>Khung giờ rõ ràng</span>
            <span>Theo dõi trạng thái</span>
        </div>
    </div>
</section>

<section class="trust-strip reveal">
    <div><strong>07:00 - 23:00</strong><span>Phục vụ mỗi ngày</span></div>
    <div><strong>14+</strong><span>Nhóm dịch vụ</span></div>
    <div><strong>Online</strong><span>Đặt lịch chủ động</span></div>
    <div><strong>AI</strong><span>Gợi ý dịch vụ hỗ trợ</span></div>
</section>

<section class="section">
    <div class="section-head reveal">
        <div>
            <span class="eyebrow">Dịch vụ nổi bật</span>
            <h2>Chăm sóc toàn diện cho chiếc xe của bạn</h2>
        </div>
        <a class="text-link" href="<?= e(app_url('dich-vu.php')) ?>">Xem toàn bộ dịch vụ</a>
    </div>

    <div class="service-grid">
        <?php foreach ($services as $service): ?>
            <article class="service-card reveal">
                <div class="service-image">
                    <img src="<?= e(service_image($service['ten_dich_vu'])) ?>" alt="<?= e($service['ten_dich_vu']) ?>" loading="lazy">
                </div>
                <div class="service-body">
                    <span class="service-index">CAR SERVICE</span>
                    <h3><?= e($service['ten_dich_vu']) ?></h3>
                    <p><?= e($service['mo_ta']) ?></p><p class="gold-price">Giá mẫu từ <?= e(format_price($service['gia_tham_khao'])) ?></p>
                    <?php if ($user && $user['vai_tro'] === 'khach_hang'): ?>
                        <a class="text-link" href="<?= e(app_url('dat-lich.php?dich_vu=' . (int) $service['ma_dich_vu'])) ?>">Đặt lịch dịch vụ này</a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section"><div class="section-head reveal"><div><span class="eyebrow">Khám phá thêm</span><h2>Dịch vụ, bảng giá và hỗ trợ khi gặp sự cố</h2></div></div>
<div class="featured-links"><a class="feature-link reveal" href="<?= e(app_url('bang-gia.php')) ?>"><strong>01 / Bảng giá</strong><span>Tham khảo chi phí trước khi đặt lịch</span></a><a class="feature-link reveal" href="<?= e(app_url('phu-tung.php')) ?>"><strong>02 / Phụ tùng</strong><span>Danh mục vật tư và linh kiện ô tô</span></a><a class="feature-link reveal" href="<?= e(app_url('cuu-ho.php')) ?>"><strong>03 / Cứu hộ</strong><span>Gửi thông tin xe gặp sự cố</span></a></div>
</section>
<section class="split-section reveal">
    <div class="split-image">
        <img src="<?= e(app_url('assets/service-body.webp')) ?>" alt="Không gian chăm sóc ô tô cao cấp" loading="lazy">
    </div>
    <div class="split-copy">
        <span class="eyebrow">Trải nghiệm CAR SERVICE</span>
        <h2>Rõ ràng từ lúc đặt lịch đến khi hoàn thành</h2>
        <p>Website giúp khách hàng chủ động chọn phương tiện, dịch vụ, ngày và khung giờ còn chỗ. Quản trị viên tiếp nhận lịch tập trung và cập nhật trạng thái theo từng bước xử lý.</p>
        <div class="mini-features">
            <div><strong>01</strong><span>Quản lý phương tiện</span></div>
            <div><strong>02</strong><span>Chọn lịch còn chỗ</span></div>
            <div><strong>03</strong><span>Theo dõi trạng thái</span></div>
        </div>
    </div>
</section>

<section class="section soft-dark">
    <div class="section-head reveal">
        <div><span class="eyebrow">Quy trình</span><h2>Đặt lịch trong 4 bước</h2></div>
    </div>
    <div class="steps premium-steps">
        <div class="reveal"><span>01</span><h3>Đăng nhập</h3><p>Sử dụng tài khoản khách hàng.</p></div>
        <div class="reveal"><span>02</span><h3>Chọn xe và dịch vụ</h3><p>Chọn đúng phương tiện và hạng mục cần thực hiện.</p></div>
        <div class="reveal"><span>03</span><h3>Chọn ngày giờ</h3><p>Hệ thống chỉ hiển thị khung giờ còn sức chứa.</p></div>
        <div class="reveal"><span>04</span><h3>Theo dõi lịch</h3><p>Xem trạng thái xử lý trực tiếp trong tài khoản.</p></div>
    </div>
</section>

<section class="cta-banner reveal">
    <div>
        <span class="eyebrow">CAR SERVICE</span>
        <h2>Sẵn sàng đặt lịch cho chiếc xe của bạn?</h2>
        <p>Chỉ vài bước để chọn dịch vụ và thời gian phù hợp.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(app_url($primaryUrl)) ?>"><?= e($primaryText) ?></a>
</section>
<?php render_footer(); ?>
