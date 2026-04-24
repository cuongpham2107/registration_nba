<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/approve/eyJpdiI6ImJVNm9TMEpDY0NEVUNpOTdzWG9ZNmc9PSIsInZhbHVlIjoiV0syVXhKSVJLc3puOUgvcXBSajRYQT09IiwibWFjIjoiYmM0ZmVhNTJiMzdkMzJjZDFkZTIyYTFjY2NkZWMyOGFjZTZmNGFhOTQ2YjhhZTA2NjMxNjVhMDdkZGZiM2UwYiIsInRhZyI6IiJ9?name_manager=Registrations&job_title_manager=', 'GET');
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . substr($response->getContent(), 0, 500) . "\n";
