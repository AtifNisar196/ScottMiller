<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\SubscriberMail;
use App\Models\EmailSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailSubscriberController extends Controller
{

    public function getAll()
    {
        if (auth()->user()) {

            $subscibers = EmailSubscriber::all();
            return response()->json([
                'status' => true,
                'message' => $subscibers
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized!'
            ], 500);
        }
    }

    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'nullable',
            'email' => 'required|unique:email_subscribers,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {

            $subsciber = new EmailSubscriber();
            $subsciber->name = $request->name;
            $subsciber->email = $request->email;
            $subsciber->save();

            $data = [
                'name' => $request->name,
                'email' => $request->email
            ];

            Mail::to($request->email)->send(new SubscriberMail($data));


            return response()->json([
                'status' => true,
                'message' => 'You have subscribed to our email list successfully!'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }
}
