<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SessionCartController extends Controller
{
    protected mixed $userId = 2;

    public function __construct()
    {
        Session::put('user_id', $this->userId);
        Session::put('cart_key', "cart_" . $this->userId);
    }

    protected function getUserID()
    {
        return Session::get('user_id');
    }

    protected function getCartKey()
    {
        return Session::get('cart_key');
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

       return $existingItem = $this->productExistsInCart($productId);
        if ($existingItem) {
            return $this->updateCartItem($existingItem['product_id'], $existingItem['quantity'] + 1);
        } else {
            return $this->addCartItem($productId, $quantity, $item_price);
        }
    }

    protected function addCartItem($product_id, $quantity, $price)
    {
        $cartKey = $this->getCartKey();
        $cartItems = Session::get($cartKey, []);
        $item = [
            'item_key' => Str::uuid(),
            'product_id' => $product_id,
            'quantity' => $quantity,
            'item_price' => $price,
            'order_price' => $price * $quantity
        ];
        $cartItems[$product_id] = $item;
        Session::put($cartKey, $cartItems);
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
        $cartItems = Session::get($cartKey, []);
        if (isset($cartItems[$productId])) {
            $cartItems[$productId]['quantity'] = $newQuantity;
            $cartItems[$productId]['order_price'] = $cartItems[$productId]['item_price'] * $newQuantity;
            Session::put($cartKey, $cartItems);

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
        $cartItems = Session::get($cartKey, []);
        if (isset($cartItems[$productId])) {
            unset($cartItems[$productId]);
            Session::put($cartKey, $cartItems);

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
        Session::forget($cartKey);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart has been cleared'
        ]);
    }

    protected function getCartItems()
    {
        $cartKey = $this->getCartKey();
        $cartItems = Session::get($cartKey, []);
        $cartItemsArray = array_values($cartItems);
        $itemTotal = array_reduce($cartItemsArray, fn($carry, $item) => $carry + $item['order_price'], 0);

        return response()->json([
            'item_total' => $itemTotal,
            'items' => $cartItemsArray
        ]);
    }

    function setCartExpiration($expirationInSeconds)
    {
        // Laravel sessions don't have direct expiration control per key.
        // However, you can reset the entire session timeout.
        // This method would be a no-op or could adjust the session lifetime in config if needed.
    }

    protected function productExistsInCart($productId)
    {
        $cartKey = $this->getCartKey();
        $cartItems = Session::get($cartKey, []);
        return $cartItems;
        return $cartItems[$productId] ?? false;
    }

    public function flushCache()
    {
        // For sessions, flushing the cache doesn't apply directly.
        // To clear all session data for the user:
        Session::flush();
    }
}
