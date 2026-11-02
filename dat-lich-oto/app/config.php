<?php
return array_replace([
    'app_name' => 'CAR SERVICE',
    'base_url' => '/dat-lich-oto/public',
    'db_host' => '127.0.0.1',
    'db_port' => '3306',
    'db_name' => 'dat_lich_oto',
    'db_user' => 'root',
    'db_pass' => '',
    // Có thể đặt GEMINI_API_KEY trong biến môi trường, hoặc app/local.php riêng.
    'gemini_api_key' => (string) (getenv('GEMINI_API_KEY') ?: ''),
    'gemini_model' => 'gemini-2.5-flash-lite',
    'timezone' => 'Asia/Ho_Chi_Minh',

    'garage_address' => 'Chùa Hưng Ký',
    'garage_phone' => '0983104905',
    'garage_email' => 'tuyentihon98@gmail.com',
    'garage_hours' => '07:00 - 23:00',
    'garage_facebook' => 'https://www.facebook.com/share/1V1LGAgd98/?mibextid=wwXIfr',
], is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : []);
