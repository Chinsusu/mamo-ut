<?php

declare(strict_types=1);

namespace App\Domain\Orders\Services;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Enums\PaymentStatus;
use App\Models\Order;

final class ExpireUnpaidBankTransferOrders
{
    public function handle(): int
    {
        $expiredCount = 0;

        Order::query()
            ->where('status', OrderStatus::New)
            ->where('payment_method', PaymentMethod::BankTransfer)
            ->where('payment_status', PaymentStatus::PendingVerification)
            ->whereNotNull('payment_expires_at')
            ->where('payment_expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$expiredCount): void {
                /** @var Order $order */
                foreach ($orders as $order) {
                    $order->forceFill([
                        'status' => OrderStatus::Cancelled,
                        'internal_note' => trim(
                            ($order->internal_note ? $order->internal_note."\n" : '')
                            .'Tự động hủy do quá hạn xác nhận chuyển khoản.',
                        ),
                    ])->save();

                    $expiredCount++;
                }
            });

        return $expiredCount;
    }
}
