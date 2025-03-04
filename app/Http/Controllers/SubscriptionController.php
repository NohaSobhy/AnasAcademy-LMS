<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription as StripeSubscription;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $user = JWTAuth::parseToken()->authenticate();

        $customer = Customer::create([
            'email' => Auth::user()->email,
            'name' => Auth::user()->name,
        ]);

        $subscription = StripeSubscription::create([
            'customer' => $customer->id,
            'items' => [[
                'price' => $request->stripe_price_id,
            ]],
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        Subscription::create([
            'user_id' => Auth::id(),
            'course_id' => $request->course_id,
            'stripe_subscription_id' => $subscription->id,
            'stripe_customer_id' => $customer->id,
            'amount' => $request->amount,
            'currency' => 'usd',
            'status' => 'active',
            'started_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        return response()->json(['message' => 'Subscription created successfully']);
    }

    public function cancelSubscription(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $subscription = Subscription::where('stripe_subscription_id', $request->subscription_id)->first();

        if (!$subscription) {
            return response()->json(['message' => 'Subscription not found'], 404);
        }

        $stripeSubscription = StripeSubscription::retrieve($subscription->stripe_subscription_id);
        $stripeSubscription->cancel();

        $subscription->update(['status' => 'canceled']);

        return response()->json(['message' => 'Subscription canceled successfully']);
    }

    public function checkSubscriptionStatus()
    {
        $subscription = Subscription::where('user_id', Auth::id())->first();

        if (!$subscription) {
            return response()->json(['status' => 'inactive']);
        }

        return response()->json(['status' => $subscription->status]);
    }
}
