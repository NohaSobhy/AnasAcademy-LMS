<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('stripe_customer_id')->after('stripe_subscription_id');
            $table->decimal('amount', 8, 2)->after('stripe_customer_id'); 
            $table->string('currency', 10)->default('USD')->after('amount');
            $table->enum('status', ['active', 'canceled', 'expired', 'past_due', 'unpaid'])->default('active')->change();
            $table->timestamp('renews_at')->nullable()->after('ends_at'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn(['stripe_customer_id', 'amount', 'currency', 'renews_at']);
            });
        });
    }
};
