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
        Schema::create('PaymentLogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->string('description', 60)->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('action', ['DEPOSIT', 'WITHDRAW', 'TRADE ENTRY']);
            $table->decimal('balance', 10, 2)->default(0.0);
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
            $table->foreign('wallet_id')->references('id')->on('Wallet')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('PaymentLogs');
    }
};
