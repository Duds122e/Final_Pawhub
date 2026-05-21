<?php
putenv('DATABASE_URL=mysql://paw_user:paw_password@127.0.0.1:3310/paw_db?serverVersion=8.0');
$_ENV['DATABASE_URL'] = 'mysql://paw_user:paw_password@127.0.0.1:3310/paw_db?serverVersion=8.0';
require __DIR__ . '/index.php';
