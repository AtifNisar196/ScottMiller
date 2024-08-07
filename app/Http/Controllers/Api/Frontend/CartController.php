<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{

    public function getAll()
    {


        $cart = Cart::where('user_id', auth()->user()->id)
        ->with('items.product.category')
        ->first();

        return response()->json([
            'status' => true,
            'data' => $cart
        ]);
    }

    public function store(CartRequest $request)
    {



        $checkCart = Cart::where('user_id', auth()->user()->id)->first();

        if ($checkCart) {

            $checkCart->subtotal = $request->subtotal;
            $checkCart->total = $request->total;
            $checkCart->save();

            $product = Product::where('id', $request->product_id)->first();


            $checkItem = CartItem::where('cart_id', $checkCart->id)->where('product_id', $product->id)->first();

            if ($checkItem) {
                $checkItem->qty += $request->qty;
                $checkItem->save();
            } else {
                $cartItem = new CartItem();
                $cartItem->cart_id = $checkCart->id;
                $cartItem->product_id = $request->product_id;
                $cartItem->price = isset($product) ? $product->price : $request->price;
                $cartItem->qty = $request->qty;
                $cartItem->total = ($request->qty * isset($product) ? $product->price : $request->price);
                $cartItem->save();
            }
        } else {
            $cart = new Cart();
            $cart->user_id = auth()->user()->id;
            $cart->subtotal = $request->subtotal;
            $cart->total = $request->total;
            $cart->save();


            $product = Product::where('id', $request->product_id)->first();

            $cartItem = new CartItem();
            $cartItem->cart_id = $cart->id;
            $cartItem->product_id = $request->product_id;
            $cartItem->price = isset($product) ? $product->price : $request->price;
            $cartItem->qty = $request->qty;
            $cartItem->total = ($request->qty * isset($product) ? $product->price : $request->price);
            $cartItem->save();
        }

        $cart = Cart::where('user_id', auth()->user()->id)->with('items.product.category')->first();

        return response()->json([
            'status' => true,
            'data' => $cart
        ]);
    }

    public function updateItemQuantity(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'cart_id' => 'required',
            'item_id' => 'required',
            'qty' => 'nullable',
            'increment' => 'required',
        ], [
            'increment.required' => 'increment required, Please specify increment 1 or 0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {

            $cart = Cart::where('id', $request->cart_id)
                ->where('user_id', auth()->user()->id)->first();

            if ($cart) {

                $item = CartItem::where('id', $request->item_id)->where('cart_id', $cart->id)->first();

                if ($item) {

                    $item->qty = isset($request->qty) ? $request->qty : $item->qty;
                    $item->save();
                    if ($request->increment == 1) {
                        $cart->subtotal += $item->price * $item->qty;
                        $cart->total += $item->price * $item->qty;
                        $cart->save();
                    } else {
                        $cart->subtotal -= $item->price * $item->qty;
                        $cart->total -= $item->price * $item->qty;
                        $cart->save();
                    }

                    $cart = Cart::where('user_id', auth()->user()->id)->with('items.product.category')->first();

                    return response()->json([
                        'status' => true,
                        'data' => $cart
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid Cart Item ID'
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Cart ID'
                ], 401);
            }
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }

    public function deleteItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => 'required',
            'item_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {
            $cart = Cart::where('id', $request->cart_id)
                ->where('user_id', auth()->user()->id)->first();

            if ($cart) {

                $item = CartItem::where('id', $request->item_id)->where('cart_id', $cart->id)->first();

                if ($item) {

                    $item->delete();

                    $cart->total -= $item->total;
                    $cart->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Deleted Successfully!'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid Cart Item ID'
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Cart ID'
                ], 401);
            }
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }
}