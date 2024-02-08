<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

    public function checkout()
    {
        // if cart is empty, redirect to cart page 
        if (Cart::count() == 0) {
            return redirect()->route('front.cart');
        }

        // if user is not logged in, then redirect to login page 
        if (Auth::check() == false) {

            if (!session()->has('url.intended')) {
                session(['url.intended' => url()->current()]);
            }
            return redirect()->route('account.login');
        }

        $customerAddress = CustomerAddress::where('user_id', Auth::user()->id)->first();



        session()->forget('url.intended');

        $countries = Country::orderBy('name', 'ASC')->get();


        return view('front.checkout', [
            'countries' => $countries,
            'customerAddress' => $customerAddress,
        ]);
    }

    public function processCheckout(Request $request)
    {
        // Step 1 ->  apply validation  to the required fields
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:5',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required|min:30',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Please fix the errors',
                'errors' => $validator->errors()
            ]);
        }

        // Step 2 ->  Save customer address
        $user = Auth::user();
        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country,
                'address' => $request->address,
                'apartment' => $request->email,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
            ]
        );

        // Step 3 ->  Store data in orders table
        if ($request->payment_method == 'cod') {
            $shipping = 0;
            $discount = 0;
            $subtotal = Cart::subtotal(2, '.', '');
            $grandTotal = $subtotal + $shipping;

            $order = new Order();
            $order->subtotal = $subtotal;
            $order->shipping = $shipping;
            $order->grand_total = $grandTotal;
            $order->user_id = $user->id;
            $order->first_name = $request->first_name;
            $order->last_name = $request->last_name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->country_id = $request->country;
            $order->address = $request->address;
            $order->apartment = $request->apartment;
            $order->city = $request->city;
            $order->state = $request->state;
            $order->zip = $request->zip;
            $order->notes = $request->order_notes;
            $order->save();


            // Step 4 ->  Store order items in orders items table
            foreach (Cart::content() as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $item->id;
                $orderItem->name = $item->name;
                $orderItem->qty = $item->qty;
                $orderItem->price = $item->price;
                $orderItem->total = $item->price * $item->qty;

                $orderItem->save();
            }
            session()->flash('success', 'You have successfully placed your order.');
            Cart::destroy();
            return response()->json([
                'status' => true,
                'message' => 'Order saved successfully',
                'orderId' => $order->id,
            ]);
        } else {
            //
        }
    }

    public function thankyou($id)
    {

        return view('front.thanks', [
            'id' => $id
        ]);
    }
}
