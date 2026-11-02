<?php
require __DIR__ . '/../app/bootstrap.php';
logout_user();
flash('success', 'Đã đăng xuất.');
redirect('index.php');
