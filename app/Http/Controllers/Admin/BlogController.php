<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Traits\UploadImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{

    use UploadImageTrait;

    public function getAll()
    {

        $posts = Post::all();

        return response()->json([
            'status' => true,
            'data' => $posts
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'summary' => 'required',
            'description' => 'required',
            'featured_image' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {

            $post = new Post();
            $post->user_id = auth()->user()->id;
            $post->title = $request->title;
            $post->slug = $request->slug;
            $post->summary = $request->summary;
            $post->description = $request->description;
            $post->featured_image = isset($request->featured_image) ? $this->upload_image('/uploads/blog/images/', $request->featured_image) : '';
            $post->save();

            return response()->json([
                'status' => true,
                'data' => $post
            ], 200);
        } catch (\Throwable $th) {
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

            $post = Post::where('id', $id)->first();

            if ($post) {
                $post->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'Deleted Successfully!'
                ]);
            }
            return response()->json([
                'status' => false,
                'message' => 'Invalid ID!'
            ], 500);
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
        // dd($request->all());

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
            $post = Post::where('id', $request->id)->first();
            if ($post) {

                $post->title = isset($request->title) ? $request->title : $post->title;
                $post->slug = isset($request->slug) ? $request->slug : $post->title;
                $post->summary = isset($request->summary) ? $request->summary : $post->summary;
                $post->description = isset($request->description) ? $request->description : $post->description;
                $post->featured_image = isset($request->featured_image) ? $this->upload_image('/uploads/blog/images/', $request->featured_image) : '';
                $post->is_published = isset($request->is_published) ? $request->is_published : $post->is_published;
                $post->save();

                return response()->json([
                    'status' => true,
                    'data' => $post
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => 'Invalid Id, please enter valid id'
                ], 500);
            }
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }
}
