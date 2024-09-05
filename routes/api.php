<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PracticeController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('store-cache',[PracticeController::class,'storeCache']);
Route::get('get-cache/{id}',[PracticeController::class,'getChache']);

// Cache cart
Route::controller(\App\Http\Controllers\CacheCartController::class)->group(function () {
   Route::get('cart-items','getCartItems')->name('cart-items');
   Route::post('add-to-cart','addToCart')->name('add-to-cart');
   Route::delete('remove-item-from-cart','removeFromCart')->name('remove-item-from-cart');
   Route::get('update-cart-quantity','updateCartQuantity')->name('update-cart-quantity');
   Route::delete('clear-cart','clearCart')->name('clear-cart');
   Route::delete('flush-cache','flushCache')->name('clear-cart');
});

// Redis cart
//Route::controller(\App\Http\Controllers\RedisCartController::class)->group(function () {
//   Route::get('cart-items','getCartItems')->name('cart-items');
//   Route::post('add-to-cart','addToCart')->name('add-to-cart');
//   Route::delete('remove-item-from-cart','removeFromCart')->name('remove-item-from-cart');
//   Route::get('update-cart-quantity','updateCartQuantity')->name('update-cart-quantity');
//   Route::delete('clear-cart','clearCart')->name('clear-cart');
//   Route::delete('flush-cache','flushCache')->name('clear-cart');
//});

// Session cart (not working properly)
//Route::controller(\App\Http\Controllers\SessionCartController::class)->group(function () {
//   Route::get('cart-items','getCartItems')->name('cart-items');
//   Route::post('add-to-cart','addToCart')->name('add-to-cart');
//   Route::delete('remove-item-from-cart','removeFromCart')->name('remove-item-from-cart');
//   Route::get('update-cart-quantity','updateCartQuantity')->name('update-cart-quantity');
//   Route::delete('clear-cart','clearCart')->name('clear-cart');
//   Route::delete('flush-cache','flushCache')->name('clear-cart');
//});

// Cache check
Route::controller(\App\Http\Controllers\CacheController::class)->group(function() {
    Route::get('remember-on-cache','rememberOnCache');
    Route::get('fetch-from-cache','rememberOnCache');
});

// Redis check
Route::controller(\App\Http\Controllers\RedisController::class)->group(function() {
    //
});


