<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Refund;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function chargeAmount($customer, $card_id, $total)
    {
        // Retrieve the customer from Stripe using their email
        $stripeCustomer = getStripeCustomerByEmail($customer['email']);

        if (!$stripeCustomer) {
            // Create a new customer in Stripe
            $stripeCustomer = Customer::create($customer);
        }

        // Attach the new card to the customer
        $source = Customer::createSource(
            $stripeCustomer->id,
            ['source' => $card_id]
        );

        // Create the charge
        $charge = Charge::create([
            "amount" => (float)$total * 100,
            "currency" => "usd",
            "customer" => $stripeCustomer->id,
            "source" => $source->id,
            "description" => "Testing Charge For Scott Miller",
        ]);

        return $charge;
    }

    public function refundCharge($chargeId, $amount = null)
    {
        try {
            $refundParams = ['charge' => $chargeId];

            if ($amount) {
                $refundParams['amount'] = $amount * 100;
            }

            $refund = Refund::create($refundParams);

            return $refund;
        } catch (\Exception $e) {
            // Handle the error
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
