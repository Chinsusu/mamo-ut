<?php

declare(strict_types=1);

use App\Domain\Orders\Services\ExpireUnpaidBankTransferOrders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(
    fn (): int => app(ExpireUnpaidBankTransferOrders::class)->handle(),
)
    ->name('orders:expire-bank-transfers')
    ->hourly()
    ->withoutOverlapping();
