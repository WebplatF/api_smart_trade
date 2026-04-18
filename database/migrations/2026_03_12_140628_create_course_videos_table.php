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
        Schema::create('CourseVideos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('detail_id');
            $table->integer('video_id');
            $table->integer('thumbnail_id');
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
            $table->foreign('detail_id', 'fk_cd')
                ->references('id')
                ->on('CourseDetails')
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
        Schema::dropIfExists('CourseVideos');
    }
};
