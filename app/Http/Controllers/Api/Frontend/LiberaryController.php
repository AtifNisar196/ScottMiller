<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBookmarkPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LiberaryController extends Controller
{

    public function getAll()
    {

        if (!auth()->user()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated!'
            ], 402);
        }


        $ordersID = Order::where('user_id', auth()->user()->id)->pluck('id');
        $orderItemIds = OrderItem::whereIn('order_id', $ordersID)->pluck('product_id');

        $userId = auth()->user()->id;

        $products = Product::where('category_id', 1)->whereIn('id', $orderItemIds)
            ->with(['bookmark_page' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])->get();


        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    public function getById($id)
    {
        $userId = auth()->user()->id;

        $product = Product::where('category_id', 1)->where('id', $id)
            ->with(['bookmark_page' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])->first();


        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    public function bookmark_page(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'page_no' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {

            if (!auth()->user()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $product = Product::where('id', $request->product_id)->first();

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid product id!'
                ], 500);
            }

            $checkBookmark = ProductBookmarkPage::where('user_id', auth()->user()->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($checkBookmark) {
                $checkBookmark->page_no = $request->page_no;
                $checkBookmark->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Page Bookmark Updated Successfully!'
                ]);
            } else {
                $bookmark = new ProductBookmarkPage();
                $bookmark->user_id = auth()->user()->id;
                $bookmark->product_id = $request->product_id;
                $bookmark->page_no = $request->page_no;
                $bookmark->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Page Bookmark Successfully!'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!'
            ], 500);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
