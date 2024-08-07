<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{

    public function getAll($page = 4)
    {
        try {

            $posts = Post::paginate($page);

            return response()->json([
                'status' => true,
                'data' => $posts
            ]);
        } catch (\Throwable $th) {
            Log::error("message: " . $th);

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function getById($id)
    {
        try {

            $post = Post::where('id', $id)->first();

            return response()->json([
                'status' => true,
                'data' => $post
            ]);
        } catch (\Throwable $th) {
            Log::error("message: " . $th);

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
