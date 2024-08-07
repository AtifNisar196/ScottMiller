<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\ProductFavourite;
use App\Traits\UploadImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{

    use UploadImageTrait;

    public function getAll()
    {
        try {

            if (auth()->user()) {

                if (auth()->user()->user_role == 'author' || auth()->user()->user_role == 'admin') {
                    $products = Product::with('category', 'author')
                        ->orderBy('created_at', 'desc')->get();

                    return response()->json([
                        'status' => true,
                        'data' => $products
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'data' => 'Unauthorized'
                    ], 401);
                }
            } else {

                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'price' => 'required',
            'disc_price' => 'nullable',
            'year' => 'required',
            'publisher' => 'required',
            'wittenby' => 'required',
            'image' => 'required|file|mimes:png,jpg',
            'summary' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {


            if (auth()->user()) {

                if (auth()->user()->user_role == 'author' || auth()->user()->user_role == 'admin') {

                    $category = Category::where('id', $request->category_id)->first();

                    if (!$category) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Invalid Category ID!'
                        ], 500);
                    }

                    $product = new Product();

                    $product->category_id = $request->category_id;
                    $product->author_id = auth()->user()->id;


                    $product->image = isset($request->image) ? $this->upload_image('/uploads/products/images/', $request->image) : '';


                    $product->pdf = isset($request->pdf) ? $this->upload_image('/uploads/products/pdf/', $request->pdf) : '';


                    $product->title = $request->title;
                    $product->price = $request->price;
                    $product->summary = $request->summary;
                    $product->description = $request->description;
                    $product->disc_price = $request->disc_price;
                    $product->wittenby = $request->wittenby;
                    $product->publisher = $request->publisher;
                    $product->year = $request->year;
                    //lulu details
                    $product->lulu_book_id = $request->lulu_book_id;
                    $product->book_size = $request->book_size;
                    $product->page_count = $request->page_count;
                    $product->binding_type = $request->binding_type;
                    $product->interior_color = $request->interior_color;
                    $product->paper_type = $request->paper_type;
                    $product->cover_finish = $request->cover_finish;
                    $product->cover_url = $request->cover_url;
                    $product->interior_url = $request->interior_url;
                    $product->save();

                    return response()->json([
                        'status' => true,
                        'data' => $product
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Unauthorized'
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'category_id' => 'nullable',
            'image' => 'nullable|file|mimes:png,jpg',
            'title' => 'nullable',
            'price' => 'nullable',
            'summary' => 'nullable',
            'description' => 'nullable',
            'is_active' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {

            if (auth()->user()) {
                $category = '';
                if (auth()->user()->user_role == 'author' || auth()->user()->user_role == 'admin') {

                    if (isset($request->category_id)) {
                        $category = Category::where('id', $request->category_id)->first();

                        if (!$category) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Invalid Category ID!'
                            ], 500);
                        }
                    }

                    $product = Product::where('id', $request->id)->first();

                    if ($product) {

                        $product->category_id = isset($request->category_id) ? $request->category_id : $product->category_id;

                        $filename = '';
                        $domain = request()->getSchemeAndHttpHost();

                        $product->image = isset($request->image) ? $this->upload_image('/uploads/products/images/', $request->image) : $product->image;


                        $product->pdf = isset($request->pdf) ? $this->upload_image('/uploads/products/pdf/', $request->pdf) : $product->pdf;


                        $product->title = isset($request->title) ? $request->title : $product->title;
                        $product->price = isset($request->price) ? $request->price : $product->price;
                        $product->disc_price = isset($request->disc_price) ? $request->disc_price : $product->disc_price;
                        $product->wittenby = isset($request->wittenby) ? $request->wittenby : $product->wittenby;
                        $product->publisher = isset($request->publisher) ? $request->publisher : $product->publisher;
                        $product->year = isset($request->year) ? $request->year : $product->year;
                        $product->summary = isset($request->summary) ? $request->summary : $product->summary;
                        $product->description = isset($request->description) ? $request->description : $product->description;
                        $product->is_active = isset($request->is_active) ? $request->is_active : $product->is_active;
                        //lulu details
                        $product->lulu_book_id = isset($request->lulu_book_id) ? $request->lulu_book_id : $product->lulu_book_id;
                        $product->book_size = isset($request->book_size) ? $request->book_size : $product->book_size;
                        $product->page_count = isset($request->page_count) ? $request->page_count : $product->page_count;
                        $product->binding_type = isset($request->binding_type) ? $request->binding_type : $product->binding_type;
                        $product->interior_color = isset($request->interior_color) ? $request->interior_color : $product->interior_color;
                        $product->paper_type = isset($request->paper_type) ? $request->paper_type : $product->paper_type;
                        $product->cover_finish = isset($request->cover_finish) ? $request->cover_finish : $product->cover_finish;
                        $product->cover_url = isset($request->cover_url) ? $request->cover_url : $product->cover_url;
                        $product->interior_url = isset($request->interior_url) ? $request->interior_url : $product->interior_url;
                        $product->save();

                        $product = Product::where('id', $product->id)->with('category', 'author')->first();

                        return response()->json([
                            'status' => true,
                            'data' => $product
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Not Found, Invalid product id'
                        ], 401);
                    }
                } else {

                    return response()->json([
                        'status' => false,
                        'message' => 'Unauthorized'
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized!'
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

    public function delete($id)
    {
        try {

            if (auth()->user()) {
                if (auth()->user()->user_role == 'author' || auth()->user()->user_role == 'admin') {
                    $product = Product::where('id', $id)->first();

                    if ($product) {

                        $cartItem = CartItem::where('product_id', $product->id)->pluck('cart_id');

                        if(count($cartItem) > 0)
                        {
                            foreach ($cartItem as $key => $item) {
                                $cart = Cart::where('id', $item)->first();
                                if($cart)
                                {
                                    $cart->delete();
                                }
                            }
                        }

                        $wishlist = ProductFavourite::where('product_id', $product->id)->get();

                        if(count($wishlist) > 0)
                        {
                            foreach ($wishlist as $key => $data) {
                                $data->delete();
                            }
                        }


                        $product->delete();

                        return response()->json([
                            'status' => true,
                            'data' => 'Deleted Successfully!'
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Not Found, Invalid product id'
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Unauthorized'
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized!'
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

    public function getById($id)
    {
        try {

            if (auth()->user()) {
                if (auth()->user()->user_role == 'author' || auth()->user()->user_role == 'admin') {
                    $product = Product::where('author_id', auth()->user()->id)
                        ->where('id', $id)->with('category', 'author')->first();

                    $product->makeVisible(['interior_url', 'cover_url']);

                    if ($product) {


                        return response()->json([
                            'status' => true,
                            'data' => $product
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Not Found, Invalid product id'
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Unauthorized!'
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized!'
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

    public function changeStatus(Request $request)
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

        $product = Product::where('id', $request->id)->first();

        if ($product) {

            if ($product->is_active == 1) {
                $product->is_active = 0;
                $product->save();
            } else {
                $product->is_active = 1;
                $product->save();
            }

            return response()->json([
                'status' => true,
                'data' => 'Status Updated Successfully!'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => 'Not Found, Invalid ID!'
            ], 500);
        }
    }
}
