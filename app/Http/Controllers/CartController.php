<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $product = Product::with('product_images')->find($request->id);

        // Check for product 
        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ]);
        }

        if (Cart::count() > 0) {
            // echo 'Product already in cart';
            // Products count in cart
            //Check if this product already in the cart.
            // Return a message that product is already added in your cart 
            // if product not found in the cart, then add product in the cart 

            $cartContent = Cart::content();

            $productAlreadyExists = false;

            foreach ($cartContent as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExists = true;
                }
            }

            if ($productAlreadyExists == false) {
                Cart::add(
                    $product->id,
                    $product->title,
                    $product->qty,
                    $product->price,
                    ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']
                );
                $status = true;
                $message = $product->title . " added in cart";
            } else {
                $status = false;
                $message = $product->title . " already added in cart";
            }
        } else {
            echo 'Cart is empty now. Adding a product in cart';
            Cart::add(
                $product->id,
                $product->title,
                $product->qty,
                $product->price,
                ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']
            );

            $status = true;
            $message = $product->title . " added in cart";
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }
    public function cart()
    {
        $cartContent = Cart::content();
        // dd($cartContent);
        $data['cartContent'] = $cartContent;
        return view('front.cart', $data);
    }
}
