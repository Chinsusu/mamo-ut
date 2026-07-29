<?php

declare(strict_types=1);

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/san-pham', [StorefrontController::class, 'products'])->name('products.index');
Route::get('/san-pham/danh-muc/{category:slug}', [StorefrontController::class, 'category'])
    ->name('categories.show');
Route::get('/san-pham/{product:slug}', [StorefrontController::class, 'product'])
    ->name('products.show');
Route::get('/tim-kiem', [StorefrontController::class, 'search'])->name('search');
Route::get('/ve-o-ut', [StorefrontController::class, 'about'])->name('about');
Route::get('/chinh-sach', [StorefrontController::class, 'policy'])->name('policy');
Route::get('/cam-nang', [StorefrontController::class, 'blog'])->name('blog.index');
Route::get('/cam-nang/{post:slug}', [StorefrontController::class, 'post'])->name('posts.show');
Route::get('/tra-cuu-don', [StorefrontController::class, 'track'])->name('orders.track');
Route::get('/lien-he', [StorefrontController::class, 'contact'])->name('contact');
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang/{productVariant}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/gio-hang/{productVariant}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/{productVariant}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::get('/thanh-toan', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/thanh-toan/bao-gia', [CheckoutController::class, 'quote'])->name('checkout.quote');
Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/dat-hang/thanh-cong/{orderCode}', [CheckoutController::class, 'success'])
    ->middleware('signed')
    ->name('checkout.success');
Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'canonical' => '/up',
]))->name('health');
