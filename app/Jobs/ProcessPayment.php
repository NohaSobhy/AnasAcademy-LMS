<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $session;
    /**
     * Create a new job instance.
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $userId = $this->session['metadata']['user_id'] ?? null;
            $amount = $this->session['amount_total'] / 100;
            $currency = $this->session['currency'];
            $paymentId = $this->session['id'];

            Payment::create([
                'user_id' => $userId,
                'stripe_payment_id' => $paymentId,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'succeeded',
            ]);

            Log::info("Payment processed for user ID: $userId, Payment ID: $paymentId");
        } catch (\Exception $e) {
            Log::error("Payment processing failed: " . $e->getMessage());
        }
    }
}
