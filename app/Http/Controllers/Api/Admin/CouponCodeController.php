<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoupenCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CouponCodeController extends Controller
{
    public function getAll()
    {

        $coupens = CoupenCode::all();

        return response()->json([
            'status' => true,
            'data' => $coupens
        ]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:coupen_codes,name',
            'code' => 'required|unique:coupen_codes,code',
            'type' => 'required',
            'discount' => 'required',
            'expiry_date' => 'required',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {
            $coupen = new CoupenCode();
            $coupen->name = $request->name;
            $coupen->code = $request->code;
            $coupen->type = $request->type;
            $coupen->discount = $request->discount;
            $coupen->expiry_date = $request->expiry_date;
            $coupen->status = isset($request->status) ? $request->status : true;
            $coupen->save();

            return response()->json([
                'status' => true,
                'message' => 'Coupen Generated Successfully!'
            ]);
        } catch (\Throwable $th) {
            Log::error("message: " . $th);

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'nullable|unique:coupen_codes,name',
            'code' => 'nullable|unique:coupen_codes,code',
            'type' => 'nullable',
            'discount' => 'nullable',
            'expiry_date' => 'nullable',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {
            $coupon = CoupenCode::where('id', $request->id)->first();

            if ($coupon) {
                $coupon->name = ($request->name) ? $request->name : $coupon->name;
                $coupon->code = ($request->name) ? $request->code : $coupon->code;
                $coupon->type = ($request->name) ? $request->type : $coupon->type;
                $coupon->discount = ($request->name) ? $request->discount : $coupon->discount;
                $coupon->expiry_date = ($request->name) ? $request->expiry_date : $coupon->expiry_date;
                $coupon->status = isset($request->status) ? $request->status : true;
                $coupon->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Coupen Generated Successfully!'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid ID, Not Found!'
                ], 500);
            }
        } catch (\Throwable $th) {
            Log::error("message: " . $th);

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function toggleStatus($id)
    {

        $coupen = CoupenCode::where('id', $id)->first();

        if ($coupen) {
            if ($coupen->status == 1) {
                $coupen->status = false;
                $coupen->save();
            } else {
                $coupen->status = true;
                $coupen->save();
            }

            return response()->json([
                'status' => true,
                'data' => 'Status Updated Successfully!'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => 'Not Found, Invalid ID!'
            ],  500);
        }
    }
}
