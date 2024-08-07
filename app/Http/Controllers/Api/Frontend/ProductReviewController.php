<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductReviewController extends Controller
{

    public function getAll()
    {
        $reviews = ProductReview::all();

        return response()->json([
            'status' => true,
            'data' => $reviews
        ]);
    }

    public function store(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'product_id' => 'required',
                'review' => 'required',
                'rating' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 500);
            }

            $review = new ProductReview();
            $review->product_id = $request->product_id;
            $review->user_id = auth()->user()->id;
            $review->review = $request->review;
            $review->rating = $request->rating;
            $review->save();

            return response()->json([
                'status' => true,
                'data' => $review
            ]);
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
