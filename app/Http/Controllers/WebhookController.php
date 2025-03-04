<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebhookLog;
use App\Models\Subscription;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
    public function handleStripeWebhook(Request $request)
    {
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET'); 

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid Signature'], 400);
        }

        // Log the event
        WebhookLog::create([
            'event_type' => $event->type,
            'event_payload' => $event->data->object
        ]);

        // Handle specific events
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleSubscriptionCreated($event->data->object);
                break;
            case 'invoice.payment_failed':
                $this->handleSubscriptionFailed($event->data->object);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionCanceled($event->data->object);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function handleSubscriptionCreated($session)
    {
        $user = User::where('stripe_id', $session->customer)->first();

        if ($user) {
            Subscription::create([
                'user_id' => $user->id,
                'course_id' => $session->metadata->course_id,
                'stripe_subscription_id' => $session->subscription,
                'status' => 'active',
                'started_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);
        }
    }

    private function handleSubscriptionFailed($invoice)
    {
        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

        if ($subscription) {
            $subscription->update(['status' => 'expired']);
        }
    }

    private function handleSubscriptionCanceled($subscription)
    {
        $sub = Subscription::where('stripe_subscription_id', $subscription->id)->first();

        if ($sub) {
            $sub->update(['status' => 'canceled']);
        }
    }
}
