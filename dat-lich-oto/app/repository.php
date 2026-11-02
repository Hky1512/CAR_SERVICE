<?php
function user_find_by_email(string $email): ?array
{
    $st = db()->prepare('SELECT * FROM nguoi_dung WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    return $st->fetch() ?: null;
}

function user_get(int $id): ?array
{
    $st = db()->prepare('SELECT * FROM nguoi_dung WHERE ma_nguoi_dung = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function user_create(string $name, string $email, string $phone, string $password): int
{
    $st = db()->prepare(
        "INSERT INTO nguoi_dung (ho_ten, email, so_dien_thoai, mat_khau_bam, vai_tro, trang_thai_tai_khoan)
         VALUES (?, ?, ?, ?, 'khach_hang', 'hoat_dong')"
    );
    $st->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
    return (int) db()->lastInsertId();
}

function user_update_profile(int $id, string $name, string $phone): void
{
    $st = db()->prepare(
        'UPDATE nguoi_dung SET ho_ten = ?, so_dien_thoai = ? WHERE ma_nguoi_dung = ?'
    );
    $st->execute([$name, $phone, $id]);
}

function user_change_password(int $userId, string $password): void
{
    $st = db()->prepare('UPDATE nguoi_dung SET mat_khau_bam = ? WHERE ma_nguoi_dung = ?');
    $st->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);
}

function user_set_active(int $adminId, int $userId, bool $active): void
{
    if ($adminId === $userId) {
        throw new RuntimeException('Không thể tự khóa tài khoản quản trị đang đăng nhập.');
    }
    $st = db()->prepare('UPDATE nguoi_dung SET trang_thai_tai_khoan = ? WHERE ma_nguoi_dung = ?');
    $st->execute([$active ? 'hoat_dong' : 'khoa', $userId]);
    if ($st->rowCount() !== 1) throw new RuntimeException('Không tìm thấy tài khoản hoặc trạng thái không thay đổi.');
}

function user_list(string $q = ''): array
{
    if ($q === '') {
        return db()->query('SELECT * FROM nguoi_dung ORDER BY ma_nguoi_dung DESC')->fetchAll();
    }

    $like = "%{$q}%";
    $st = db()->prepare(
        'SELECT * FROM nguoi_dung WHERE ho_ten LIKE ? OR email LIKE ? ORDER BY ma_nguoi_dung DESC'
    );
    $st->execute([$like, $like]);
    return $st->fetchAll();
}

function vehicles_by_user(int $userId): array
{
    $st = db()->prepare(
        'SELECT * FROM phuong_tien WHERE ma_nguoi_dung = ? ORDER BY ma_phuong_tien DESC'
    );
    $st->execute([$userId]);
    return $st->fetchAll();
}

function vehicle_get_owned(int $vehicleId, int $userId): ?array
{
    $st = db()->prepare(
        'SELECT * FROM phuong_tien WHERE ma_phuong_tien = ? AND ma_nguoi_dung = ?'
    );
    $st->execute([$vehicleId, $userId]);
    return $st->fetch() ?: null;
}

function vehicle_plate_exists(string $plate, ?int $excludeId = null): bool
{
    $sql = 'SELECT 1 FROM phuong_tien WHERE bien_so = ?';
    $args = [$plate];

    if ($excludeId !== null) {
        $sql .= ' AND ma_phuong_tien <> ?';
        $args[] = $excludeId;
    }

    $st = db()->prepare($sql . ' LIMIT 1');
    $st->execute($args);
    return (bool) $st->fetchColumn();
}

function vehicle_save(
    int $userId,
    ?int $vehicleId,
    string $brand,
    string $model,
    string $plate,
    ?int $year,
    string $note
): int {
    if ($vehicleId === null) {
        $st = db()->prepare(
            'INSERT INTO phuong_tien (ma_nguoi_dung, hang_xe, dong_xe, bien_so, nam_san_xuat, ghi_chu)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$userId, $brand, $model, $plate, $year, $note]);
        return (int) db()->lastInsertId();
    }

    $st = db()->prepare(
        'UPDATE phuong_tien
         SET hang_xe = ?, dong_xe = ?, bien_so = ?, nam_san_xuat = ?, ghi_chu = ?
         WHERE ma_phuong_tien = ? AND ma_nguoi_dung = ?'
    );
    $st->execute([$brand, $model, $plate, $year, $note, $vehicleId, $userId]);
    return $vehicleId;
}

function services_active(): array
{
    return db()->query(
        "SELECT * FROM dich_vu
         WHERE dang_hoat_dong = 1
         ORDER BY CASE WHEN ten_dich_vu = 'Khác' THEN 1 ELSE 0 END, ma_dich_vu"
    )->fetchAll();
}

function services_all(): array
{
    return db()->query('SELECT * FROM dich_vu ORDER BY ma_dich_vu')->fetchAll();
}

function service_get(int $serviceId): ?array
{
    $st = db()->prepare('SELECT * FROM dich_vu WHERE ma_dich_vu = ?');
    $st->execute([$serviceId]);
    return $st->fetch() ?: null;
}

function service_save(?int $serviceId, string $name, string $description, int $active, ?int $price = null, ?int $minutes = null): int
{
    if ($serviceId === null) {
        $st = db()->prepare(
            'INSERT INTO dich_vu (ten_dich_vu, mo_ta, dang_hoat_dong, gia_tham_khao, thoi_gian_du_kien) VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute([$name, $description, $active, $price, $minutes]);
        return (int) db()->lastInsertId();
    }

    $st = db()->prepare(
        'UPDATE dich_vu SET ten_dich_vu = ?, mo_ta = ?, dang_hoat_dong = ?, gia_tham_khao = ?, thoi_gian_du_kien = ? WHERE ma_dich_vu = ?'
    );
    $st->execute([$name, $description, $active, $price, $minutes, $serviceId]);
    return $serviceId;
}

function slots_all(): array
{
    return db()->query('SELECT * FROM khung_gio ORDER BY gio_bat_dau')->fetchAll();
}

function slot_save(?int $slotId, string $start, string $end, int $capacity, int $active): int
{
    if (!valid_time_range($start, $end)) {
        throw new RuntimeException('Giờ bắt đầu và kết thúc không hợp lệ.');
    }
    if ($capacity < 1 || $capacity > 20) {
        throw new RuntimeException('Sức chứa phải từ 1 đến 20.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $old = null;
        if ($slotId !== null) {
            $st = $pdo->prepare('SELECT * FROM khung_gio WHERE ma_khung_gio = ? FOR UPDATE');
            $st->execute([$slotId]);
            $old = $st->fetch();
            if (!$old) throw new RuntimeException('Khung giờ cần sửa không tồn tại.');
            $oldStart = substr((string) $old['gio_bat_dau'], 0, 5);
            $oldEnd = substr((string) $old['gio_ket_thuc'], 0, 5);
            if ($oldStart !== $start || $oldEnd !== $end) {
                $st = $pdo->prepare('SELECT COUNT(*) FROM lich_hen WHERE ma_khung_gio = ?');
                $st->execute([$slotId]);
                if ((int) $st->fetchColumn() > 0) {
                    throw new RuntimeException('Khung giờ đã có lịch, không được đổi giờ bắt đầu/kết thúc. Hãy tạm ẩn khung cũ và tạo khung mới.');
                }
            }
            // Không giảm sức chứa xuống dưới số lịch đang được giữ chỗ ở bất kỳ ngày nào.
            $st = $pdo->prepare("SELECT MAX(so_luong) FROM (SELECT COUNT(*) AS so_luong FROM lich_hen WHERE ma_khung_gio = ? AND trang_thai <> 'Đã hủy' GROUP BY ngay_hen) t");
            $st->execute([$slotId]);
            if ($capacity < (int) ($st->fetchColumn() ?: 0)) {
                throw new RuntimeException('Sức chứa mới thấp hơn số lịch đã đặt trong một ngày.');
            }
        }
        if ($active === 1) {
            // Các khung giờ hoạt động không được chồng lấn nhau trên cùng lịch ngày.
            $st = $pdo->prepare('SELECT COUNT(*) FROM khung_gio WHERE dang_hoat_dong = 1 AND ma_khung_gio <> ? AND gio_bat_dau < ? AND gio_ket_thuc > ?');
            $st->execute([$slotId ?? 0, $end . ':00', $start . ':00']);
            if ((int) $st->fetchColumn() > 0) {
                throw new RuntimeException('Khung giờ bị chồng lấn với khung đang hoạt động.');
            }
        }
        if ($slotId === null) {
            $st = $pdo->prepare('INSERT INTO khung_gio (gio_bat_dau, gio_ket_thuc, suc_chua_toi_da, dang_hoat_dong) VALUES (?, ?, ?, ?)');
            $st->execute([$start . ':00', $end . ':00', $capacity, $active]);
            $slotId = (int) $pdo->lastInsertId();
        } else {
            $st = $pdo->prepare('UPDATE khung_gio SET gio_bat_dau = ?, gio_ket_thuc = ?, suc_chua_toi_da = ?, dang_hoat_dong = ? WHERE ma_khung_gio = ?');
            $st->execute([$start . ':00', $end . ':00', $capacity, $active, $slotId]);
        }
        $pdo->commit();
        return $slotId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function available_slots(string $date): array
{
    $st = db()->prepare(
        "SELECT kg.*,
                COUNT(lh.ma_lich_hen) AS so_luong_da_dat
         FROM khung_gio kg
         LEFT JOIN lich_hen lh
           ON lh.ma_khung_gio = kg.ma_khung_gio
          AND lh.ngay_hen = ?
          AND lh.trang_thai <> 'Đã hủy'
         WHERE kg.dang_hoat_dong = 1
         GROUP BY kg.ma_khung_gio
         ORDER BY kg.gio_bat_dau"
    );
    $st->execute([$date]);
    $items = $st->fetchAll();

    foreach ($items as &$item) {
        $used = (int) $item['so_luong_da_dat'];
        $item['con_cho'] = $used < (int) $item['suc_chua_toi_da'];

        if ($date === date('Y-m-d') && substr($item['gio_bat_dau'], 0, 5) <= date('H:i')) {
            $item['con_cho'] = false;
        }
    }
    unset($item);

    return $items;
}

/** Chỉ xem trước, chưa giữ chỗ; bước xác nhận cuối sẽ kiểm tra lại toàn bộ. */
function appointment_preview(int $userId, int $vehicleId, int $serviceId, int $slotId, string $date, string $note): array
{
    $vehicle = vehicle_get_owned($vehicleId, $userId);
    if (!$vehicle) throw new RuntimeException('Hãy chọn một phương tiện thuộc tài khoản của bạn.');
    $service = service_get($serviceId);
    if (!$service || (int) $service['dang_hoat_dong'] !== 1) {
        throw new RuntimeException('Dịch vụ không khả dụng.');
    }
    validate_booking_note($note, (string) $service['ten_dich_vu']);
    if (!is_valid_date_future_or_today($date)) throw new RuntimeException('Ngày hẹn không hợp lệ.');
    $matched = null;
    foreach (available_slots($date) as $slot) {
        if ((int) $slot['ma_khung_gio'] === $slotId) {
            $matched = $slot;
            break;
        }
    }
    if (!$matched || !$matched['con_cho']) throw new RuntimeException('Khung giờ không khả dụng hoặc vừa hết chỗ.');
    return ['vehicle' => $vehicle, 'service' => $service, 'slot' => $matched,
        'date' => $date, 'note' => $note];
}

function appointment_create(
    int $userId,
    int $vehicleId,
    int $serviceId,
    int $slotId,
    string $date,
    string $note
): array {
    if (!is_valid_date_future_or_today($date)) throw new RuntimeException('Ngày hẹn không hợp lệ.');
    if (text_len($note) > 1000) throw new RuntimeException('Ghi chú quá dài.');

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT * FROM phuong_tien WHERE ma_phuong_tien = ? AND ma_nguoi_dung = ? FOR UPDATE');
        $st->execute([$vehicleId, $userId]);
        if (!$st->fetch()) throw new RuntimeException('Phương tiện không thuộc tài khoản của bạn.');

        $st = $pdo->prepare('SELECT * FROM dich_vu WHERE ma_dich_vu = ? AND dang_hoat_dong = 1 FOR UPDATE');
        $st->execute([$serviceId]);
        $service = $st->fetch();
        if (!$service) throw new RuntimeException('Dịch vụ không khả dụng.');
        validate_booking_note($note, (string) $service['ten_dich_vu']);

        // Mọi yêu cầu tạo lịch cùng một khung phải lần lượt chiếm lock này.
        $st = $pdo->prepare('SELECT * FROM khung_gio WHERE ma_khung_gio = ? AND dang_hoat_dong = 1 FOR UPDATE');
        $st->execute([$slotId]);
        $slot = $st->fetch();
        if (!$slot) throw new RuntimeException('Khung giờ không khả dụng.');
        if ($date === date('Y-m-d') && substr($slot['gio_bat_dau'], 0, 5) <= date('H:i')) {
            throw new RuntimeException('Khung giờ đã bắt đầu hoặc đã qua.');
        }
        $st = $pdo->prepare("SELECT COUNT(*) FROM lich_hen WHERE ngay_hen = ? AND ma_khung_gio = ? AND trang_thai <> 'Đã hủy'");
        $st->execute([$date, $slotId]);
        if ((int) $st->fetchColumn() >= (int) $slot['suc_chua_toi_da']) {
            throw new RuntimeException('Khung giờ vừa hết chỗ. Vui lòng chọn giờ khác.');
        }
        $code = make_appointment_code();
        $st = $pdo->prepare("INSERT INTO lich_hen (ma_tra_cuu, ma_phuong_tien, ma_dich_vu, ma_khung_gio, ngay_hen, ghi_chu_khach_hang, trang_thai) VALUES (?, ?, ?, ?, ?, ?, 'Chờ xác nhận')");
        $st->execute([$code, $vehicleId, $serviceId, $slotId, $date, $note]);
        $id = (int) $pdo->lastInsertId();
        $pdo->commit();
        return appointment_get($id);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function appointment_get(int $appointmentId): ?array
{
    $st = db()->prepare('SELECT * FROM lich_hen WHERE ma_lich_hen = ?');
    $st->execute([$appointmentId]);
    return $st->fetch() ?: null;
}

function appointments_by_user(int $userId): array
{
    $st = db()->prepare(
        'SELECT lh.*, pt.bien_so, pt.hang_xe, pt.dong_xe,
                dv.ten_dich_vu, kg.gio_bat_dau, kg.gio_ket_thuc
         FROM lich_hen lh
         JOIN phuong_tien pt ON pt.ma_phuong_tien = lh.ma_phuong_tien
         JOIN dich_vu dv ON dv.ma_dich_vu = lh.ma_dich_vu
         JOIN khung_gio kg ON kg.ma_khung_gio = lh.ma_khung_gio
         WHERE pt.ma_nguoi_dung = ?
         ORDER BY lh.ngay_hen DESC, kg.gio_bat_dau DESC'
    );
    $st->execute([$userId]);
    return $st->fetchAll();
}

function appointment_cancel(int $userId, int $appointmentId): void
{
    // Chỉ đổi trạng thái khi lịch còn thuộc khách và vẫn có thể hủy.
    $st = db()->prepare("UPDATE lich_hen lh
        INNER JOIN phuong_tien pt ON pt.ma_phuong_tien = lh.ma_phuong_tien
        SET lh.trang_thai = 'Đã hủy'
        WHERE lh.ma_lich_hen = ? AND pt.ma_nguoi_dung = ?
          AND lh.trang_thai IN ('Chờ xác nhận', 'Đã xác nhận')");
    $st->execute([$appointmentId, $userId]);
    if ($st->rowCount() !== 1) {
        throw new RuntimeException('Không thể hủy: lịch không thuộc tài khoản hoặc trạng thái đã thay đổi.');
    }
}

function appointments_admin(string $q = '', string $status = '', string $date = ''): array
{
    $sql = 'SELECT lh.*, pt.bien_so, pt.hang_xe, pt.dong_xe,
                   dv.ten_dich_vu, kg.gio_bat_dau, kg.gio_ket_thuc,
                   nd.ho_ten, nd.email
            FROM lich_hen lh
            JOIN phuong_tien pt ON pt.ma_phuong_tien = lh.ma_phuong_tien
            JOIN nguoi_dung nd ON nd.ma_nguoi_dung = pt.ma_nguoi_dung
            JOIN dich_vu dv ON dv.ma_dich_vu = lh.ma_dich_vu
            JOIN khung_gio kg ON kg.ma_khung_gio = lh.ma_khung_gio
            WHERE 1 = 1';
    $args = [];

    if ($q !== '') {
        $sql .= ' AND (lh.ma_tra_cuu LIKE ? OR nd.ho_ten LIKE ? OR pt.bien_so LIKE ?)';
        $like = "%{$q}%";
        array_push($args, $like, $like, $like);
    }
    if ($status !== '') {
        $sql .= ' AND lh.trang_thai = ?';
        $args[] = $status;
    }
    if ($date !== '') {
        $sql .= ' AND lh.ngay_hen = ?';
        $args[] = $date;
    }

    $sql .= ' ORDER BY lh.ngay_hen DESC, kg.gio_bat_dau DESC';
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}

function appointment_update_status(int $appointmentId, string $newStatus): void
{
    $appointment = appointment_get($appointmentId);
    if (!$appointment || !can_transition_status((string) $appointment['trang_thai'], $newStatus)) {
        throw new RuntimeException('Lịch không tồn tại hoặc chuyển trạng thái không hợp lệ.');
    }
    $st = db()->prepare('UPDATE lich_hen SET trang_thai = ? WHERE ma_lich_hen = ? AND trang_thai = ?');
    $st->execute([$newStatus, $appointmentId, $appointment['trang_thai']]);
    if ($st->rowCount() !== 1) {
        throw new RuntimeException('Lịch đã được cập nhật bởi thao tác khác. Vui lòng tải lại.');
    }
}

function dashboard_stats(): array
{
    $row = db()->query(
        "SELECT COUNT(*) AS tong,
                SUM(ngay_hen = CURDATE()) AS hom_nay,
                SUM(trang_thai = 'Chờ xác nhận') AS cho,
                SUM(trang_thai = 'Đang xử lý') AS dang_xu_ly,
                SUM(trang_thai = 'Hoàn thành') AS hoan_thanh
         FROM lich_hen"
    )->fetch();

    return [
        'tong' => (int) ($row['tong'] ?? 0),
        'hom_nay' => (int) ($row['hom_nay'] ?? 0),
        'cho' => (int) ($row['cho'] ?? 0),
        'dang_xu_ly' => (int) ($row['dang_xu_ly'] ?? 0),
        'hoan_thanh' => (int) ($row['hoan_thanh'] ?? 0),
    ];
}


function parts_active(): array
{
    return db()->query('SELECT * FROM phu_tung WHERE dang_hien_thi = 1 ORDER BY ma_phu_tung')->fetchAll();
}

function parts_all(): array
{
    return db()->query('SELECT * FROM phu_tung ORDER BY ma_phu_tung DESC')->fetchAll();
}

function part_get(int $id): ?array
{
    $st = db()->prepare('SELECT * FROM phu_tung WHERE ma_phu_tung = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function part_save(?int $id, string $name, string $brand, string $description, ?int $price, string $image, int $inStock, int $visible): int
{
    if ($id === null) {
        $st = db()->prepare('INSERT INTO phu_tung (ten_phu_tung, hang_san_xuat, mo_ta, gia_tham_khao, hinh_anh, con_hang, dang_hien_thi) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $st->execute([$name, $brand, $description, $price, $image, $inStock, $visible]);
        return (int) db()->lastInsertId();
    }
    $st = db()->prepare('UPDATE phu_tung SET ten_phu_tung=?, hang_san_xuat=?, mo_ta=?, gia_tham_khao=?, hinh_anh=?, con_hang=?, dang_hien_thi=? WHERE ma_phu_tung=?');
    $st->execute([$name, $brand, $description, $price, $image, $inStock, $visible, $id]);
    return $id;
}

function rescue_request_create(string $name, string $phone, string $location, string $issue, string $note, ?int $userId = null): string
{
    $code = 'CH' . date('ymd') . strtoupper(bin2hex(random_bytes(4)));
    $st = db()->prepare('INSERT INTO yeu_cau_cuu_ho (ma_tra_cuu, ma_nguoi_dung, ho_ten, so_dien_thoai, dia_diem, loai_su_co, mo_ta) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $st->execute([$code, $userId, $name, $phone, $location, $issue, $note]);
    return $code;
}

function rescue_lookup(string $code, string $phone): ?array
{
    $st = db()->prepare('SELECT ma_tra_cuu, loai_su_co, trang_thai, ngay_tao, ngay_cap_nhat FROM yeu_cau_cuu_ho WHERE ma_tra_cuu = ? AND so_dien_thoai = ? LIMIT 1');
    $st->execute([text_upper(trim($code)), normalize_phone($phone)]);
    return $st->fetch() ?: null;
}

function rescue_requests_admin(string $status = '', string $q = ''): array
{
    $sql = 'SELECT * FROM yeu_cau_cuu_ho WHERE 1 = 1';
    $args = [];
    if ($status !== '') {
        $sql .= ' AND trang_thai = ?';
        $args[] = $status;
    }
    if ($q !== '') {
        $sql .= ' AND (ma_tra_cuu LIKE ? OR ho_ten LIKE ? OR so_dien_thoai LIKE ?)';
        $term = '%' . $q . '%';
        array_push($args, $term, $term, $term);
    }
    $st = db()->prepare($sql . ' ORDER BY ngay_tao DESC');
    $st->execute($args);
    return $st->fetchAll();
}

function rescue_update_status(int $id, string $status): void
{
    $st = db()->prepare('SELECT trang_thai FROM yeu_cau_cuu_ho WHERE ma_yeu_cau = ?');
    $st->execute([$id]);
    $from = $st->fetchColumn();
    if (!$from || !rescue_transition_ok((string) $from, $status)) {
        throw new RuntimeException('Trạng thái cứu hộ không hợp lệ.');
    }
    // Chỉ cập nhật khi trạng thái chưa bị thay đổi bởi thao tác khác.
    $st = db()->prepare('UPDATE yeu_cau_cuu_ho SET trang_thai = ? WHERE ma_yeu_cau = ? AND trang_thai = ?');
    $st->execute([$status, $id, $from]);
    if ($st->rowCount() !== 1) {
        throw new RuntimeException('Yêu cầu đã được cập nhật bởi người khác. Hãy tải lại trang.');
    }
}

function extra_dashboard_stats(): array
{
    return [
        'cuu_ho_moi' => (int) db()->query("SELECT COUNT(*) FROM yeu_cau_cuu_ho WHERE trang_thai = 'Mới tiếp nhận'")->fetchColumn(),
        'phu_tung' => (int) db()->query('SELECT COUNT(*) FROM phu_tung WHERE dang_hien_thi = 1')->fetchColumn(),
    ];
}
