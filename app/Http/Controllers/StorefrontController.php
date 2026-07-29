<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Content\Enums\BannerPosition;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorefrontController extends Controller
{
    public function home(): View
    {
        $heroBanner = Banner::query()
            ->where('position', BannerPosition::HomeHero)
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->first();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => function (Builder $query): void {
                $query
                    ->where('status', ProductStatus::Active)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            }])
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = $this->visibleProducts()
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $curatedProducts = $this->visibleProducts()
            ->whereIn('slug', [
                'mam-ruoc',
                'mam-ro',
                'cha-ca-lat',
                'mam-tom-chua',
                'mam-tep-chua',
            ])
            ->orderBy('sort_order')
            ->get();

        $latestPosts = Post::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('storefront.home', compact(
            'categories',
            'curatedProducts',
            'featuredProducts',
            'heroBanner',
            'latestPosts',
        ));
    }

    public function products(Request $request): View
    {
        $selectedCategory = null;
        $categorySlug = $request->string('category')->toString();
        $productsQuery = $this->visibleProducts()
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($categorySlug !== '') {
            $selectedCategory = Category::query()
                ->where('slug', $categorySlug)
                ->where('is_active', true)
                ->firstOrFail();

            $productsQuery->where('category_id', $selectedCategory->getKey());
        }

        return $this->productIndexView($productsQuery, $selectedCategory, $request);
    }

    public function category(Category $category, Request $request): View
    {
        abort_unless($category->is_active, 404);

        $productsQuery = $this->visibleProducts()
            ->where('category_id', $category->getKey())
            ->orderBy('sort_order')
            ->orderBy('name');

        return $this->productIndexView($productsQuery, $category, $request);
    }

    public function product(Product $product): View
    {
        abort_unless(
            $product->status === ProductStatus::Active
            && $product->published_at !== null
            && ! $product->published_at->isFuture(),
            404,
        );

        $product->load(['category', 'defaultVariant', 'primaryImage', 'variants', 'images']);

        $relatedProducts = $this->visibleProducts()
            ->whereKeyNot($product->getKey())
            ->when(
                $product->category_id !== null,
                fn (Builder $query) => $query->where('category_id', $product->category_id),
            )
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        return view('storefront.products.show', compact('product', 'relatedProducts'));
    }

    public function search(Request $request): View
    {
        $query = Str::limit(trim($request->string('q')->toString()), 100, '');
        $products = $this->visibleProducts()
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $builder->where(function (Builder $search) use ($query): void {
                    $search
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('short_description', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                });
            })
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        return view('storefront.search', compact('products', 'query'));
    }

    public function about(): View
    {
        return view('storefront.about');
    }

    public function policy(): View
    {
        return view('storefront.policy');
    }

    public function blog(): View
    {
        $posts = Post::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(9);

        return view('storefront.blog.index', compact('posts'));
    }

    public function post(Post $post): View
    {
        abort_unless($post->is_published && $post->published_at?->isPast(), 404);

        return view('storefront.blog.show', compact('post'));
    }

    public function track(Request $request): View
    {
        $order = null;
        $error = null;
        $code = Str::upper(trim($request->string('code')->toString()));
        $phone = preg_replace('/\D+/', '', $request->string('phone')->toString()) ?: '';

        if ($code !== '' || $phone !== '') {
            $candidate = Order::query()->with('statusHistory')->where('code', $code)->first();

            if ($candidate !== null && hash_equals($candidate->phone_normalized, $phone)) {
                $order = $candidate;
            } else {
                $error = 'Không tìm thấy đơn phù hợp. Hãy kiểm tra lại mã đơn và số điện thoại.';
            }
        }

        return view('storefront.orders.track', compact('code', 'error', 'order'));
    }

    public function contact(): View
    {
        return view('storefront.contact');
    }

    /**
     * @return Builder<Product>
     */
    private function visibleProducts(): Builder
    {
        return Product::query()
            ->visible()
            ->with(['category', 'defaultVariant', 'primaryImage']);
    }

    /**
     * @param  Builder<Product>  $productsQuery
     */
    private function productIndexView(Builder $productsQuery, ?Category $selectedCategory, Request $request): View
    {
        $sort = $request->string('sort', 'featured')->toString();
        match ($sort) {
            'price-asc' => $productsQuery->withMin('variants', 'price_vnd')->orderBy('variants_min_price_vnd'),
            'price-desc' => $productsQuery->withMin('variants', 'price_vnd')->orderByDesc('variants_min_price_vnd'),
            'name' => $productsQuery->reorder()->orderBy('name'),
            default => $productsQuery,
        };

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        $products = $productsQuery->paginate(12)->withQueryString();

        return view('storefront.products.index', compact('categories', 'products', 'selectedCategory'));
    }
}
