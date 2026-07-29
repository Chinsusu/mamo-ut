<?php

declare(strict_types=1);

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('phone_normalized')->nullable()->index();
            $table->string('address_line')->nullable();
            $table->string('ward')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_active_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 32)->unique();
            $table->char('idempotency_key', 64)->unique();
            $table->char('idempotency_payload_hash', 64);
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');
            $table->string('phone_normalized')->index();
            $table->string('shipping_address_line');
            $table->string('shipping_ward');
            $table->string('shipping_district');
            $table->string('shipping_province');
            $table->text('customer_note')->nullable();
            $table->text('internal_note')->nullable();
            $table->string('status')->default(OrderStatus::New->value)->index();
            $table->string('payment_method')->default(PaymentMethod::CashOnDelivery->value)->index();
            $table->string('payment_status')->default(PaymentStatus::Unpaid->value)->index();
            $table->string('payment_reference')->nullable();
            $table->unsignedBigInteger('subtotal_vnd')->default(0);
            $table->unsignedBigInteger('shipping_fee_vnd')->default(0);
            $table->unsignedBigInteger('discount_vnd')->default(0);
            $table->unsignedBigInteger('total_vnd')->default(0);
            $table->timestamp('payment_expires_at')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('inventory_released_at')->nullable();
            $table->timestamp('placed_at')->index();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['phone_normalized', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_label')->nullable();
            $table->string('product_sku');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_vnd');
            $table->unsignedBigInteger('line_total_vnd');
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['product_variant_id', 'created_at']);
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
    }
};
