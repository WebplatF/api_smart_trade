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
       Schema::create('TransactionMaster', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('tag');
            $table->string('receipt', 50);
            $table->string('amount');
            $table->string('order_id')->index();
            $table->enum('status',['None','Order Created','Payment Pending','Payment Completed'])->default('None');
            $table->string('payment_order_id')->nullable();
            $table->string('payment_id')->nullable()->index();
            $table->string('signature')->nullable();
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('TransactionMaster');
    }
};
