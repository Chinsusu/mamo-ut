<?php

declare(strict_types=1);

namespace App\Domain\Orders\Services;

use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Enums\PaymentStatus;
use App\Domain\Orders\Exceptions\IdempotencyConflict;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use JsonException;

final class OrderPlacementService
{
    public function __construct(
        private readonly ShippingFeeCalculator $shippingFeeCalculator,
    ) {}

    /**
     * @param array{
     *     customer_id?: int|null,
     *     customer_name: string,
     *     customer_email?: string|null,
     *     customer_phone: string,
     *     shipping_address_line: string,
     *     shipping_ward: string,
     *     shipping_district: string,
     *     shipping_province: string,
     *     customer_note?: string|null,
     *     payment_method: PaymentMethod|string,
     *     items: list<array{product_variant_id: int, quantity: int}>
     * } $payload
     */
    public function place(array $payload, string $idempotencyKey): Order
    {
        $idempotencyKey = trim($idempotencyKey);

        if ($idempotencyKey === '') {
            throw new InvalidArgumentException('Idempotency-Key là bắt buộc.');
        }

        $this->validateRequiredFields($payload);

        $keyHash = hash('sha256', $idempotencyKey);
        $payloadHash = $this->payloadHash($payload);

        try {
            return DB::transaction(function () use ($payload, $keyHash, $payloadHash): Order {
                $existingOrder = Order::query()
                    ->where('idempotency_key', $keyHash)
                    ->lockForUpdate()
                    ->first();

                if ($existingOrder !== null) {
                    if (! hash_equals($existingOrder->idempotency_payload_hash, $payloadHash)) {
                        throw new IdempotencyConflict;
                    }

                    return $existingOrder->load('items');
                }

                $paymentMethod = $payload['payment_method'] instanceof PaymentMethod
                    ? $payload['payment_method']
                    : PaymentMethod::from($payload['payment_method']);
                $requestedItems = $this->normalizeItems($payload['items']);
                $variantIds = array_keys($requestedItems);

                /** @var array<int, ProductVariant> $variants */
                $variants = ProductVariant::query()
                    ->with('product')
                    ->whereIn('id', $variantIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id')
                    ->all();

                if (count($variants) !== count($variantIds)) {
                    throw new InvalidArgumentException('Có biến thể sản phẩm không tồn tại.');
                }

                $subtotalVnd = 0;

                foreach ($requestedItems as $variantId => $quantity) {
                    $variant = $variants[$variantId];

                    if (
                        ! $variant->is_active
                        || $variant->product->status !== ProductStatus::Active
                        || $variant->product->published_at === null
                        || $variant->product->published_at->isFuture()
                    ) {
                        throw new InvalidArgumentException('Sản phẩm chưa sẵn sàng để bán.');
                    }

                    if ($variant->stock_quantity < $quantity) {
                        throw new InvalidArgumentException('Tồn kho không đủ.');
                    }

                    $subtotalVnd += $variant->price_vnd * $quantity;
                }

                $shippingFeeVnd = $this->shippingFeeCalculator->calculate(
                    $subtotalVnd,
                    $payload['shipping_province'],
                );
                $paymentStatus = $paymentMethod === PaymentMethod::BankTransfer
                    ? PaymentStatus::PendingVerification
                    : PaymentStatus::Unpaid;

                /** @var Order $order */
                $order = Order::query()->create([
                    'customer_id' => $payload['customer_id'] ?? null,
                    'customer_name' => trim($payload['customer_name']),
                    'customer_email' => $this->normalizeOptionalEmail($payload['customer_email'] ?? null),
                    'customer_phone' => trim($payload['customer_phone']),
                    'shipping_address_line' => trim($payload['shipping_address_line']),
                    'shipping_ward' => trim($payload['shipping_ward']),
                    'shipping_district' => trim($payload['shipping_district']),
                    'shipping_province' => trim($payload['shipping_province']),
                    'customer_note' => $this->normalizeOptionalString($payload['customer_note'] ?? null),
                    'status' => OrderStatus::New,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'subtotal_vnd' => $subtotalVnd,
                    'shipping_fee_vnd' => $shippingFeeVnd,
                    'discount_vnd' => 0,
                    'total_vnd' => $subtotalVnd + $shippingFeeVnd,
                    'idempotency_key' => $keyHash,
                    'idempotency_payload_hash' => $payloadHash,
                    'placed_at' => now(),
                    'payment_expires_at' => $paymentMethod === PaymentMethod::BankTransfer
                        ? now()->addHours((int) config('commerce.payment.bank_transfer_hold_hours'))
                        : null,
                ]);

                foreach ($requestedItems as $variantId => $quantity) {
                    $variant = $variants[$variantId];

                    $order->items()->create([
                        'product_id' => $variant->product_id,
                        'product_variant_id' => $variant->getKey(),
                        'product_name' => $variant->product->name,
                        'variant_label' => $variant->label,
                        'product_sku' => $variant->sku,
                        'quantity' => $quantity,
                        'unit_price_vnd' => $variant->price_vnd,
                        'line_total_vnd' => $variant->price_vnd * $quantity,
                    ]);

                    $variant->decrement('stock_quantity', $quantity);
                }

                return $order->load('items');
            });
        } catch (QueryException $exception) {
            $existingOrder = Order::query()
                ->where('idempotency_key', $keyHash)
                ->first();

            if ($existingOrder === null) {
                throw $exception;
            }

            if (! hash_equals($existingOrder->idempotency_payload_hash, $payloadHash)) {
                throw new IdempotencyConflict;
            }

            return $existingOrder->load('items');
        }
    }

    /**
     * @param  list<array{product_variant_id: int, quantity: int}>  $items
     * @return array<int, int>
     */
    private function normalizeItems(array $items): array
    {
        if ($items === []) {
            throw new InvalidArgumentException('Đơn hàng phải có ít nhất một sản phẩm.');
        }

        $normalized = [];

        foreach ($items as $item) {
            $variantId = (int) $item['product_variant_id'];
            $quantity = (int) $item['quantity'];

            if ($variantId <= 0 || $quantity <= 0) {
                throw new InvalidArgumentException('Biến thể và số lượng phải hợp lệ.');
            }

            $normalized[$variantId] = ($normalized[$variantId] ?? 0) + $quantity;
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * @param array{
     *     customer_name: string,
     *     customer_phone: string,
     *     shipping_address_line: string,
     *     shipping_ward: string,
     *     shipping_district: string,
     *     shipping_province: string
     * } $payload
     */
    private function validateRequiredFields(array $payload): void
    {
        $requiredValues = [
            'Họ tên' => $payload['customer_name'],
            'Số điện thoại' => $payload['customer_phone'],
            'Địa chỉ' => $payload['shipping_address_line'],
            'Phường/Xã' => $payload['shipping_ward'],
            'Quận/Huyện' => $payload['shipping_district'],
            'Tỉnh/Thành' => $payload['shipping_province'],
        ];

        foreach ($requiredValues as $label => $value) {
            if (trim($value) === '') {
                throw new InvalidArgumentException("{$label} là bắt buộc.");
            }
        }

        if ($this->normalizePhone($payload['customer_phone']) === '') {
            throw new InvalidArgumentException('Số điện thoại phải chứa chữ số.');
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws JsonException
     */
    private function payloadHash(array $payload): string
    {
        $items = [];

        foreach ($this->normalizeItems($payload['items']) as $variantId => $quantity) {
            $items[] = [
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
            ];
        }

        $normalizedPayload = [
            'customer_id' => $payload['customer_id'] ?? null,
            'customer_name' => trim($payload['customer_name']),
            'customer_email' => $this->normalizeOptionalEmail($payload['customer_email'] ?? null),
            'customer_phone' => $this->normalizePhone($payload['customer_phone']),
            'shipping_address_line' => trim($payload['shipping_address_line']),
            'shipping_ward' => trim($payload['shipping_ward']),
            'shipping_district' => trim($payload['shipping_district']),
            'shipping_province' => trim($payload['shipping_province']),
            'customer_note' => $this->normalizeOptionalString($payload['customer_note'] ?? null),
            'payment_method' => $payload['payment_method'],
            'items' => $items,
        ];

        return hash(
            'sha256',
            json_encode(
                $this->canonicalize($normalizedPayload),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
            ),
        );
    }

    /**
     * @param  array<array-key, mixed>  $value
     * @return array<array-key, mixed>
     */
    private function canonicalize(array $value): array
    {
        if (! array_is_list($value)) {
            ksort($value);
        }

        foreach ($value as $key => $item) {
            if ($item instanceof PaymentMethod) {
                $value[$key] = $item->value;
            } elseif (is_array($item)) {
                $value[$key] = $this->canonicalize($item);
            } elseif (is_string($item)) {
                $value[$key] = trim($item);
            }
        }

        return $value;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?: '';
    }

    private function normalizeOptionalEmail(?string $email): ?string
    {
        $email = $this->normalizeOptionalString($email);

        return $email === null ? null : strtolower($email);
    }

    private function normalizeOptionalString(?string $value): ?string
    {
        $value = $value === null ? '' : trim($value);

        return $value === '' ? null : $value;
    }
}
