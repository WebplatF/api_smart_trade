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
        Schema::create('SubscriptionMaster', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name', 50)->unique();
            $table->string('amount', 10);
            $table->string('validity', 10);
            $table->string('duration', 3);
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
        Schema::dropIfExists('SubscriptionMaster');
    }
};
