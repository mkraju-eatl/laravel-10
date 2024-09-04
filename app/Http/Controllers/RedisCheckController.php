<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class RedisCheckController extends Controller
{
    public function storeOnRedis()
    {
        $userId = 1;
        $cartKey = $userId . '_carts';
        $item = [
            'product_id' => 123,
            'quantity' => 2,
            'price' => 49.99
        ];
        // Add item to cart
        Redis::hset($cartKey, $item['product_id'], json_encode($item));
        $cartItems = Redis::hgetall($cartKey);
        $cartItems = array_map('json_decode', $cartItems);
        return $cartItems;
    }

    function addToCart($userId, $productId, $quantity, $price)
    {
        $cartKey = $userId . '_cart';

        // Create the item data
        $item = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $price
        ];

        // Add item to the cart
        Redis::hset($cartKey, $productId, json_encode($item));
    }

    function updateCartItem($userId, $productId, $newQuantity)
    {
        $cartKey = $userId . '_cart';

        // Retrieve the item from the cart
        $item = json_decode(Redis::hget($cartKey, $productId), true);

        if ($item) {
            // Update the item quantity
            $item['quantity'] = $newQuantity;

            // Save the updated item back to Redis
            Redis::hset($cartKey, $productId, json_encode($item));
        }
    }

    function removeFromCart($userId, $productId)
    {
        $cartKey = $userId . '_cart';

        // Remove the item from the cart
        Redis::hdel($cartKey, $productId);
    }

    function clearCart($userId)
    {
        $cartKey = $userId . '_cart';

        // Delete the entire cart
        Redis::del($cartKey);
    }

    function getCartItems($userId)
    {
        $cartKey = $userId . '_cart';

        // Retrieve all items in the cart
        $cartItems = Redis::hgetall($cartKey);

        // Decode the JSON data
        return array_map('json_decode', $cartItems);
    }

    function setCartExpiration($userId, $expirationInSeconds)
    {
        $cartKey = $userId . '_cart';

        // Set the expiration time for the cart
        Redis::expire($cartKey, $expirationInSeconds);
    }

    // addToCart(1, 123, 2, 49.99); Add an item to the cart

    // updateCartItem(1, 123, 3); Update the quantity of an item

    // removeFromCart(1, 123); Remove an item from the cart

    // $cartItems = getCartItems(1); Retrieve all cart items

    // clearCart(1); Clear the cart

    // setCartExpiration(1, 86400); Set cart to expire in 24 hours


}
