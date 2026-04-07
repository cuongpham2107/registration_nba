<?php

use Illuminate\Contracts\Console\Kernel;

use function Spatie\LaravelPdf\Support\pdf;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$out = storage_path('app/public/invoices/_pdf_smoke_test.pdf');

try {
    pdf()->html('<html><body><h1>PDF Smoke Test</h1><p>'.now().'</p></body></html>')
        ->margins(10, 10, 10, 10)
        ->format('A4')
        ->save($out);

    echo "OK: saved to {$out}\n";
} catch (Throwable $e) {
    fwrite(STDERR, "FAIL: {$e->getMessage()}\n");
    fwrite(STDERR, $e->getTraceAsString()."\n");
    exit(1);
}
