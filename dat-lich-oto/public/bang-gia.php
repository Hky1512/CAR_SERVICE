<?php
require __DIR__ . '/../app/bootstrap.php';
$items = services_active();
$user = current_user();
render_header('Bảng giá dịch vụ');
?>
<div class="page-head page-head-premium reveal">
    <span class="eyebrow">Chi phí tham khảo</span>
    <h1>Bảng giá dịch vụ</h1>
    
<div class="price-board panel reveal">
    <?php foreach ($items as $item): ?>
        <div class="price-row">
            <div><h3><?= e($item['ten_dich_vu']) ?></h3><p class="muted"><?= e($item['mo_ta']) ?></p></div>
            <div class="price-number"><strong><?= e(format_price($item['gia_tham_khao'])) ?></strong>
                <?php if (!empty($item['thoi_gian_du_kien'])): ?><small>Khoảng <?= (int) $item['thoi_gian_du_kien'] ?> phút</small><?php endif; ?>
            </div>
            <a class="btn btn-small btn-secondary" href="<?= e(app_url(($user && $user['vai_tro'] === 'khach_hang' ? 'dat-lich.php?dich_vu=' . (int)$item['ma_dich_vu'] : 'dang-nhap.php'))) ?>">Đặt lịch</a>
        </div>
    <?php endforeach; ?>
</div>
<section class="cta-banner reveal"><div><span class="eyebrow">Tư vấn trước khi sửa chữa</span><h2>Cần báo giá phù hợp với xe?</h2><p>Liên hệ trực tiếp để trao đổi về dòng xe và tình trạng hiện tại.</p></div><a class="btn btn-primary" href="tel:<?= e((string)config('garage_phone')) ?>">Gọi 0983 104 905</a></section>
<?php render_footer(); ?>
