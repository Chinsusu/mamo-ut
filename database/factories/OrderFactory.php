<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomElement([130000, 178000, 240000, 315000]);
        $shippingFee = fake()->randomElement([0, 25000, 30000, 40000]);
        $paymentMethod = fake()->randomElement(PaymentMethod::cases());
        $token = (string) Str::uuid();

        return [
            'customer_id' => Customer::factory(),
            'code' => Order::nextCode(),
            'idempotency_key' => hash('sha256', 'factory-key:'.$token),
            'idempotency_payload_hash' => hash('sha256', 'factory-payload:'.$token),
            'customer_name' => fake('vi_VN')->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->numerify('09########'),
            'shipping_address_line' => fake('vi_VN')->streetAddress(),
            'shipping_ward' => 'Phường '.fake('vi_VN')->word(),
            'shipping_district' => fake('vi_VN')->randomElement(['Quận 1', 'Quận 3', 'TP Huế']),
            'shipping_province' => fake('vi_VN')->randomElement(['TP. Hồ Chí Minh', 'Thành phố Huế', 'Đà Nẵng']),
            'status' => OrderStatus::New,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentMethod === PaymentMethod::BankTransfer
                ? PaymentStatus::PendingVerification
                : PaymentStatus::Unpaid,
            'payment_reference' => null,
            'subtotal_vnd' => $subtotal,
            'shipping_fee_vnd' => $shippingFee,
            'discount_vnd' => 0,
            'total_vnd' => $subtotal + $shippingFee,
            'payment_expires_at' => $paymentMethod === PaymentMethod::BankTransfer
                ? now()->addHours((int) config('commerce.payment.bank_transfer_hold_hours'))
                : null,
            'placed_at' => now(),
        ];
    }
}
