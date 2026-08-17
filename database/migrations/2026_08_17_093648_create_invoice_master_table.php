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
        Schema::create('InvoiceMaster', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no');
            $table->string('order_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('discount')->nullable();
            $table->string('discount_type')->nullable();
            $table->string('sub_total')->nullable();
            $table->string('tax')->nullable()->default('18');
            $table->string('grand_total')->nullable();
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('UserMaster')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('InvoiceMaster');
    }
};
