<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Orders\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_a_product_and_see_shipping_quote_before_checkout(): void
    {
        $variant = $this->saleableVariant();
        $cartCookie = $this->addToCart($variant, 2);

        $this
            ->withCookie('mamo_ut_cart', $cartCookie)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee($variant->product->name)
            ->assertSee('130.000 đ');

        $this
            ->withCredentials()
            ->withCookie('mamo_ut_cart', $cartCookie)
            ->postJson(route('checkout.quote'), ['shipping_province' => 'Thành phố Huế'])
            ->assertOk()
            ->assertJsonPath('shipping_fee_vnd', 25000)
            ->assertJsonPath('total_vnd', 155000);
    }

    public function test_guest_checkout_creates_order_and_returns_signed_confirmation(): void
    {
        $variant = $this->saleableVariant();
        $cartCookie = $this->addToCart($variant, 2);

        $response = $this
            ->withCookie('mamo_ut_cart', $cartCookie)
            ->post(route('checkout.store'), $this->checkoutPayload('guest-checkout-key-1'));

        $response
            ->assertRedirect()
            ->assertCookieExpired('mamo_ut_cart');

        /** @var Order $order */
        $order = Order::query()->sole();

        $this->assertSame(PaymentMethod::CashOnDelivery, $order->payment_method);
        $this->assertSame(155000, $order->total_vnd);
        $this->assertSame(8, $variant->fresh()->stock_quantity);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee($order->code)
            ->assertSee('Đặt hàng thành công');
    }

    public function test_reusing_checkout_key_with_different_guest_payload_returns_conflict(): void
    {
        $variant = $this->saleableVariant();
        $cartCookie = $this->addToCart($variant, 1);
        $idempotencyKey = 'guest-checkout-key-2';

        $this
            ->withCookie('mamo_ut_cart', $cartCookie)
            ->post(route('checkout.store'), $this->checkoutPayload($idempotencyKey))
            ->assertRedirect();

        $conflictingPayload = $this->checkoutPayload($idempotencyKey);
        $conflictingPayload['customer_name'] = 'Khách khác';

        $this
            ->withCookie('mamo_ut_cart', $cartCookie)
            ->post(route('checkout.store'), $conflictingPayload)
            ->assertStatus(409)
            ->assertSee('Yêu cầu đặt hàng này không còn hợp lệ');

        $this->assertSame(1, Order::query()->count());
    }

    private function saleableVariant(): ProductVariant
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

        return $variant->fresh();
    }

    private function addToCart(ProductVariant $variant, int $quantity): string
    {
        $response = $this->post(route('cart.store', $variant), ['quantity' => $quantity]);

        $response->assertRedirect()->assertCookie('mamo_ut_cart');

        /** @var Cookie $cookie */
        $cookie = $response->getCookie('mamo_ut_cart');

        return (string) $cookie->getValue();
    }

    /**
     * @return array<string, string>
     */
    private function checkoutPayload(string $idempotencyKey): array
    {
        return [
            'customer_name' => 'Nguyễn Văn A',
            'customer_email' => 'khach@example.com',
            'customer_phone' => '0901234567',
            'shipping_address_line' => '12 Nguyễn Huệ',
            'shipping_ward' => 'Phường Phú Hội',
            'shipping_district' => 'Quận Thuận Hóa',
            'shipping_province' => 'Thành phố Huế',
            'customer_note' => '',
            'payment_method' => PaymentMethod::CashOnDelivery->value,
            'idempotency_key' => $idempotencyKey,
        ];
    }
}
