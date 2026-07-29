<?php

declare(strict_types=1);

namespace App\Domain\Storefront\Services;

use App\Domain\Catalog\Enums\ProductStatus;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use JsonException;

final class GuestCart
{
    private const COOKIE_NAME = 'mamo_ut_cart';

    private const MAX_LINE_ITEMS = 25;

    private const MAX_QUANTITY = 99;

    /**
     * @return array<int, int>
     */
    public function quantities(Request $request): array
    {
        $cookieValue = $request->cookie(self::COOKIE_NAME);

        if (! is_string($cookieValue) || $cookieValue === '') {
            return [];
        }

        try {
            $payload = json_decode($cookieValue, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($payload) || ($payload['version'] ?? null) !== 1 || ! is_array($payload['items'] ?? null)) {
            return [];
        }

        $quantities = [];

        foreach ($payload['items'] as $variantId => $quantity) {
            if (! is_numeric($variantId) || ! is_numeric($quantity)) {
                continue;
            }

            $variantId = (int) $variantId;
            $quantity = (int) $quantity;

            if ($variantId <= 0 || $quantity <= 0) {
                continue;
            }

            $quantities[$variantId] = min(self::MAX_QUANTITY, $quantity);
        }

        ksort($quantities);

        return array_slice($quantities, 0, self::MAX_LINE_ITEMS, true);
    }

    public function quantity(Request $request, int $variantId): int
    {
        return $this->quantities($request)[$variantId] ?? 0;
    }

    public function count(Request $request): int
    {
        return array_sum($this->quantities($request));
    }

    public function add(Request $request, int $variantId, int $quantity): void
    {
        $items = $this->quantities($request);

        if (! array_key_exists($variantId, $items) && count($items) >= self::MAX_LINE_ITEMS) {
            return;
        }

        $items[$variantId] = min(self::MAX_QUANTITY, ($items[$variantId] ?? 0) + $quantity);

        $this->store($items);
    }

    public function set(Request $request, int $variantId, int $quantity): void
    {
        $items = $this->quantities($request);

        if ($quantity <= 0) {
            unset($items[$variantId]);
        } elseif (array_key_exists($variantId, $items) || count($items) < self::MAX_LINE_ITEMS) {
            $items[$variantId] = min(self::MAX_QUANTITY, $quantity);
        }

        $this->store($items);
    }

    public function remove(Request $request, int $variantId): void
    {
        $items = $this->quantities($request);
        unset($items[$variantId]);

        $this->store($items);
    }

    public function clear(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    /**
     * @return Collection<int, array{
     *     variant: ProductVariant,
     *     quantity: int,
     *     line_total_vnd: int,
     *     is_available: bool,
     *     can_fulfill: bool
     * }>
     */
    public function lines(Request $request): Collection
    {
        $quantities = $this->quantities($request);

        if ($quantities === []) {
            return collect();
        }

        /** @var Collection<int, ProductVariant> $variants */
        $variants = ProductVariant::query()
            ->with('product.category')
            ->whereKey(array_keys($quantities))
            ->get()
            ->keyBy('id');

        return collect($quantities)
            ->map(function (int $quantity, int $variantId) use ($variants): ?array {
                /** @var ProductVariant|null $variant */
                $variant = $variants->get($variantId);

                if ($variant === null) {
                    return null;
                }

                $product = $variant->product;
                $isAvailable = $variant->is_active
                    && $product->status === ProductStatus::Active
                    && $product->published_at !== null
                    && ! $product->published_at->isFuture();

                return [
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'line_total_vnd' => $variant->price_vnd * $quantity,
                    'is_available' => $isAvailable,
                    'can_fulfill' => $isAvailable && $variant->stock_quantity >= $quantity,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @param  array<int, int>  $items
     */
    private function store(array $items): void
    {
        ksort($items);

        Cookie::queue(cookie(
            self::COOKIE_NAME,
            json_encode(['version' => 1, 'items' => $items], JSON_THROW_ON_ERROR),
            (int) config('commerce.cart.lifetime_days') * 24 * 60,
            '/',
            null,
            null,
            true,
            false,
            'lax',
        ));
    }
}
