<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPayment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

class PaymentController extends Controller
{
    public function createPaymentSession(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Course Subscription'],
                    'unit_amount' => $request->amount * 100, 
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('/api/payment/success'),
            'cancel_url' => url('/api/payment/cancel'),
        ]);

        return response()->json(['id' => $session->id]);
    }

    public function handleWebhook(Request $request)
    {
        $event = $request->all();

        if ($event['type'] == 'checkout.session.completed') {
            $session = $event['data']['object'];      
            ProcessPayment::dispatch($session);
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
