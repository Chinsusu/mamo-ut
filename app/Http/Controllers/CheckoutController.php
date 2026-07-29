<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Exceptions\IdempotencyConflict;
use App\Domain\Orders\Services\OrderPlacementService;
use App\Domain\Orders\Services\ShippingFeeCalculator;
use App\Domain\Storefront\Services\GuestCart;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ShopSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly GuestCart $cart,
        private readonly OrderPlacementService $orderPlacement,
        private readonly ShippingFeeCalculator $shippingFeeCalculator,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        $lines = $this->checkoutableLines($request);

        if ($lines === null) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Giỏ hàng đang trống hoặc có sản phẩm không còn đủ tồn kho.',
            ]);
        }

        $subtotalVnd = $lines->sum('line_total_vnd');
        $province = trim($request->string('shipping_province')->toString());
        $shippingFeeVnd = $province === ''
            ? null
            : $this->shippingFeeCalculator->calculate($subtotalVnd, $province);
        $idempotencyKey = Str::uuid()->toString();

        return view('storefront.checkout.create', compact(
            'idempotencyKey',
            'lines',
            'province',
            'shippingFeeVnd',
            'subtotalVnd',
        ));
    }

    public function quote(Request $request): JsonResponse
    {
        $province = trim($request->validate([
            'shipping_province' => ['required', 'string', 'max:255'],
        ])['shipping_province']);
        $lines = $this->checkoutableLines($request);

        if ($lines === null) {
            throw ValidationException::withMessages([
                'cart' => 'Giỏ hàng đang trống hoặc có sản phẩm không còn đủ tồn kho.',
            ]);
        }

        $subtotalVnd = $lines->sum('line_total_vnd');
        $shippingFeeVnd = $this->shippingFeeCalculator->calculate($subtotalVnd, $province);

        return response()->json([
            'shipping_fee_vnd' => $shippingFeeVnd,
            'total_vnd' => $subtotalVnd + $shippingFeeVnd,
        ]);
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'shipping_address_line' => ['required', 'string', 'max:255'],
            'shipping_ward' => ['required', 'string', 'max:255'],
            'shipping_district' => ['required', 'string', 'max:255'],
            'shipping_province' => ['required', 'string', 'max:255'],
            'customer_note' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'idempotency_key' => ['required', 'string', 'max:255'],
        ]);
        $lines = $this->checkoutableLines($request);

        if ($lines === null) {
            throw ValidationException::withMessages([
                'cart' => 'Giỏ hàng đang trống hoặc có sản phẩm không còn đủ tồn kho.',
            ]);
        }

        $payload = [
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'],
            'shipping_address_line' => $validated['shipping_address_line'],
            'shipping_ward' => $validated['shipping_ward'],
            'shipping_district' => $validated['shipping_district'],
            'shipping_province' => $validated['shipping_province'],
            'customer_note' => $validated['customer_note'] ?? null,
            'payment_method' => $validated['payment_method'],
            'items' => $lines
                ->map(fn (array $line): array => [
                    'product_variant_id' => $line['variant']->getKey(),
                    'quantity' => $line['quantity'],
                ])
                ->all(),
        ];

        try {
            $order = $this->orderPlacement->place($payload, $validated['idempotency_key']);
        } catch (IdempotencyConflict) {
            return response()->view('storefront.checkout.conflict', status: 409);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['cart' => $exception->getMessage()]);
        }

        $this->cart->clear();

        return redirect(URL::temporarySignedRoute(
            'checkout.success',
            now()->addDays((int) config('commerce.cart.lifetime_days')),
            ['orderCode' => $order->code],
        ));
    }

    public function success(string $orderCode): View
    {
        /** @var Order $order */
        $order = Order::query()
            ->where('code', $orderCode)
            ->with('items')
            ->firstOrFail();

        /** @var array<string, string|null> $bankDetails */
        $bankDetails = ShopSetting::query()
            ->whereIn('key', ['bank_account_name', 'bank_account_number', 'bank_name', 'qr_image_path'])
            ->pluck('value', 'key');

        return view('storefront.checkout.success', compact('bankDetails', 'order'));
    }

    /**
     * @return Collection<int, array{
     *     variant: ProductVariant,
     *     quantity: int,
     *     line_total_vnd: int,
     *     is_available: bool,
     *     can_fulfill: bool
     * }>|null
     */
    private function checkoutableLines(Request $request): ?Collection
    {
        $lines = $this->cart->lines($request);

        if ($lines->isEmpty() || $lines->contains(fn (array $line): bool => ! $line['can_fulfill'])) {
            return null;
        }

        return $lines;
    }
}
