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
        Schema::create('SubscriptionDetails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedBigInteger('course_id');
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
            $table->foreign('subscription_id', 'fk_sm')
                ->references('id')
                ->on('SubscriptionMaster')
                ->onDelete('cascade');
            $table->foreign('course_id', 'fk_sd')
                ->references('id')
                ->on('CourseMaster')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('SubscriptionDetails');
    }
};
