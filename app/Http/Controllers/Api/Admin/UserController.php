<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function getAll()
    {
        try {

            $users = User::where('user_role', '!=', 'author')
                ->where('user_role', '!=', 'admin')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }

    public function getById(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 500);
            }

            $user = User::where('id', $request->id)->first();

            return response()->json([
                'status' => true,
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }
}
