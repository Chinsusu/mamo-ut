<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Storefront\Services\GuestCart;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function __construct(
        private readonly GuestCart $cart,
    ) {}

    public function index(Request $request): View
    {
        $lines = $this->cart->lines($request);
        $subtotalVnd = $lines->sum('line_total_vnd');

        return view('storefront.cart.index', compact('lines', 'subtotalVnd'));
    }

    public function store(Request $request, ProductVariant $productVariant): RedirectResponse
    {
        $quantity = (int) $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ])['quantity'];

        $this->assertQuantityCanBeAdded($request, $productVariant, $quantity);
        $this->cart->add($request, $productVariant->getKey(), $quantity);

        return back()->with('status', 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    public function update(Request $request, ProductVariant $productVariant): RedirectResponse
    {
        $quantity = (int) $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ])['quantity'];

        if ($quantity > 0) {
            $this->assertVariantCanFulfill($productVariant, $quantity);
        }

        $this->cart->set($request, $productVariant->getKey(), $quantity);

        return back()->with('status', 'Đã cập nhật giỏ hàng.');
    }

    public function destroy(Request $request, ProductVariant $productVariant): RedirectResponse
    {
        $this->cart->remove($request, $productVariant->getKey());

        return back()->with('status', 'Đã bỏ sản phẩm khỏi giỏ hàng.');
    }

    private function assertQuantityCanBeAdded(Request $request, ProductVariant $variant, int $quantity): void
    {
        $targetQuantity = $this->cart->quantity($request, $variant->getKey()) + $quantity;

        $this->assertVariantCanFulfill($variant, $targetQuantity);
    }

    private function assertVariantCanFulfill(ProductVariant $variant, int $quantity): void
    {
        $variant->loadMissing('product');
        $product = $variant->product;

        if (
            ! $variant->is_active
            || $product->status !== ProductStatus::Active
            || $product->published_at === null
            || $product->published_at->isFuture()
        ) {
            throw ValidationException::withMessages([
                'quantity' => 'Sản phẩm này hiện chưa sẵn sàng để bán.',
            ]);
        }

        if ($variant->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Số lượng vượt quá tồn kho hiện có.',
            ]);
        }
    }
}
