<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::view('/gio-hang', 'checkout.cart')->name('cart');
Route::view('/dat-hang', 'checkout.checkout')->name('checkout');
Route::get('/dat-hang/thanh-cong/{order:order_number}', [PageController::class, 'orderSuccess'])->name('checkout.success');
Route::get('/tra-cuu-don', [OrderTrackingController::class, 'form'])->name('orders.track.form');
Route::post('/tra-cuu-don', [OrderTrackingController::class, 'lookup'])->name('orders.track');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::view('/ve-o-ut', 'storefront.about')->name('about');
Route::view('/chinh-sach', 'storefront.policy')->name('policy');
Route::view('/lien-he', 'storefront.contact')->name('contact');
