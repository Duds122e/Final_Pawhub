<?php

// Standalone diagnostics (not routed through Symfony): /diag.php?key=pawhub-diag
if (($_GET['key'] ?? '') !== 'pawhub-diag') {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

require dirname(__DIR__).'/vendor/autoload.php';

$env = getenv('APP_ENV') ?: 'prod';
$debug = filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOL);

try {
    $kernel = new App\Kernel($env, $debug);
    $kernel->boot();
    $container = $kernel->getContainer();

    echo "APP_ENV={$env}\n";
    echo 'DATABASE_URL='.(getenv('DATABASE_URL') ? 'set' : 'MISSING')."\n";
    echo 'DEFAULT_URI='.(getenv('DEFAULT_URI') ?: 'MISSING')."\n\n";

    $conn = $container->get('doctrine')->getConnection();
    foreach (['user', 'pet', 'adoption_request', 'appointment', 'service'] as $table) {
        try {
            echo "{$table}: ".$conn->fetchOne("SELECT COUNT(*) FROM {$table}")."\n";
        } catch (Throwable $e) {
            echo "{$table}: ERROR ".$e->getMessage()."\n";
        }
    }

    echo "\nRendering dashboard via controller:\n";
    $request = Symfony\Component\HttpFoundation\Request::create('/dashboard', 'GET');
    $response = $kernel->handle($request);
    echo 'HTTP '.$response->getStatusCode().' ('.strlen($response->getContent())." bytes)\n";
    if ($response->getStatusCode() >= 500) {
        echo substr($response->getContent(), 0, 2000)."\n";
    }
    $kernel->terminate($request, $response);
} catch (Throwable $e) {
    echo "FATAL: ".$e->getMessage()."\n\n".$e->getTraceAsString();
}
