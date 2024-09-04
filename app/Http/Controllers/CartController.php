<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CartController extends Controller
{
    protected mixed $userId = 2;

    public function __construct()
    {
        Cache::put('user_id', $this->userId, now()->addHours(24)); // Cache for 24 hours
        Cache::put('cart_key', "cart_".$this->userId, now()->addHours(24)); // Cache for 24 hours
    }

    public function flushCache() {
        return Cache::flush();
    }

    protected function getUserID()
    {
        return Cache::get('user_id');
    }

    protected function getCartKey()
    {
        return Cache::get('cart_key');
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
        // Check if the product exists in the cart
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
        // Get existing cart items or initialize an empty array
        $cartItems = Cache::get($cartKey, []);
        // Create the item data
        $item = [
            'item_key' => Str::uuid(),
            'product_id' => $product_id,
            'quantity' => $quantity,
            'item_price' => $price,
            'order_price' => $price * $quantity
        ];
        // Add or update the item in the cart
        $cartItems[$product_id] = $item;
        // Store the updated cart in the cache
        Cache::put($cartKey, $cartItems, now()->addHours(24)); // Cache for 24 hours
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
        if(!$existingItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid cart item'
            ]);
        }
        $newQuantity = 0;
        if ($type === "increment") {
            $newQuantity = $existingItem['quantity'] + 1;
        } elseif ($type === "decrement") {
            $newQuantity = $existingItem['quantity'] - 1;
        }
        $this->updateCartItem($productId, $newQuantity);
        return true;
    }

    protected function updateCartItem($productId, $newQuantity)
    {
        $cartKey = $this->getCartKey();
        // Retrieve the cart items
        $cartItems = Cache::get($cartKey, []);
        if (isset($cartItems[$productId])) {
            // Update the item quantity
            $cartItems[$productId]['quantity'] = $newQuantity;
            $cartItems[$productId]['order_price'] = $cartItems[$productId]['item_price'] * $newQuantity;
            // Save the updated cart back to the cache
            Cache::put($cartKey, $cartItems, now()->addHours(24));
            return response()->json([
                'status' => 'success',
                'message' => 'Item has been added successfully!'
            ]);
        }
    }

    function removeFromCart()
    {
        $productId = request()->get('product_id');
        $cartKey = $this->getCartKey();
        // Retrieve the cart items
        $cartItems = Cache::get($cartKey, []);
        if (isset($cartItems[$productId])) {
            // Remove the item
            unset($cartItems[$productId]);
            // Update the cart in the cache
            Cache::put($cartKey, $cartItems, now()->addHours(24));
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
        // Forget the entire cart from the cache
        Cache::forget($cartKey);
        return response()->json([
            'status' => 'success',
            'message' => 'Cart item removed'
        ]);
    }

    protected function getCartItems()
    {
        $cartKey = $this->getCartKey();
        // Retrieve all items in the cart
        $cartItems = Cache::get($cartKey, []);
        // Transform the associative array into an array of objects
        $cartItemsArray = array_values($cartItems);
        // Calculate the total price of all items in the cart
        $itemTotal = array_reduce($cartItemsArray, function ($carry, $item) {
            //return $carry + ($item['item_price'] * $item['quantity']);
            return $carry + $item['order_price'];
        }, 0);
        return response()->json([
            'item_total' => $itemTotal,
            'items' => $cartItemsArray
        ]);
    }


    function setCartExpiration($expirationInSeconds)
    {
        $cartKey = $this->getCartKey();
        // Retrieve the current cart
        $cartItems = Cache::get($cartKey, []);
        if (!empty($cartItems)) {
            // Update the cart with a new expiration time
            Cache::put($cartKey, $cartItems, now()->addSeconds($expirationInSeconds));
        }
    }

    protected function productExistsInCart($productId)
    {
        $cartKey = $this->getCartKey();
        // Retrieve the cart items
        $cartItems = Cache::get($cartKey, []);
        // Check if the product_id exists in any cart item
        foreach ($cartItems as $item) {
            if ($item['product_id'] == $productId) {
                return $item; // Return the item if found
            }
        }
        return false; // Return false if the product_id does not exist
    }

}
