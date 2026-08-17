<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('InvoiceDetails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('user_sub_id');
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
            $table->foreign('invoice_id')->references('id')->on('InvoiceMaster')->cascadeOnDelete();
            $table->foreign('user_sub_id')->references('id')->on('UserSubscription')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('InvoiceDetails');
    }
};
