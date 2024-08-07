<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Stripe\Customer;

if (!function_exists('calculateAvgRating')) {

    function calculateAvgRating($id)
    {
        // Retrieve the user by ID
        $averageRating = 0;
        $product = Product::where('id', $id)->first();

        if ($product) {
            // Get the total sum of ratings
            $totalRating = $product->reviews()->sum('rating');

            // Count the number of reviews
            $reviewCount = $product->reviews()->count();

            // Calculate the average rating
            $averageRating = $reviewCount > 0 ? $totalRating / $reviewCount : 0;


        }
        return $averageRating;
    }
}


if (!function_exists('getStripeCustomerByEmail')) {

    function getStripeCustomerByEmail($email)
    {
        $customers = Customer::all(['email' => $email, 'limit' => 1]);
        if (!empty($customers->data)) {
            return $customers->data[0];
        }
        return null;
    }
}


if (!function_exists('generateOrderId')) {

    function generateOrderId()
    {
        $date = now()->format('ymd');
        $lastOrder = Order::whereDate('created_at', now()->format('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();

        $nextNumber = $lastOrder ? intval(substr($lastOrder->order_id, -1)) + 1 : 1;

        return 'ORDER_' . $date . '_' . $nextNumber;
    }
}

if (!function_exists('lulu_auth')) {

    function lulu_auth()
    {
        $authResponse = Http::asForm()->withHeaders([
            'Authorization' => env('LULU_TOKEN'),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->post('https://api.lulu.com/auth/realms/glasstree/protocol/openid-connect/token', [
            'grant_type' => 'client_credentials'
        ]);

        if ($authResponse->failed()) {
            return response()->json(['message' => 'Authentication failed', 'error' => $authResponse->body()], $authResponse->status());
        }

        $token = $authResponse->json()['access_token'];

        return $token;
    }
}


if (!function_exists('lulu_print_jobs')) {

    function lulu_print_jobs($userID, $productID, $qty)
    {

        $token = lulu_auth();

        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Lulu authentication error!'
            ], 401);
        }

        $user = User::where('id', $userID)->first();

        if ($user) {

            $products = Product::whereIn('id', $productID)
                ->where('lulu_book_id', '!=', NULL)
                ->get();

            if (count($products) == 0) {
                return false;
            }

            $array = [];

            foreach ($products as $key => $product) {
                array_push($array, [
                    "external_id" => 'product' . $product->id . "-" . $key + 1,
                    "printable_normalization" => [
                        "cover" => [
                            "source_url" => $product->cover_url
                        ],
                        "interior" => [
                            "source_url" => $product->interior_url
                        ],
                        "pod_package_id" => $product->lulu_book_id
                    ],
                    "quantity" => $qty[$key],
                    "title" => $product->title
                ]);
            }

            // Prepare the data
            $data = [
                "contact_email" => $user->email,
                "external_id" => "1",
                "line_items" => $array,
                "production_delay" => 120,
                "shipping_address" => [
                    "city" => "Lübeck",
                    "country_code" => "GB",
                    "name" => $user->name,
                    "phone_number" => "844-212-0689",
                    "postcode" => "PO1 3AX",
                    "state_code" => "",
                    "street1" => "Holstenstr. 48"
                ],
                "shipping_level" => "MAIL"
            ];

            // Make the HTTP request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->post('https://api.lulu.com/print-jobs', $data);

            // Handle the response
            // if ($response->successful()) {
            //     return response()->json(['message' => 'Request successful', 'data' => $response->json()]);
            // } else {
            //     return response()->json(['message' => 'Request failed', 'error' => $response->body()], $response->status());
            // }
        } else {
            return false;
        }
    }
}
