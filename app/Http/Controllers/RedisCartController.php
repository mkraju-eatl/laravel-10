<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RedisCartController extends Controller
{
    protected mixed $userId = 2;

    public function __construct()
    {
        Redis::setex('user_id', 86400, $this->userId); // Cache for 24 hours
        Redis::setex('cart_key', 86400, "cart_" . $this->userId); // Cache for 24 hours
    }

    public function flushCache()
    {
        return Redis::flushall();
        // or if you want to clear only the current database
        // return Redis::flushdb();
    }


    protected function getUserID()
    {
        return Redis::get('user_id');
    }

    protected function getCartKey()
    {
        return Redis::get('cart_key');
    }

    public function addToCart(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required',
            'quantity' => 'required|int',
            'item_price' => 'required'
        ]);

        $userId = $this->getUserID();
        $productId = $request->product_id;
        $quantity = $request->quantity;
        $item_price = $request->item_price;

        $existingItem = $this->productExistsInCart($productId);
        if ($existingItem) {
            return $this->updateCartItem($existingItem['product_id'], $existingItem['quantity'] + 1);
        } else {
            return $this->addCartItem($productId, $quantity, $item_price);
        }
    }

    protected function addCartItem($product_id, $quantity, $price)
    {
        $cartKey = $this->getCartKey();
        $cartItems = json_decode(Redis::get($cartKey), true) ?? [];
        $item = [
            'item_key' => Str::uuid(),
            'product_id' => $product_id,
            'quantity' => $quantity,
            'item_price' => $price,
            'order_price' => $price * $quantity
        ];
        $cartItems[$product_id] = $item;
        Redis::setex($cartKey, 86400, json_encode($cartItems)); // Cache for 24 hours

        return response()->json([
            'status' => 'success',
            'message' => 'Product has been added to your cart successfully!'
        ]);
    }

    protected function updateCartQuantity()
    {
        $validator = Validator::make(request()->all(), [
            'type' => 'required|in:increment,decrement',
            'product_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'errors',
                'errors' => $validator->messages()
            ]);
        }

        $type = request()->get('type') ?? 'increment';
        $productId = request()->get('product_id');
        $existingItem = $this->productExistsInCart($productId);
        if (!$existingItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid cart item'
            ]);
        }

        $newQuantity = ($type === "increment") ? $existingItem['quantity'] + 1 : $existingItem['quantity'] - 1;
        $this->updateCartItem($productId, $newQuantity);
        return true;
    }

    protected function updateCartItem($productId, $newQuantity)
    {
        $cartKey = $this->getCartKey();
        $cartItems = json_decode(Redis::get($cartKey), true) ?? [];
        if (isset($cartItems[$productId])) {
            $cartItems[$productId]['quantity'] = $newQuantity;
            $cartItems[$productId]['order_price'] = $cartItems[$productId]['item_price'] * $newQuantity;
            Redis::setex($cartKey, 86400, json_encode($cartItems)); // Cache for 24 hours

            return response()->json([
                'status' => 'success',
                'message' => 'Item has been updated successfully!'
            ]);
        }
    }

    function removeFromCart()
    {
        $productId = request()->get('product_id');
        $cartKey = $this->getCartKey();
        $cartItems = json_decode(Redis::get($cartKey), true) ?? [];
        if (isset($cartItems[$productId])) {
            unset($cartItems[$productId]);
            Redis::setex($cartKey, 86400, json_encode($cartItems)); // Cache for 24 hours

            return response()->json([
                'status' => 'success',
                'message' => 'Item has been removed from cart'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid cart item'
            ]);
        }
    }

    function clearCart()
    {
        $cartKey = $this->getCartKey();
        Redis::del($cartKey); // Delete the cart from Redis

        return response()->json([
            'status' => 'success',
            'message' => 'Cart has been cleared'
        ]);
    }

    protected function getCartItems()
    {
        $cartKey = $this->getCartKey();
        $cartItems = json_decode(Redis::get($cartKey), true) ?? [];
        $cartItemsArray = array_values($cartItems);
        $itemTotal = array_reduce($cartItemsArray, fn($carry, $item) => $carry + $item['order_price'], 0);

        return response()->json([
            'item_total' => $itemTotal,
            'items' => $cartItemsArray
        ]);
    }

    function setCartExpiration($expirationInSeconds)
    {
        $cartKey = $this->getCartKey();
        $cartItems = json_decode(Redis::get($cartKey), true) ?? [];
        if (!empty($cartItems)) {
            Redis::setex($cartKey, $expirationInSeconds, json_encode($cartItems));
        }
    }

    protected function productExistsInCart($productId)
    {
        $cartKey = $this->getCartKey();
        $cartItems = json_decode(Redis::get($cartKey), true) ?? [];
        return $cartItems[$productId] ?? false;
    }
}
