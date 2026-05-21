<?php

// Standalone diagnostics: /diag.php?key=pawhub-diag
if (($_GET['key'] ?? '') !== 'pawhub-diag') {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

require dirname(__DIR__).'/vendor/autoload.php';

$env = getenv('APP_ENV') ?: 'prod';
$debug = isset($_GET['debug']) && $_GET['debug'] === '1';

try {
    $kernel = new App\Kernel($env, $debug);
    $kernel->boot();
    $container = $kernel->getContainer();

    $lines = [];
    $lines[] = 'APP_ENV='.$env;
    $lines[] = 'DATABASE_URL='.(getenv('DATABASE_URL') ? 'set' : 'MISSING');
    $lines[] = 'DEFAULT_URI='.(getenv('DEFAULT_URI') ?: 'MISSING');
    $lines[] = 'RAILWAY_PUBLIC_DOMAIN='.(getenv('RAILWAY_PUBLIC_DOMAIN') ?: 'not set');
    $lines[] = 'session.save_path='.ini_get('session.save_path');
    $lines[] = '';

    $conn = $container->get('doctrine')->getConnection();
    foreach (['user', 'pet', 'adoption_request', 'appointment', 'service'] as $table) {
        try {
            $lines[] = "{$table}: ".$conn->fetchOne("SELECT COUNT(*) FROM {$table}");
        } catch (Throwable $e) {
            $lines[] = "{$table}: ERROR ".$e->getMessage();
        }
    }

    $lines[] = '';
    $lines[] = 'Dashboard request (unauthenticated — expect 302 or 401, not 500):';

    ob_start();
    $request = Symfony\Component\HttpFoundation\Request::create('/dashboard', 'GET');
    $response = $kernel->handle($request);
    ob_end_clean();

    $lines[] = 'HTTP '.$response->getStatusCode().' ('.strlen($response->getContent()).' bytes)';
    if ($response->getStatusCode() >= 500) {
        $lines[] = substr($response->getContent(), 0, 1500);
    }
    $kernel->terminate($request, $response);

    echo implode("\n", $lines);
} catch (Throwable $e) {
    echo "FATAL: ".$e->getMessage()."\n\n".$e->getTraceAsString();
}
