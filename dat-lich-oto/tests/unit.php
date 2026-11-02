<?php
require __DIR__ . '/../app/bootstrap.php';

$passed = 0;
$failed = 0;

function test(string $name, callable $fn): void
{
    global $passed, $failed;
    try {
        $fn();
        $passed++;
        echo "[PASS] {$name}\n";
    } catch (Throwable $e) {
        $failed++;
        echo "[FAIL] {$name}: {$e->getMessage()}\n";
    }
}

function assert_true(bool $condition, string $message = 'Điều kiện không đúng'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

test('Email hợp lệ', fn() => assert_true(is_valid_email('test@example.com')));
test('Email sai bị từ chối', fn() => assert_true(!is_valid_email('test@')));
test('Ngày quá khứ bị từ chối', fn() => assert_true(!is_valid_date_future_or_today('2020-01-01')));
test('Khách chỉ được hủy lịch đang chờ hoặc đã xác nhận', function () {
    assert_true(can_customer_cancel_status('Chờ xác nhận'));
    assert_true(can_customer_cancel_status('Đã xác nhận'));
    assert_true(!can_customer_cancel_status('Đang xử lý'));
});
test('Luồng trạng thái hợp lệ', function () {
    assert_true(can_transition_status('Chờ xác nhận', 'Đã xác nhận'));
    assert_true(can_transition_status('Đã xác nhận', 'Đang xử lý'));
    assert_true(can_transition_status('Đang xử lý', 'Hoàn thành'));
    assert_true(!can_transition_status('Chờ xác nhận', 'Hoàn thành'));
});
test('Dịch vụ Khác được nhận diện đúng', function () {
    assert_true(is_other_service_name('Khác'));
    assert_true(!is_other_service_name('Bảo dưỡng định kỳ'));
});
test('Mã lịch có định dạng LH + ngày + 6 ký tự', function () {
    assert_true((bool) preg_match('/^LH\d{8}[A-F0-9]{6}$/', make_appointment_code()));
});
test('AI chỉ chấp nhận mã dịch vụ có trong danh mục', function () {
    $services = [
        ['ma_dich_vu' => 1, 'ten_dich_vu' => 'Bảo dưỡng định kỳ'],
        ['ma_dich_vu' => 2, 'ten_dich_vu' => 'Kiểm tra hệ thống phanh'],
    ];
    $response = [
        'candidates' => [[
            'content' => ['parts' => [[
                'text' => '{"ma_dich_vu":2,"ly_do":"Liên quan đến hệ thống phanh."}'
            ]]]
        ]]
    ];
    $result = ai_parse_gemini_response($response, $services);
    assert_true($result !== null && $result['ma_dich_vu'] === 2);
});
test('AI từ chối mã dịch vụ không tồn tại', function () {
    $services = [['ma_dich_vu' => 1, 'ten_dich_vu' => 'Bảo dưỡng định kỳ']];
    $response = [
        'candidates' => [[
            'content' => ['parts' => [['text' => '{"ma_dich_vu":999,"ly_do":"Test"}']]]
        ]]
    ];
    assert_true(ai_parse_gemini_response($response, $services) === null);
});
test('Không có API key thì AI báo chưa cấu hình mà không gọi CSDL', function () {
    global $CONFIG;
    $old = $CONFIG['gemini_api_key'];
    $CONFIG['gemini_api_key'] = '';
    try {
        try {
            ai_suggest_service('Xe rung khi phanh');
            throw new RuntimeException('Không phát sinh lỗi cấu hình AI.');
        } catch (RuntimeException $e) {
            assert_true(str_contains($e->getMessage(), 'chưa được cấu hình'));
        }
    } finally {
        $CONFIG['gemini_api_key'] = $old;
    }
});

test('Định dạng tiền tham khảo và trạng thái cứu hộ', function () {
    assert_true(format_price(150000) === '150.000 đ');
    assert_true(format_price(null) === 'Liên hệ báo giá');
    assert_true(rescue_transition_ok('Mới tiếp nhận','Đã liên hệ'));
    assert_true(!rescue_transition_ok('Mới tiếp nhận','Hoàn thành'));
    assert_true(!rescue_transition_ok('Đã hủy','Đang hỗ trợ'));
});
test('Kiểm tra ngày hợp lệ và ngày giả bị từ chối', function () {
    assert_true(!is_valid_date_future_or_today('2026-02-30'));
    assert_true(!is_valid_date_future_or_today('abc'));
    assert_true(is_valid_date_future_or_today((new DateTime('+1 day'))->format('Y-m-d')));
});
test('Ảnh phụ tùng dùng HTTPS hoặc đường dẫn nội bộ an toàn', function () {
    $one = part_image(['hinh_anh' => 'javascript:alert(1)']);
    assert_true(str_starts_with($one, 'https://') || str_starts_with($one, '/'));
    assert_true(!str_contains($one, 'javascript:'));
    assert_true(part_image(['hinh_anh' => 'https://example.com/a.jpg']) === 'https://example.com/a.jpg');
});

test('Khung giờ hợp lệ và không chồng lấn ngược', function () {
    assert_true(valid_time_range('08:00', '09:00'));
    assert_true(!valid_time_range('09:00', '08:00'));
    assert_true(!valid_time_range('25:00', '26:00'));
    assert_true(!valid_time_range('08:00', '08:00'));
});
test('Ngày và mã lịch được xác thực', function () {
    assert_true(is_valid_date_future_or_today(date('Y-m-d')));
    assert_true(!is_valid_date_future_or_today('2099-13-01'));
});
test('Ghi chú dịch vụ Khác tối thiểu 5 ký tự', function () {
    try {
        validate_booking_note('a', 'Khác');
    } catch (RuntimeException $e) {
        validate_booking_note('Xe bị rung khi phanh', 'Khác');
        return;
    }
    throw new RuntimeException('Đã cho phép ghi chú quá ngắn.');
});
test('Ghi chú bất kỳ bị từ chối nếu quá dài', function () {
    try { validate_booking_note(str_repeat('A', 1001), 'Bảo dưỡng định kỳ'); }
    catch (RuntimeException $e) { return; }
    throw new RuntimeException('Không kiểm tra độ dài ghi chú.');
});
test('Biển số chuẩn hóa và loại khoảng trắng', function () {
    assert_true(normalize_plate(' 30a - 12345 ') === '30A-12345');
});
test('Chỉ cho phép đường quay lại nội bộ hợp lệ', function () {
    assert_true(safe_after_login_path('dat-lich.php?phu_tung=12') !== '');
    assert_true(safe_after_login_path('dat-lich.php?dich_vu=4') !== '');
    assert_true(safe_after_login_path('https://example.com') === '');
    assert_true(safe_after_login_path('//example.com') === '');
    assert_true(safe_after_login_path('../admin/index.php') === '');
});
test('Gemini trả về loại không hợp lệ bị bỏ', function () {
    $services = [['ma_dich_vu' => 3, 'ten_dich_vu' => 'Kiểm tra phanh']];
    assert_true(ai_parse_gemini_response(['candidates' => [['content' => ['parts' => [['text' => '{}']]]]]], $services) === null);
});
test('Lỗi HTTP Gemini có nhãn phân biệt', function () {
    assert_true(str_contains(ai_http_error_message(401), 'quyền'));
    assert_true(str_contains(ai_http_error_message(429), 'quota'));
    assert_true(str_contains(ai_http_error_message(404), 'Model'));
});
test('Trạng thái cứu hộ không được nhảy thẳng', function () {
    assert_true(rescue_transition_ok('Đã liên hệ', 'Đang hỗ trợ'));
    assert_true(!rescue_transition_ok('Mới tiếp nhận', 'Hoàn thành'));
    assert_true(!rescue_transition_ok('Hoàn thành', 'Mới tiếp nhận'));
});

echo "\nTổng: " . ($passed + $failed) . " | Đạt: {$passed} | Không đạt: {$failed}\n";
exit($failed === 0 ? 0 : 1);
