<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Mail\OrderMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use App\Services\SquareUpService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;

class OrderController extends Controller
{

    protected $squareService;

    public function __construct(SquareUpService $squareService)
    {
        $this->squareService = $squareService;
    }

    public function getAll()
    {

        if (!auth()->user()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated!'
            ], 401);
        }

        $orders = Order::where('user_id', auth()->user()->id)->with('items.product', 'user', 'coupon')->get();

        return response()->json([
            'status' => true,
            'data' => $orders
        ]);
    }

    public function store(OrderRequest $request)
    {

        // dd($request->all());


        try {
            //code...

            $charge = '';

            if (auth()->user()) {
                $user = User::where('id', auth()->user()->id)->first();
                $charge = '';

                try {


                    $payment = $this->squareService->charge($request->total, $request->card_id, $user->email);

                    if ($payment == false) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Payment error occured!!'
                        ], 500);
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                    Log::error($th);
                    return response()->json([
                        'status' => false,
                        'message' => $th->getMessage()
                    ], 500);
                }

                $shipping = '';
                if ($request->shipping_id) {
                    $shipping = Shipping::where('id', $request->shipping_id)->first();
                }



                $order = new Order();
                $order->charge_id = $payment;
                $order->order_id = generateOrderId();
                $order->shipping_id = ($shipping) ? $shipping->id : null;
                $order->user_id = ($user) ? $user->id : NULL;
                $order->name = $user->name;
                $order->country = $request->country;
                $order->city = $request->city;
                $order->state = $request->state;
                $order->post_code = $request->post_code;
                $order->phone = $request->phone;
                $order->email_address = $user->email;
                $order->address = $request->address;
                $order->vat = $request->vat;
                $order->subtotal = $request->subtotal;
                $order->total = $request->total;
                $order->coupon_id = $request->coupon_id;
                $order->status = 'Pending';

                $order->save();

                foreach ($request->product_id as $key => $value) {

                    $order_item = new OrderItem();
                    $order_item->order_id = $order->id;
                    $order_item->product_id = $value;
                    $order_item->qty = $request->qty[$key];
                    $order_item->price = $request->price[$key];
                    $order_item->total = ($request->qty[$key] * $request->price[$key]);
                    $order_item->save();
                }

                $carts = Cart::where('user_id', auth()->user()->id)->get();

                if (count($carts) > 0) {
                    foreach ($carts as $cart) {
                        $cart->delete();
                    }
                }

                lulu_print_jobs($user->id, $request->product_id, $request->qty);

                // $data = [
                //     'title' => 'Your order has been placed successfully [' . $order->order_id . ']',
                //     'body' => 'Dear ' . auth()->user()->name . ', congratulations your order has been placed successfully!' .
                //         ' You can track your order by logging in to your account and soon receive an email about your order status'
                // ];

                // Mail::to($request->email)->send(new OrderMail($data));


                return response()->json([
                    'status' => true,
                    'message' => 'Order has been created!'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized!'
                ], 402);
            }
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}