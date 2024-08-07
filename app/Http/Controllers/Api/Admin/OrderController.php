<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{

    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function getAll()
    {
        try {
            $orders = Order::with('user', 'items', 'coupon')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $orders
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }

    public function changeStatus(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }


        try {

            $refund = '';

            $order = Order::where('id', $id)->first();

            if ($order->status == 'Cancelled' || $order->status == 'Completed') {
                return response()->json([
                    'status' => false,
                    'message' => 'Order is already ' . $order->status . '!'
                ], 500);
            }

            if ($order) {
                // if ($request->status == 'Cancelled') {

                //     $amount = $order->total - $order->vat;

                //     $refund = $this->stripeService->refundCharge($order->charge_id, $amount);
                //     $order->refund_id = $refund->id;
                // }

                $order->status = $request->status;
                $order->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Order Status Changed Successfully!'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid ID, Order Not Found!'
                ], 500);
            }
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }
}
