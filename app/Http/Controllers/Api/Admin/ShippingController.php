<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{

    public function getAll()
    {
        $shippings = Shipping::first();

        return response()->json([
            'status' => true,
            'data' => $shippings
        ]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'amount' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {

            $shipping = Shipping::first();

            if ($shipping) {
                $shipping->name = isset($request->name) ? $request->name : $shipping->name;
                $shipping->amount = isset($request->amount) ? $request->amount : $shipping->amount;
                $shipping->save();
            } else {
                $shipping = Shipping::first();
                $shipping->name = $request->name;
                $shipping->amount = $request->amount;
                $shipping->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Shipping Updated Successfully!'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}