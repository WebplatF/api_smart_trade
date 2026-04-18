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
        Schema::create('VideoUpload', function (Blueprint $table) {
            $table->id();
            $table->string('video_id')->unique();
            $table->string('media_url');
            $table->integer('thumbnail_id');
            $table->bigInteger('durations');
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
        Schema::dropIfExists('VideoUpload');
    }
};
