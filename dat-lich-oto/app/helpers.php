<?php
function config(?string $key = null)
{
    global $CONFIG;
    return $key === null ? $CONFIG : ($CONFIG[$key] ?? null);
}

function app_url(string $path = ''): string
{
    $base = rtrim((string) config('base_url'), '/');
    $path = ltrim($path, '/');
    return $base . ($path !== '' ? '/' . $path : '');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $items = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $items;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn.');
    }
}

function old(string $key, string $default = ''): string
{
    return e((string) ($_POST[$key] ?? $default));
}

function text_lower(string $value): string
{
    return function_exists('mb_strtolower')
        ? mb_strtolower($value, 'UTF-8')
        : strtolower($value);
}

function text_upper(string $value): string
{
    return function_exists('mb_strtoupper')
        ? mb_strtoupper($value, 'UTF-8')
        : strtoupper($value);
}

function text_len(string $value): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : strlen($value);
}

function normalize_phone(string $value): string
{
    return preg_replace('/\D+/', '', $value) ?? '';
}

function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_valid_date_future_or_today(string $date): bool
{
    $value = DateTime::createFromFormat('Y-m-d', $date);
    return $value
        && $value->format('Y-m-d') === $date
        && $value >= new DateTime('today');
}

/** Loại bỏ đường dẫn chuyển hướng ra ngoài website khi đăng nhập. */
function safe_after_login_path(string $path): string
{
    if (preg_match('~^dat-lich\.php(?:\?(?:dich_vu|phu_tung)=[1-9][0-9]{0,8})?$~D', $path)) {
        return $path;
    }
    return '';
}

function valid_time_range(string $start, string $end): bool
{
    if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D', $start)
        || !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D', $end)) {
        return false;
    }
    return $start < $end;
}

function normalize_plate(string $plate): string
{
    return text_upper(preg_replace('/\s+/', '', trim($plate)) ?? '');
}

function validate_booking_note(string $note, string $serviceName): void
{
    if (text_len($note) > 1000) {
        throw new RuntimeException('Ghi chú không được vượt quá 1.000 ký tự.');
    }
    if (is_other_service_name($serviceName) && text_len(trim($note)) < 5) {
        throw new RuntimeException('Vui lòng mô tả nhu cầu ít nhất 5 ký tự khi chọn dịch vụ Khác.');
    }
}

function make_appointment_code(): string
{
    return 'LH' . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));
}

function appointment_statuses(): array
{
    return ['Chờ xác nhận', 'Đã xác nhận', 'Đang xử lý', 'Hoàn thành', 'Đã hủy'];
}

function can_customer_cancel_status(string $status): bool
{
    return in_array($status, ['Chờ xác nhận', 'Đã xác nhận'], true);
}

function can_transition_status(string $from, string $to): bool
{
    $map = [
        'Chờ xác nhận' => ['Đã xác nhận', 'Đã hủy'],
        'Đã xác nhận' => ['Đang xử lý', 'Đã hủy'],
        'Đang xử lý' => ['Hoàn thành'],
        'Hoàn thành' => [],
        'Đã hủy' => [],
    ];
    return in_array($to, $map[$from] ?? [], true);
}

function status_class(string $status): string
{
    return match ($status) {
        'Chờ xác nhận' => 'status status-wait',
        'Đã xác nhận' => 'status status-confirmed',
        'Đang xử lý' => 'status status-processing',
        'Hoàn thành' => 'status status-done',
        'Đã hủy' => 'status status-cancelled',
        default => 'status',
    };
}

function is_other_service_name(string $name): bool
{
    return text_lower(trim($name)) === 'khác';
}

function format_price($amount): string
{
    return $amount === null ? 'Liên hệ báo giá' : number_format((float) $amount, 0, ',', '.') . ' đ';
}

function rescue_statuses(): array
{
    return ['Mới tiếp nhận', 'Đã liên hệ', 'Đang hỗ trợ', 'Hoàn thành', 'Đã hủy'];
}

function rescue_transition_ok(string $from, string $to): bool
{
    $transitions = [
        'Mới tiếp nhận' => ['Đã liên hệ', 'Đã hủy'],
        'Đã liên hệ' => ['Đang hỗ trợ', 'Đã hủy'],
        'Đang hỗ trợ' => ['Hoàn thành', 'Đã hủy'],
        'Hoàn thành' => [],
        'Đã hủy' => [],
    ];
    return in_array($to, $transitions[$from] ?? [], true);
}

function part_image(array $item): string
{
    $url = trim((string) ($item['hinh_anh'] ?? ''));
    return filter_var($url, FILTER_VALIDATE_URL) && str_starts_with($url, 'https://')
        ? $url
        : app_url('assets/service-workshop.webp');
}

function service_image(string $serviceName): string
{
    $name = text_lower($serviceName);

    if (str_contains($name, 'dầu') || str_contains($name, 'lọc')) {
        return app_url('assets/service-workshop.webp');
    }
    if (str_contains($name, 'động cơ') || str_contains($name, 'hộp số') || str_contains($name, 'chẩn đoán')) {
        return app_url('assets/service-front.webp');
    }
    if (str_contains($name, 'phanh') || str_contains($name, 'lốp') || str_contains($name, 'treo')) {
        return app_url('assets/service-wheel.webp');
    }
    if (str_contains($name, 'điện') || str_contains($name, 'ắc quy') || str_contains($name, 'điều hòa')) {
        return app_url('assets/service-body.webp');
    }
    return app_url('assets/service-front.webp');
}

