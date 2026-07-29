<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Enums\PaymentStatus;
use App\Domain\Orders\Exceptions\IdempotencyConflict;
use App\Domain\Orders\Services\ExpireUnpaidBankTransferOrders;
use App\Domain\Orders\Services\OrderPlacementService;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPlacementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_checkout_returns_the_same_order_without_decrementing_stock_twice(): void
    {
        [$variant, $payload] = $this->checkoutFixture(PaymentMethod::CashOnDelivery);
        $service = app(OrderPlacementService::class);

        $firstPayload = $payload;
        $firstPayload['items'] = [
            ['product_variant_id' => $variant->getKey(), 'quantity' => 1],
            ['product_variant_id' => $variant->getKey(), 'quantity' => 1],
        ];
        $replayPayload = $payload;
        $replayPayload['customer_name'] = "  {$payload['customer_name']}  ";
        $replayPayload['customer_email'] = strtoupper($payload['customer_email']);
        $replayPayload['customer_phone'] = '0901234567';

        $firstOrder = $service->place($firstPayload, 'checkout-key-1');
        $secondOrder = $service->place($replayPayload, 'checkout-key-1');

        $this->assertTrue($firstOrder->is($secondOrder));
        $this->assertSame(1, Order::query()->count());
        $this->assertSame(8, $variant->fresh()->stock_quantity);
        $this->assertSame(1, $firstOrder->statusHistory()->count());
    }

    public function test_reusing_an_idempotency_key_with_a_different_payload_is_rejected(): void
    {
        [, $payload] = $this->checkoutFixture(PaymentMethod::CashOnDelivery);
        $service = app(OrderPlacementService::class);
        $service->place($payload, 'checkout-key-2');

        $payload['shipping_address_line'] = 'Địa chỉ khác';

        $this->expectException(IdempotencyConflict::class);
        $service->place($payload, 'checkout-key-2');
    }

    public function test_bank_transfer_must_be_paid_before_confirmation(): void
    {
        [, $payload] = $this->checkoutFixture(PaymentMethod::BankTransfer);
        $order = app(OrderPlacementService::class)->place($payload, 'checkout-key-3');

        $this->assertSame(PaymentStatus::PendingVerification, $order->payment_status);
        $this->assertNotNull($order->payment_expires_at);

        $order->status = OrderStatus::Confirmed;

        try {
            $order->save();
            $this->fail('Unpaid bank transfer should not be confirmable.');
        } catch (DomainException) {
            $order->refresh();
        }

        $order->payment_status = PaymentStatus::Paid;
        $order->status = OrderStatus::Confirmed;
        $order->save();

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
        $this->assertSame(2, $order->statusHistory()->count());
    }

    public function test_expired_bank_transfer_is_cancelled_and_inventory_is_released_once(): void
    {
        [$variant, $payload] = $this->checkoutFixture(PaymentMethod::BankTransfer);
        $order = app(OrderPlacementService::class)->place($payload, 'checkout-key-4');
        $order->forceFill(['payment_expires_at' => now()->subMinute()])->save();

        $expiredCount = app(ExpireUnpaidBankTransferOrders::class)->handle();
        $secondPassCount = app(ExpireUnpaidBankTransferOrders::class)->handle();

        $order->refresh();

        $this->assertSame(1, $expiredCount);
        $this->assertSame(0, $secondPassCount);
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertNotNull($order->inventory_released_at);
        $this->assertSame(10, $variant->fresh()->stock_quantity);
        $this->assertSame(2, $order->statusHistory()->count());
    }

    public function test_shipping_fee_adjustment_requires_a_reason_and_is_audited(): void
    {
        $order = Order::factory()->create([
            'shipping_fee_vnd' => 25000,
            'total_vnd' => 155000,
        ]);
        $order->shipping_fee_vnd = 30000;

        try {
            $order->save();
            $this->fail('Shipping fee changes without a reason must be rejected.');
        } catch (DomainException) {
            $order->refresh();
        }

        $order->fill([
            'shipping_fee_vnd' => 30000,
            'shipping_fee_adjustment_reason' => 'Điều chỉnh theo khu vực giao thực tế',
        ])->save();

        $auditLog = AuditLog::query()
            ->where('action', 'order.shipping_fee_updated')
            ->where('auditable_type', Order::class)
            ->where('auditable_id', $order->getKey())
            ->sole();

        $this->assertSame([
            'shipping_fee_vnd' => 30000,
            'reason' => 'Điều chỉnh theo khu vực giao thực tế',
        ], $auditLog->new_values);
        $this->assertNull($order->fresh()->shipping_fee_adjustment_reason);
    }

    /**
     * @return array{
     *     ProductVariant,
     *     array{
     *         customer_name: string,
     *         customer_email: string,
     *         customer_phone: string,
     *         shipping_address_line: string,
     *         shipping_ward: string,
     *         shipping_district: string,
     *         shipping_province: string,
     *         payment_method: PaymentMethod,
     *         items: list<array{product_variant_id: int, quantity: int}>
     *     }
     * }
     */
    private function checkoutFixture(PaymentMethod $paymentMethod): array
    {
        $product = Product::factory()->create([
            'name' => 'Mắm ruốc',
            'slug' => 'mam-ruoc',
        ]);
        /** @var ProductVariant $variant */
        $variant = $product->defaultVariant()->firstOrFail();
        $variant->forceFill([
            'price_vnd' => 65000,
            'stock_quantity' => 10,
        ])->save();

        return [
            $variant,
            [
                'customer_name' => 'Nguyễn Văn A',
                'customer_email' => 'khach@example.com',
                'customer_phone' => '090 123 4567',
                'shipping_address_line' => '12 Nguyễn Huệ',
                'shipping_ward' => 'Phường Phú Hội',
                'shipping_district' => 'Quận Thuận Hóa',
                'shipping_province' => 'Thành phố Huế',
                'payment_method' => $paymentMethod,
                'items' => [
                    [
                        'product_variant_id' => $variant->getKey(),
                        'quantity' => 2,
                    ],
                ],
            ],
        ];
    }
}
