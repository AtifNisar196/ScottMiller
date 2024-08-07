<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    public function getAll()
    {
        $categories = Category::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {

            $category = new Category();
            $category->name = $request->name;
            $category->save();

            return response()->json([
                'status' => true,
                'message' => 'New Category Added Successfully!'
            ]);
        } catch (\Throwable $th) {

            Log::error("message:" . $th);

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
