<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$start = microtime(true);
$requests = 1000;
$passed = 0;

for ($i = 0; $i < $requests; $i++) {
    $request = Illuminate\Http\Request::create('/up', 'GET');
    $response = $kernel->handle($request);
    if ($response->getStatusCode() === 200) {
        $passed++;
    }
    $kernel->terminate($request, $response);
}

$elapsed = microtime(true) - $start;
$rps = $requests / $elapsed;

echo "Total Requests : {$requests}\n";
echo "Passed (200 OK): {$passed}\n";
echo "Elapsed Time   : " . round($elapsed, 4) . " seconds\n";
echo "Throughput     : " . round($rps, 2) . " requests/sec\n";
