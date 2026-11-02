<?php
/**
 * CAR SERVICE: eight integration scenarios requested for the academic report.
 * Run from the project's root: C:\xampp\php\php.exe tests\kiem_thu_8_ca.php
 * SAFETY: creates and deletes ONLY a fresh, randomly named database matching
 * cs_kiemthu_<10 hex>. It never writes to dat_lich_oto.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require __DIR__ . '/../app/bootstrap.php';
$CONFIG['gemini_api_key'] = '';
$mainDb = (string) $CONFIG['db_name'];
$tests = [];
$testDb = 'cs_kiemthu_' . bin2hex(random_bytes(5));
$admin = null;
$dbCreated = false;

function run_case(string $id, string $name, callable $test): void {
    global $tests;
    try {
        $description = (string) $test();
        $tests[] = ['id'=>$id, 'name'=>$name, 'status'=>'PASS', 'detail'=>$description];
        echo "[PASS] {$id}: {$name}\n";
    } catch (Throwable $e) {
        $tests[] = ['id'=>$id, 'name'=>$name, 'status'=>'FAIL', 'detail'=>$e->getMessage()];
        echo "[FAIL] {$id}: {$name}: {$e->getMessage()}\n";
    }
}
function ensure(bool $yes, string $message): void { if (!$yes) throw new RuntimeException($message); }
function select_value(string $sql, array $params): mixed {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchColumn();
}
function process_child(array $command): array {
    if (!function_exists('proc_open')) throw new RuntimeException('proc_open disabled; cannot start subprocess');
    $desc = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $process = proc_open($command, $desc, $pipes, __DIR__);
    if (!is_resource($process)) throw new RuntimeException('proc_open failed');
    fclose($pipes[0]);
    return [$process, $pipes];
}
function read_child(array $child): array {
    [$process, $pipes] = $child;
    $out = stream_get_contents($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    $code = proc_close($process);
    return ['out'=>$out, 'err'=>$err, 'exit'=>$code];
}
function wait_ready(array $paths, int $seconds=8): void {
    $deadline=microtime(true)+$seconds;
    do {
        if (count(array_filter($paths,'file_exists')) === count($paths)) return;
        usleep(10000);
    } while (microtime(true)<$deadline);
    throw new RuntimeException('The two workers did not start before timeout');
}
function schema_into_test_db(PDO $pdo, string $dbName): void {
    $source = file_get_contents(__DIR__.'/../database/schema.sql');
    if ($source === false) throw new RuntimeException('Cannot read database/schema.sql');
    // Read original SQL but keep the new database strictly isolated.
    $source = preg_replace('/CREATE DATABASE\s+IF NOT EXISTS\s+dat_lich_oto\s+CHARACTER SET\s+utf8mb4\s+COLLATE\s+utf8mb4_unicode_ci\s*;/i', '', $source);
    $source = preg_replace('/\bUSE\s+dat_lich_oto\s*;/i', '', $source);
    $parts = preg_split('/;[ \t]*\r?\n/', $source);
    $pdo->exec('USE `'.$dbName.'`');
    foreach ($parts as $fragment) {
        $sql = trim($fragment);
        if ($sql === '' || preg_match('/^--[^\n]*$/', $sql)) continue;
        $pdo->exec($sql);
    }
}
function output_report(string $folder, array $cases, string $databaseName): void {
    $stamp = date('Y-m-d H:i:s');
    $out = "CAR SERVICE - KIEM THU 8 CA TICH HOP\n";
    $out .= "Ngay gio: {$stamp}\nMoi truong: PHP ".PHP_VERSION."\n";
    $out .= "Database tam: {$databaseName} (da xoa khi ket thuc)\n";
    $out .= "\n";
    foreach ($cases as $t) {
        $out .= $t['id'].' ['.$t['status'].'] '.$t['name']."\n";
        $out .= '  '.str_replace(["\r", "\n"],['',' '], $t['detail'])."\n";
    }
    $pass = count(array_filter($cases, static fn($c)=>$c['status']==='PASS'));
    $out .= "\nKet qua: {$pass}/".count($cases)." PASS\n";
    $path = $folder.DIRECTORY_SEPARATOR.'ket_qua_8_ca.txt';
    file_put_contents($path,$out);
    file_put_contents($folder.DIRECTORY_SEPARATOR.'ket_qua_8_ca.json', json_encode([
        'executed_at'=>$stamp, 'php_version'=>PHP_VERSION,
        'cases'=>$cases, 'passed'=>$pass, 'total'=>count($cases)
    ],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    echo "\nLog saved: {$path}\n";
}

try {
    ensure($mainDb !== '' && !str_starts_with($mainDb,'cs_kiemthu_'), 'Main database config invalid');
    ensure(in_array('mysql', PDO::getAvailableDrivers(), true), 'PDO MySQL is unavailable; run using XAMPP PHP with pdo_mysql enabled.');
    $admin = new PDO(
        'mysql:host='.$CONFIG['db_host'].';port='.$CONFIG['db_port'].';charset=utf8mb4',
        (string)$CONFIG['db_user'], (string)$CONFIG['db_pass'],
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
    );
    $admin->exec('CREATE DATABASE `'.$testDb.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $dbCreated = true;
    schema_into_test_db($admin,$testDb);
    $CONFIG['db_name'] = $testDb;
    $today = new DateTimeImmutable('today');
    $date = $today->modify('+4 days')->format('Y-m-d');
    $anotherDate = $today->modify('+5 days')->format('Y-m-d');

    $ua=user_create('Khach A kiem thu','a@integration.local','0912345678','Password123');
    $ub=user_create('Khach B kiem thu','b@integration.local','0912345679','Password123');
    $root=user_create('Quan tri kiem thu','admin@integration.local','0912345680','Password123');
    db()->prepare("UPDATE nguoi_dung SET vai_tro='admin' WHERE ma_nguoi_dung=?")->execute([$root]);
    $va=vehicle_save($ua,null,'Toyota','Camry','KT-10001',2022,'');
    $vb=vehicle_save($ub,null,'Honda','City','KT-10002',2023,'');
    $service=service_save(null,'Bao duong kiem thu','Test only',1,500000,60);
    $slot=slot_save(null,'21:00','22:00',1,1);

    run_case('TC01','Hai khach dat dong thoi cho cuoi',function() use($testDb,$ua,$ub,$va,$vb,$service,$slot,$date) {
        $barrier=sys_get_temp_dir().DIRECTORY_SEPARATOR.'cs_barrier_'.bin2hex(random_bytes(4));
        $ready=[$barrier.'.r1', $barrier.'.r2'];
        $worker=__DIR__.'/worker_8test.php';
        $children=[];
        try {
            $children[]=process_child([PHP_BINARY,$worker,'booking',$testDb,(string)$ua,(string)$va,(string)$service,(string)$slot,$date,$barrier,$ready[0]]);
            $children[]=process_child([PHP_BINARY,$worker,'booking',$testDb,(string)$ub,(string)$vb,(string)$service,(string)$slot,$date,$barrier,$ready[1]]);
            wait_ready($ready);
            file_put_contents($barrier,'go');
            $results=[];
            foreach ($children as $child) $results[]=read_child($child);
            $children=[];
            $attempts=[];
            foreach ($results as $r) {
                $one=json_decode(trim($r['out']),true);
                if (!is_array($one) || $r['exit']!==0) throw new RuntimeException('Worker error: '.$r['err'].' '.$r['out']);
                $attempts[]=$one;
            }
            $success=count(array_filter($attempts,static fn($item)=>$item['success'] === true));
            $count=(int)select_value("SELECT COUNT(*) FROM lich_hen WHERE ngay_hen=? AND ma_khung_gio=? AND trang_thai <> 'Đã hủy'",[$date,$slot]);
            ensure($success===1 && $count===1, "Concurrent result: {$success} accepted; {$count} rows (expected 1 and 1)");
            return '2 processes submitted concurrently, 1 accepted and 1 rejected; database has exactly 1 appointment.';
        } finally {
            if (!file_exists($barrier)) file_put_contents($barrier,'go');
            foreach($children as $child) { read_child($child); }
            @unlink($barrier); foreach($ready as $p) @unlink($p);
        }
    });

    run_case('TC02','Khach A khong sua duoc xe cua khach B',function()use($ua,$ub,$vb){
        $before=vehicle_get_owned($vb,$ub);
        ensure((bool)$before,'Cannot load B vehicle');
        vehicle_save($ua,$vb,'Changed','Changed','KT-10002',2023,'changed');
        $after=vehicle_get_owned($vb,$ub);
        ensure($after['hang_xe']===$before['hang_xe'] && $after['ghi_chu']===$before['ghi_chu'],'B vehicle changed by A');
        return 'Update attempted with B vehicle ID and A user ID; B vehicle remained unchanged.';
    });

    run_case('TC03','Khach A khong huy duoc lich cua khach B',function()use($ub,$ua,$vb,$service,$slot,$anotherDate){
        $own=appointment_create($ub,$vb,$service,$slot,$anotherDate,'User B own booking');
        $id=(int)$own['ma_lich_hen'];
        $blocked=false;
        try {appointment_cancel($ua,$id);} catch(RuntimeException $e){$blocked=true;}
        $fresh=appointment_get($id);
        ensure($blocked && $fresh['trang_thai']==='Chờ xác nhận','Unauthorized cancellation was accepted');
        return 'The cancellation was rejected; B appointment status remained Cho xac nhan.';
    });

    run_case('TC04','Form thieu token CSRF bi tu choi',function()use($testDb){
        $p=process_child([PHP_BINARY,__DIR__.'/worker_8test.php','csrf',$testDb]);
        $result=read_child($p);
        $out=$result['out'];
        $saved=(int)select_value('SELECT COUNT(*) FROM nguoi_dung WHERE email=?',['csrf-not-blocked@test.local']);
        ensure(str_contains($out,'CSRF_HTTP_STATUS=419') && !str_contains($out,'UNBLOCKED_WRITE') && $saved===0,
            'CSRF missing token was not rejected: '.$out.' '.$result['err']);
        return 'Missing CSRF token triggers HTTP 419, and no user record was created.';
    });

    run_case('TC05','Admin khong giam suc chua thap hon so lich',function()use($ub,$vb,$service,$slot,$date){
        slot_save($slot,'21:00','22:00',2,1);
        appointment_create($ub,$vb,$service,$slot,$date,'Second booking after raising capacity');
        $count=(int)select_value("SELECT COUNT(*) FROM lich_hen WHERE ngay_hen=? AND ma_khung_gio=? AND trang_thai<>'Đã hủy'",[$date,$slot]);
        ensure($count===2,'Fixture must contain two active bookings');
        $blocked=false;
        try {slot_save($slot,'21:00','22:00',1,1);}catch(RuntimeException $e){$blocked=true;}
        $capacity=(int)select_value('SELECT suc_chua_toi_da FROM khung_gio WHERE ma_khung_gio=?',[$slot]);
        ensure($blocked && $capacity===2,'Capacity reduction below existing bookings was accepted');
        return '2 existing bookings; reducing capacity from 2 to 1 was rejected, stored capacity remains 2.';
    });

    run_case('TC06','Admin khong doi gio cua khung da co lich',function()use($slot){
        $blocked=false;
        try {slot_save($slot,'20:30','21:30',2,1);}catch(RuntimeException $e){$blocked=true;}
        $start=(string)select_value('SELECT gio_bat_dau FROM khung_gio WHERE ma_khung_gio=?',[$slot]);
        ensure($blocked && str_starts_with($start,'21:00'),'An already used slot was changed');
        return 'Changing slot time was rejected and start time remains 21:00.';
    });

    run_case('TC07','Tra cuu cuu ho dung ma sai so dien thoai',function(){
        $code=rescue_request_create('Khach cuu ho','0912345678','Ha Noi','Khong no may','Test contact');
        $wrong=rescue_lookup($code,'0999999999');
        $right=rescue_lookup($code,'0912345678');
        ensure($wrong===null && is_array($right) && $right['ma_tra_cuu']===$code,'Rescue privacy check failed');
        return 'Correct code with incorrect phone number returned no data; correct pair returned the rescue status.';
    });

    run_case('TC08','Tai khoan bi khoa khong su dung duoc phien cu',function()use($root,$ub,$testDb){
        user_set_active($root,$ub,false);
        $child=process_child([PHP_BINARY,__DIR__.'/worker_8test.php','locked',$testDb,(string)$ub]);
        $r=read_child($child);
        $state=(string)select_value('SELECT trang_thai_tai_khoan FROM nguoi_dung WHERE ma_nguoi_dung=?',[$ub]);
        ensure($state==='khoa' && str_contains($r['out'],'AUTH_SESSION=CLEARED') && !str_contains($r['out'],'AUTH_ALLOWED'),
            'Locked account still retained auth session: '.$r['out'].' '.$r['err']);
        return 'Backend cleared the existing login session when account status was locked.';
    });
} catch(Throwable $e) {
    echo "[STOP] Cannot execute integration tests: " . $e->getMessage() . "\n";
    $tests[]=['id'=>'SETUP','name'=>'Isolated MySQL test database','status'=>'SKIP','detail'=>$e->getMessage()];
} finally {
    if($dbCreated && $admin instanceof PDO) {
        try {$admin->exec('DROP DATABASE IF EXISTS `'.$testDb.'`'); echo "Temporary database deleted: {$testDb}\n";}
        catch(Throwable $e){echo "WARNING: automatic database removal failed: {$testDb} - {$e->getMessage()}\n";}
    }
    output_report(__DIR__,$tests,$testDb);
}
$passed=count(array_filter($tests,static fn($t)=>$t['status']==='PASS'));
exit($passed===8 ? 0 : 1);
