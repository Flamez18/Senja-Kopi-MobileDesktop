<?php

use Illuminate\Support\Facades\Http;

$key = config('midtrans.server_key');

$channels = [
    'gopay' => [
        'payment_type' => 'gopay',
        'transaction_details' => ['order_id' => 'TEST-GP-' . time(), 'gross_amount' => 10000],
    ],
    'qris' => [
        'payment_type' => 'qris',
        'transaction_details' => ['order_id' => 'TEST-QR-' . time(), 'gross_amount' => 10000],
        'qris' => ['acquirer' => 'gopay'],
    ],
    'bca_va' => [
        'payment_type' => 'bank_transfer',
        'transaction_details' => ['order_id' => 'TEST-BCA-' . time(), 'gross_amount' => 10000],
        'bank_transfer' => ['bank' => 'bca'],
    ],
    'permata_va' => [
        'payment_type' => 'bank_transfer',
        'transaction_details' => ['order_id' => 'TEST-PRM-' . time(), 'gross_amount' => 10000],
        'bank_transfer' => ['bank' => 'permata'],
    ],
    'bni_va' => [
        'payment_type' => 'bank_transfer',
        'transaction_details' => ['order_id' => 'TEST-BNI-' . time(), 'gross_amount' => 10000],
        'bank_transfer' => ['bank' => 'bni'],
    ],
];

foreach ($channels as $name => $payload) {
    $response = Http::withoutVerifying()
        ->withBasicAuth($key, '')
        ->post('https://api.sandbox.midtrans.com/v2/charge', $payload);
    
    echo "Channel $name - Status: " . $response->status() . " - Body: " . $response->body() . PHP_EOL;
}
