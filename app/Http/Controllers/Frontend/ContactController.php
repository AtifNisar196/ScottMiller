<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function getAll()
    {

        if (auth()->user()) {

            if (auth()->user()->user_role == "customer") {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized, Please login as admin!!'
                ], 402);
            }

            $contacts = Contact::all();

            return response()->json([
                'status' => true,
                'data' => (count($contacts) > 0) ? $contacts : []
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated!!'
            ], 402);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 500);
        }

        try {

            $contact = new Contact();
            $contact->name = $request->name;
            $contact->email = $request->email;
            $contact->phone = $request->phone;
            $contact->message = $request->message;
            $contact->save();

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'message' => $request->message
            ];

            Mail::to($request->email)->send(new ContactMail($data));

            return response()->json([
                'status' => true,
                'message' => 'We have received your request!'
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}