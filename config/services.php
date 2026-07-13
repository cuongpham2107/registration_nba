<?php

return [

    'api_x_token' => env('API_X_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'zalo' => [
        'default_user_id' => env('ZALO_DEFAULT_USER_ID', '3948439024214471746'),
        'webhook_url' => env('ZALO_WEBHOOK_URL', 'http://192.168.1.70:5678/webhook/send-registration'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

    'viettel_invoice' => [
        'username' => env('VIETTEL_INVOICE_USER_NAME'),
        'password' => env('VIETTEL_INVOICE_PASSWORD'),
        'base_url' => env('VIETTEL_INVOICE_BASE_URL', 'https://api-vinvoice.viettel.vn'),
        'template_code' => env('VIETTEL_INVOICE_TEMPLATE_CODE', '5/0078'),
        'invoice_series' => env('VIETTEL_INVOICE_SERIES', 'C25MAA'),
    ],

];
