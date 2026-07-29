<?php

declare(strict_types=1);

return [
    'display_timezone' => env('APP_DISPLAY_TIMEZONE', 'Asia/Ho_Chi_Minh'),

    'cart' => [
        'lifetime_days' => (int) env('CART_LIFETIME_DAYS', 7),
    ],

    'payment' => [
        'bank_transfer_hold_hours' => (int) env('BANK_TRANSFER_HOLD_HOURS', 24),
    ],

    'shipping' => [
        'hue_fee_vnd' => (int) env('SHIPPING_FEE_HUE_VND', 25000),
        'other_fee_vnd' => (int) env('SHIPPING_FEE_OTHER_VND', 40000),
        'free_threshold_vnd' => (int) env('FREE_SHIPPING_THRESHOLD_VND', 500000),
    ],

    'media' => [
        'public_disk' => env('MEDIA_PUBLIC_DISK', 'public'),
    ],
];
