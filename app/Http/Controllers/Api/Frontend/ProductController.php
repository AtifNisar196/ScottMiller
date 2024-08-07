<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductFavourite;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function getAll($page = 10)
    {

        $products = Product::Active()->with('category', 'author', 'reviews.user')->withCount('reviews')->paginate($page);

        if (auth()->user()) {
            foreach ($products as $key => $product) {
                $product['avgRating'] = calculateAvgRating($product->id);

                $checkFavourite = ProductFavourite::where('product_id', $product->id)
                    ->where('user_id', auth()->user()->id)->exists();

                if ($checkFavourite) {
                    $product['my_favourite'] = true;
                } else {
                    $product['my_favourite'] = false;
                }
            }
        } else {
            foreach ($products as $key => $product) {
                $product['my_favourite'] = false;
                $product['my_favourite'] = false;
                $product['avgRating'] = calculateAvgRating($product->id);
            }
        }

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    public function getById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        $product = Product::Active()->where('id', $request->id)->with('category', 'author', 'is_favourite', 'reviews.user')->withCount('reviews')->first();

        if (auth()->user()) {

            $product['avgRating'] = calculateAvgRating($product->id);

            $checkFavourite = ProductFavourite::where('product_id', $product->id)
                ->where('user_id', auth()->user()->id)->exists();

            if ($checkFavourite) {
                $product['my_favourite'] = true;
            } else {
                $product['my_favourite'] = false;
            }
        } else {
            $product['my_favourite'] = false;
            $product['my_favourite'] = false;
            $product['avgRating'] = calculateAvgRating($product->id);
        }

        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }
}
