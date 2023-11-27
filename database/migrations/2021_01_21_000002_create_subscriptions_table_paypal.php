<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTablePaypal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('subscribable_id');
            $table->string('subscribable_type');
            $table->string('name');
            $table->string('customer_id');
            $table->string('status');
            $table->string('agreement_id')->nullable();
            $table->string('plan_id')->nullable();
            $table->string('paypal_plan_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->json('last_payment')->nullable();
            $table->json('next_payment')->nullable();
            $table->timestamp('last_payment_date')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
