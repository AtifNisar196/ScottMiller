<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CoupenCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{

    public function getByCode(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'subtotal' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {

            $coupen = CoupenCode::where('code', $request->code)->where('status', 1)->first();

            if ($coupen) {

                // Parse the expiry_date from the varchar field
                $expiryDate = Carbon::createFromFormat('Y-m-d', $coupen->expiry_date);

                if ($expiryDate->isPast()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Coupon is expired!'
                    ], 500);
                }

                if ($coupen->discount >= $request->subtotal) {
                    return response()->json([
                        'status' => false,
                        'message' => 'This coupon can only applied on more than $' . $coupen->discount . ' order amount.'
                    ], 500);
                }

                return response()->json([
                    'status' => true,
                    'data' => $coupen
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Code, Not Found or coupon expired!'
                ], 500);
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