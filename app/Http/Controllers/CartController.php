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
                    1,
                    $product->price,
                    ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']
                );
                $status = true;
                $message = "<strong>" . $product->title . "</strong> added in your cart successfully.";
                session()->flash('success', $message);
            } else {
                $status = false;
                $message = "<strong>" . $product->title . "</strong> already added in cart.";
                session()->flash('error', $message);
            }
        } else {
            Cart::add(
                $product->id,
                $product->title,
                1,
                $product->price,
                ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']
            );

            $status = true;
            $message = '<strong>' . $product->title . "</strong> added in your cart successfully.";
            session()->flash('success', $message);
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

    public function updateCart(Request $request)
    {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);

        $product = Product::find($itemInfo->id);
        // Check qty available in stock 
        if ($product->track_qty == 'Yes') {
            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully.';
                $status = true;
                session()->flash('success', $message);
            } else {
                $message = 'Requested quantity <strong>(' . $qty . ')</strong> not available in stock';
                $status = false;
                session()->flash('error', $message);
            }
        } else {
            Cart::update($rowId, $qty);
            $message = 'Cart updated successfully.';
            $status = true;
            session()->flash('success', $message);
        }


        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function deleteItem(Request $request)
    {
        $itemInfo = Cart::get($request->rowId);
        if ($itemInfo == null) {
            $errorMessage = 'Item not found in cart';
            session()->flash('success', $errorMessage);
            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }

        Cart::remove($request->rowId);
        $message = 'Item removed from cart successfully.';
        session()->flash('success', $message);
        return response()->json([
            'status' => false,
            'message' => $message
        ]);
    }
}
