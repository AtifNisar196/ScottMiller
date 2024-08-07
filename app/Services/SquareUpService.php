<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class SquareUpService
{
    protected $bearerToken;
    protected $appId;
    protected $environment;
    protected $client;

    public function __construct()
    {
        $this->appId = env('SQUARE_UP_APP_ID');
        $this->bearerToken = env('SQUARE_UP_BEARER');
        $this->environment = env('SQUARE_ENVIRONMENT');
    }

    public function register_customer($user_id)
    {

        $user = User::where('id', $user_id)->first();
        if ($user) {
            $response = Http::withHeaders([
                'Square-Version' => '2024-06-04',
                'Authorization' => $this->bearerToken,
                'Content-Type' => 'application/json',
            ])->post('https://connect.squareup.com/v2/customers', [
                'given_name' => 'Amelia',
                'family_name' => 'Earhart',
                'email_address' => 'Amelia.Earhart@example.com',
                'address' => [
                    'address_line_1' => '500 Electric Ave',
                    'address_line_2' => 'Suite 600',
                    'locality' => 'New York',
                    'administrative_district_level_1' => 'NY',
                    'postal_code' => '10003',
                    'country' => 'US',
                ],
                'phone_number' => '+1-212-555-4240',
                'reference_id' => 'YOUR_REFERENCE_ID',
                'note' => 'a customer',
            ]);

            if ($response->successful()) {
                // Handle the successful response
                $data = $response->json();
                // Do something with the $data
                return $data;
            } else {
                // Handle the error response
                $error = $response->body();
                return $error;
            }

            $data = json_decode($response, TRUE);
            return $data;
            if (isset($data['customer']['id'])) {
                $customer_update = User::find($user_id);
                $customer_update->square_cust_id = $data['customer']['id'];
                $customer_update->save();
            }
        } else {
            return false;
        }
    }

    public function charge($amount, $card_nonce, $userEmail)
    {
        // Generate a random idempotency key
        $length = 35;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }


        // Convert amount to cents and ensure it's an integer
        $amountInCents = round($amount * 100);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://connect.squareup.com/v2/payments',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                "idempotency_key" => $randomString,
                "amount_money" => [
                    "amount" => $amountInCents, // Amount in cents
                    "currency" => "USD"
                ],
                "source_id" => $card_nonce,
                "buyer_email_address" => $userEmail
            ]),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->bearerToken,
                'Square-Version: ' . env('SQUARE_UP_VERSION'),
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $result = json_decode($response, true); // Return the response if needed
        if ($statusCode == 200) {
            return $result['payment']['id'];
        } else {
            // $error = isset($result['errors']) ? $result['errors'] : 'Unknown error';
            Log::error('Payment Error: ' . $response);
            return false;
        }
    }
}
