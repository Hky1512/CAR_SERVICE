<?php
require __DIR__ . '/../app/bootstrap.php';
$parts = parts_active();
$customer = current_user();
render_header('Linh kiện và phụ tùng');
?>
<div class="page-head page-head-premium reveal">
    <span class="eyebrow">Linh kiện và phụ tùng</span>
    <h1>Chọn đúng vật tư cho chiếc xe</h1>
    
<div class="parts-grid">
    <?php foreach ($parts as $part): ?>
    <article class="part-card reveal">
        <div class="part-image"><img src="<?= e(part_image($part)) ?>" alt="<?= e($part['ten_phu_tung']) ?>" loading="lazy"></div>
        <div class="part-body">
            <span class="eyebrow"><?= e($part['hang_san_xuat'] ?: 'Phụ tùng ô tô') ?></span>
            <h2><?= e($part['ten_phu_tung']) ?></h2>
            <p><?= e($part['mo_ta']) ?></p>
            <div class="part-bottom"><strong><?= e(format_price($part['gia_tham_khao'])) ?></strong><span class="tag"><?= (int)$part['con_hang'] ? 'Dự kiến có hàng' : 'Cần đặt trước' ?></span></div>
            <a class="btn btn-secondary btn-wide" href="<?= e(app_url('lien-he.php')) ?>">Liên hệ xác nhận</a>
            <?php if ((int) $part['con_hang'] === 1): ?>
                <?php $bookPath = 'dat-lich.php?phu_tung=' . (int) $part['ma_phu_tung']; ?>
                <a class="btn btn-primary btn-wide part-book" href="<?= e(app_url($customer && ($customer['vai_tro'] ?? '') === 'khach_hang' ? $bookPath : 'dang-nhap.php?next=' . rawurlencode($bookPath))) ?>">Đặt lịch tư vấn/thay</a>
            <?php endif; ?>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?php render_footer(); ?>
