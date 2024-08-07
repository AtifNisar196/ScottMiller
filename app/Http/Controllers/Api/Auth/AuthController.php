<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {

            if (Hash::check($request->password, $user->password)) {

                $credentials = request(['email', 'password']);

                if (!$token = auth()->attempt($credentials)) {
                    return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
                }

                return response()->json([
                    'status' => true,
                    'token' => $this->respondWithToken($token),
                    'data' => $user
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Password, Please try again!'
                ], 500);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Account does not exists on this email!'
            ], 500);
        }
    }

    public function me()
    {
        if (auth()->user()) {

            $token = request()->bearerToken();

            // Return token and user details
            return response()->json([
                'status' => true,
                'token' => $token,
                'data' => auth()->user()
            ]);
        } else {
            // Return token and user details
            return response()->json([
                'status' => false,
                'data' => 'Unauthenticated!'
            ], 402);
        }
    }


    public function logout()
    {
        try {
            if (auth()->user()) {

                auth()->logout();

                return response()->json(['message' => 'Successfully logged out']);
            } else {
                // Return token and user details
                return response()->json([
                    'status' => false,
                    'data' => 'Unauthenticated!'
                ], 402);
            }
        } catch (\Throwable $th) {
            Log::error("message: " . $th);

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }


    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->setTTL(config('jwt.ttl'))->getTTL() * 60
        ];
    }

    public function register(Request $request)
    {
        // return $request->all();

        try {


            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|string|min:6',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 500);
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->user_role = 'customer';

            $user->save();

            $token = auth()->login($user);



            // Return token and user details
            return response()->json([
                'status' => true,
                'token' => $this->respondWithToken($token),
                'user' => $user
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
