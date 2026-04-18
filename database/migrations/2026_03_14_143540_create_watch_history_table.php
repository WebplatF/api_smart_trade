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
        Schema::create('WatchHistory', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->index();
            $table->bigInteger('video_id')->index();
            $table->bigInteger('subscription_id')->index();
            $table->bigInteger('last_time_stamp')->default(0);
            $table->boolean('is_watch')->default(false);
            $table->boolean('is_finshed')->default(false);
            $table->boolean('is_delete')->default(false);
            $table->dateTime('created_at')->index();
            $table->dateTime('updated_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('WatchHistory');
    }
};
