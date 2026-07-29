<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'email',
    'phone',
    'phone_normalized',
    'address_line',
    'ward',
    'district',
    'province',
    'notes',
    'last_active_at',
])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Customer $customer): void {
            $customer->phone_normalized = self::normalizePhone($customer->phone);
        });
    }

    private static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        return preg_replace('/\D+/', '', $phone) ?: null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_active_at' => 'immutable_datetime',
        ];
    }
}
