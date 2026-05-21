<?php

header('Content-Type: application/json');

$status = ['status' => 'ok', 'app' => 'up'];

if (isset($_GET['db'])) {
    $url = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
    if (!$url) {
        $status['db'] = 'not_configured';
        $status['status'] = 'degraded';
    } else {
        try {
            $p = parse_url($url);
            $dsn = sprintf(
                'mysql:host=%s;port=%d',
                $p['host'] ?? '127.0.0.1',
                $p['port'] ?? 3306
            );
            new PDO(
                $dsn,
                urldecode($p['user'] ?? 'root'),
                urldecode($p['pass'] ?? '')
            );
            $status['db'] = 'ok';
        } catch (Throwable $e) {
            $status['db'] = 'error';
            $status['status'] = 'degraded';
        }
    }
}

http_response_code($status['status'] === 'ok' ? 200 : 503);
echo json_encode($status);
