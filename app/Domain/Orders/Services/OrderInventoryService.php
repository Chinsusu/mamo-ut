<?php

declare(strict_types=1);

namespace App\Domain\Orders\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

final class OrderInventoryService
{
    public function release(Order $order): bool
    {
        $released = DB::transaction(function () use ($order): bool {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->getKey());

            if ($lockedOrder->inventory_released_at !== null) {
                return false;
            }

            OrderItem::query()
                ->where('order_id', $lockedOrder->getKey())
                ->whereNotNull('product_variant_id')
                ->orderBy('id')
                ->each(function (OrderItem $item): void {
                    ProductVariant::query()
                        ->whereKey($item->product_variant_id)
                        ->increment('stock_quantity', $item->quantity);
                });

            $lockedOrder->forceFill(['inventory_released_at' => now()])->save();

            return true;
        });

        $order->refresh();

        return $released;
    }
}