function render_header(string $title): void
{
    $user = current_user();
    if ($user && !headers_sent()) header('Cache-Control: private, no-store');
    $flashes = take_flashes();
    $app = e((string) config('app_name'));
    $isAdminPage = str_contains($_SERVER['PHP_SELF'] ?? '', '/admin/');

    echo '<!doctype html><html lang="vi"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="theme-color" content="#0b0b0b">';
    echo '<title>' . e($title) . ' - ' . $app . '</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="' . e(app_url('assets/app.css?v=20261016-2')) . '"></head>';
    echo '<body class="' . ($isAdminPage ? 'admin-page' : '') . '">';

    echo '<header class="topbar" id="topbar"><div class="container nav">';
    echo '<a class="brand" href="' . e(app_url('index.php')) . '">';
    echo '<span class="brand-mark">CS</span><span class="brand-copy"><strong>CAR SERVICE</strong><small>Premium Auto Care</small></span></a>';
    echo '<button class="menu-toggle" id="menuToggle" type="button" aria-label="Mở menu"><span></span><span></span><span></span></button>';
    echo '<nav id="mainNav"><a href="' . e(app_url('index.php')) . '">Trang chủ</a><a href="' . e(app_url('dich-vu.php')) . '">Dịch vụ</a>';
    echo '<a href="' . e(app_url('bang-gia.php')) . '">Bảng giá</a><a href="' . e(app_url('phu-tung.php')) . '">Phụ tùng</a><a href="' . e(app_url('cuu-ho.php')) . '">Cứu hộ</a>';

    if (!$user) {
        echo '<a href="' . e(app_url('dang-nhap.php')) . '">Đăng nhập</a>';
        echo '<a class="nav-cta" href="' . e(app_url('dang-ky.php')) . '">Đăng ký</a>';
    } elseif ($user['vai_tro'] === 'admin') {
        echo '<a href="' . e(app_url('admin/index.php')) . '">Quản trị</a>';
        echo '<a href="' . e(app_url('tai-khoan.php')) . '">Tài khoản</a>';
        echo '<a href="' . e(app_url('dang-xuat.php')) . '">Đăng xuất</a>';
    } else {
        echo '<a href="' . e(app_url('dat-lich.php')) . '">Đặt lịch</a>';
        echo '<a href="' . e(app_url('lich-cua-toi.php')) . '">Lịch của tôi</a>';
        echo '<a href="' . e(app_url('phuong-tien.php')) . '">Phương tiện</a>';
        echo '<a href="' . e(app_url('tai-khoan.php')) . '">Tài khoản</a>';
        echo '<a href="' . e(app_url('dang-xuat.php')) . '">Đăng xuất</a>';
    }

    echo '</nav></div></header>';

    if ($isAdminPage) {
        echo '<div class="admin-nav"><div class="container">';
        echo '<a href="' . e(app_url('admin/index.php')) . '">Tổng quan</a>';
        echo '<a href="' . e(app_url('admin/lich-hen.php')) . '">Lịch hẹn</a>';
        echo '<a href="' . e(app_url('admin/dich-vu.php')) . '">Dịch vụ</a>';
        echo '<a href="' . e(app_url('admin/khung-gio.php')) . '">Khung giờ</a>';
        echo '<a href="' . e(app_url('admin/nguoi-dung.php')) . '">Người dùng</a>';
        echo '<a href="' . e(app_url('admin/phu-tung.php')) . '">Phụ tùng</a>';
        echo '<a href="' . e(app_url('admin/cuu-ho.php')) . '">Cứu hộ</a>';
        echo '</div></div>';
    }

    echo '<main class="container main">';
    foreach ($flashes as $item) {
        echo '<div class="alert alert-' . e($item['type']) . '">' . e($item['message']) . '</div>';
    }
}

function render_footer(): void
{
    $phone = (string) config('garage_phone');
    $email = (string) config('garage_email');
    $facebook = (string) config('garage_facebook');

    echo '</main>';
    echo '<footer class="garage-footer"><div class="container">';
    echo '<div class="garage-info-grid">';
    echo '<section><div class="footer-brand">CAR <span>SERVICE</span></div><p>Garage chăm sóc, bảo dưỡng và sửa chữa ô tô theo lịch hẹn, hướng tới trải nghiệm rõ ràng và chuyên nghiệp.</p></section>';
    echo '<section><h3>Thông tin gara</h3><p><strong>Địa chỉ:</strong> ' . e((string) config('garage_address')) . '</p><p><strong>Giờ mở cửa:</strong> ' . e((string) config('garage_hours')) . '</p></section>';
    echo '<section><h3>Liên hệ</h3><p><a href="tel:' . e($phone) . '">' . e($phone) . '</a></p><p><a href="mailto:' . e($email) . '">' . e($email) . '</a></p></section>';
    echo '<section><h3>Mạng xã hội</h3><a class="facebook-link" target="_blank" rel="noopener noreferrer" href="' . e($facebook) . '"><span class="fb-icon">f</span> Facebook CAR SERVICE</a><p class="footer-note">Bấm để mở trang Facebook.</p></section>';
    echo '</div>';
    echo '<div class="footer-bottom"><span>© 2026 CAR SERVICE</span><span>Đồ án quản lý dịch vụ và đặt lịch bảo dưỡng, sửa chữa ô tô.</span></div>';
    echo '</div></footer>';
    echo '<script src="' . e(app_url('assets/app.js?v=20261016-2')) . '"></script></body></html>';
}
