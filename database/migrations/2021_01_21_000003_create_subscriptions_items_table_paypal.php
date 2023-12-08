<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsItemsTablePaypal extends Migration
{
   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('subscription_id');
            $table->string('customer_id')->index();
            $table->string('plan_id');
            $table->string('paypal_plan_id');
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['subscription_id', 'plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_items');
    }
}
