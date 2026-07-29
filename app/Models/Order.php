<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Enums\PaymentStatus;
use App\Domain\Orders\Services\OrderInventoryService;
use Carbon\CarbonInterface;
use Database\Factories\OrderFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property string $code
 * @property string $idempotency_key
 * @property string $idempotency_payload_hash
 * @property string $customer_phone
 * @property string|null $internal_note
 * @property OrderStatus $status
 * @property PaymentMethod $payment_method
 * @property PaymentStatus $payment_status
 * @property int $shipping_fee_vnd
 * @property string|null $shipping_fee_adjustment_reason
 * @property CarbonInterface|null $payment_expires_at
 * @property CarbonInterface|null $paid_at
 * @property CarbonInterface|null $inventory_released_at
 * @property CarbonInterface $placed_at
 */ #[Fillable([
    'customer_id',
    'code',
    'idempotency_key',
    'idempotency_payload_hash',
    'customer_name',
    'customer_email',
    'customer_phone',
    'phone_normalized',
    'shipping_address_line',
    'shipping_ward',
    'shipping_district',
    'shipping_province',
    'customer_note',
    'internal_note',
    'status',
    'payment_method',
    'payment_status',
    'payment_reference',
    'subtotal_vnd',
    'shipping_fee_vnd',
    'shipping_fee_adjustment_reason',
    'discount_vnd',
    'total_vnd',
    'payment_expires_at',
    'paid_at',
    'inventory_released_at',
    'placed_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public static function nextCode(): string
    {
        return 'OUT-'.Str::upper(Str::random(14));
    }

    public function canTransitionTo(OrderStatus $next): bool
    {
        if (! $this->status->canTransitionTo($next)) {
            return false;
        }

        return ! (
            $next === OrderStatus::Confirmed
            && $this->payment_method === PaymentMethod::BankTransfer
            && $this->payment_status !== PaymentStatus::Paid
        );
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $seed = (string) Str::uuid();
            $order->code ??= self::nextCode();
            $order->idempotency_key ??= hash('sha256', 'admin-key:'.$seed);
            $order->idempotency_payload_hash ??= hash('sha256', 'admin-payload:'.$seed);
            $order->status ??= OrderStatus::New;
            $order->payment_method ??= PaymentMethod::CashOnDelivery;
            $order->shipping_fee_adjustment_reason = null;
            $order->payment_status ??= $order->payment_method === PaymentMethod::BankTransfer
                ? PaymentStatus::PendingVerification
                : PaymentStatus::Unpaid;
            $order->placed_at ??= now();

            if (
                $order->payment_method === PaymentMethod::BankTransfer
                && $order->payment_expires_at === null
            ) {
                $order->payment_expires_at = now()->addHours(
                    (int) config('commerce.payment.bank_transfer_hold_hours'),
                );
            }
        });

        static::saving(function (Order $order): void {
            $order->phone_normalized = preg_replace('/\D+/', '', $order->customer_phone) ?: '';

            if ($order->payment_status === PaymentStatus::Paid && $order->paid_at === null) {
                $order->paid_at = now();
            }
        });

        static::updating(function (Order $order): void {
            if ($order->isDirty('shipping_fee_vnd')) {
                $reason = trim((string) $order->shipping_fee_adjustment_reason);

                if ($reason === '') {
                    throw new DomainException('Phải nhập lý do khi điều chỉnh phí giao hàng.');
                }

                $order->shipping_fee_adjustment_reason = $reason;
            } elseif ($order->isDirty('shipping_fee_adjustment_reason')) {
                $order->shipping_fee_adjustment_reason = null;
            }

            if (! $order->isDirty('status')) {
                return;
            }

            $from = OrderStatus::from((string) $order->getRawOriginal('status'));
            $to = $order->status;

            if (! $from->canTransitionTo($to)) {
                throw new DomainException("Không thể chuyển đơn từ {$from->value} sang {$to->value}.");
            }

            if (
                $to === OrderStatus::Confirmed
                && $order->payment_method === PaymentMethod::BankTransfer
                && $order->payment_status !== PaymentStatus::Paid
            ) {
                throw new DomainException('Đơn chuyển khoản phải được xác nhận đã thanh toán trước.');
            }
        });

        static::created(function (Order $order): void {
            $order->statusHistory()->create([
                'actor_id' => self::currentActorId(),
                'from_status' => null,
                'to_status' => $order->status,
            ]);
        });

        static::updated(function (Order $order): void {
            if ($order->wasChanged('status')) {
                $order->statusHistory()->create([
                    'actor_id' => self::currentActorId(),
                    'from_status' => (string) $order->getRawOriginal('status'),
                    'to_status' => $order->status,
                ]);

                if ($order->status === OrderStatus::Cancelled) {
                    app(OrderInventoryService::class)->release($order);
                }
            }

            if ($order->wasChanged('shipping_fee_vnd')) {
                AuditLog::query()->create([
                    'actor_id' => self::currentActorId(),
                    'action' => 'order.shipping_fee_updated',
                    'auditable_type' => self::class,
                    'auditable_id' => $order->getKey(),
                    'old_values' => [
                        'shipping_fee_vnd' => (int) $order->getRawOriginal('shipping_fee_vnd'),
                    ],
                    'new_values' => [
                        'shipping_fee_vnd' => $order->shipping_fee_vnd,
                        'reason' => $order->shipping_fee_adjustment_reason,
                    ],
                    'ip_address' => app()->runningInConsole() ? null : request()->ip(),
                ]);

                DB::table($order->getTable())
                    ->where($order->getKeyName(), $order->getKey())
                    ->update(['shipping_fee_adjustment_reason' => null]);
                $order->shipping_fee_adjustment_reason = null;
            }
        });
    }

    private static function currentActorId(): ?int
    {
        $actorId = auth()->id();

        return is_numeric($actorId) ? (int) $actorId : null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'subtotal_vnd' => 'integer',
            'shipping_fee_vnd' => 'integer',
            'discount_vnd' => 'integer',
            'total_vnd' => 'integer',
            'payment_expires_at' => 'immutable_datetime',
            'paid_at' => 'immutable_datetime',
            'inventory_released_at' => 'immutable_datetime',
            'placed_at' => 'immutable_datetime',
        ];
    }
}
