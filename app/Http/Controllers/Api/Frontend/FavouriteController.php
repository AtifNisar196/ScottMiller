<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductFavourite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FavouriteController extends Controller
{

    public function getAll()
    {


        $favourites = ProductFavourite::where('user_id', auth()->user()->id)->with('user', 'product')->get();

        return response()->json([
            'status' => true,
            'data' => $favourites
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {
            $product = Product::where('id', $request->id)->first();

            if ($product) {

                $checkFavourite = ProductFavourite::where('product_id', $request->id)
                    ->where('user_id', auth()->user()->id)->first();

                if ($checkFavourite) {

                    $checkFavourite->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Product removed from your favourite list!'
                    ]);
                }

                $favourite = new ProductFavourite();
                $favourite->product_id = $product->id;
                $favourite->user_id = auth()->user()->id;
                $favourite->save();

                $favourites = ProductFavourite::where('user_id', auth()->user()->id)->with('user', 'product')->get();

                return response()->json([
                    'status' => true,
                    'data' => $favourites
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Not Found, Invalid ID!'
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
