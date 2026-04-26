<?php

require __DIR__ . '/vendor/autoload.php';

use Spatie\Browsershot\Browsershot;

try {
    echo "Starting test...\n";
    Browsershot::html('<h1>Hello World</h1>')
        ->setNodeBinary('/Users/cuongpham/.nvm/versions/node/v24.13.0/bin/node')
        ->setNpmBinary('/Users/cuongpham/.nvm/versions/node/v24.13.0/bin/npm')
        ->noSandbox()
        ->save('test_browsershot.pdf');
    echo "Success!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
