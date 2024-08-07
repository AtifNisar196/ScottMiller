<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Models\UserOtp;
use App\Traits\UploadImageTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    use UploadImageTrait;

    public function update(Request $request)
    {

        try {
            if (auth()->user()) {
                $user = User::where('id', auth()->user()->id)->first();



                if ($user) {

                    $user->profile_img = isset($request->profile_img) ? $this->upload_image('/uploads/user/profile/', $request->profile_img) : $user->profile_img;
                    $user->name = isset($request->name) ? $request->name : $user->name;
                    $user->save();

                    return response()->json(['status' => true, 'message' => 'Profile Updated Successfully!']);
                } else {
                    return response()->json(['status' => false, 'message' => 'Authentication Error!'], 400);
                }
            } else {
                // Return token and user details
                return response()->json([
                    'status' => false,
                    'data' => 'Unauthenticated!'
                ]);
            }
        } catch (\Throwable $th) {
            Log::error("message: " . $th);

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }


        try {
            if (auth()->user()) {

                $user = User::where('id', auth()->user()->id)->first();

                if ($user) {

                    if (Hash::check($request->old_password, $user->password)) {

                        $user->password = Hash::make($request->new_password);
                        $user->save();

                        return response()->json(['status' => true, 'message' => 'Password Updated Successfully!']);
                    } else {
                        return response()->json(['status' => false, 'message' => 'Old password does not matched!'], 500);
                    }
                } else {
                    return response()->json(['status' => false, 'message' => 'Authentication Error!'], 401);
                }
            } else {
                // Return token and user details
                return response()->json([
                    'status' => false,
                    'data' => 'Unauthenticated!'
                ]);
            }
        } catch (\Throwable $th) {
            Log::error("message: " . $th);

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function sendOTP(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {


            $otp = rand(0000, 9999);

            $data = [
                'title' => 'Your Forget Password OTP',
                'body' => 'Please verify this otp to change your password',
                'otp' => $otp
            ];

            $user = User::where('email', $request->email)->first();

            if ($user) {
                $user_otp = new UserOtp();
                $user_otp->user_id = $user->id;
                $user_otp->email = $request->email;
                $user_otp->otp = $otp;
                $user_otp->save();

                Mail::to($request->email)->send(new OtpMail($data));

                return response()->json([
                    'status' => true,
                    'message' => 'New OTP send to your email'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not exists for this email'
                ]);
            }
        } catch (\Throwable $th) {
            //throw $th;
            Log::error("error message:" . $th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function checkOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {

            $password_reset = UserOtp::where('email', $request->email)
                ->where('otp', $request->otp)
                ->latest()
                ->first();

            if (!$password_reset) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid email or otp, please try again!'
                ], 500);
            }

            $password_resets = UserOtp::where('email', $request->email)
                ->get();

            foreach ($password_resets as $key => $otps) {
                $otps->delete();
            }

            return response()->json([
                'status' => true,
                'message' => 'Otp Verified'
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error("error message:" . $th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {

            $user = User::where('email', $request->email)->first();

            if ($user) {

                $user->password = Hash::make($request->password);
                $user->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Password changed successfully!'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User does not exists on the given email!'
                ], 500);
            }


            return response()->json([
                'status' => true,
                'message' => 'Otp Verified'
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error("error message:" . $th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}