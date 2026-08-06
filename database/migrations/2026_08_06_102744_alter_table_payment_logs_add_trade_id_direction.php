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
        Schema::table('PaymentLogs', function (Blueprint $table) {
            $table->enum('direction', ['Inward', 'Outward'])->after('action');
            $table->bigInteger('trade_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('PaymentLogs', function (Blueprint $table) {
            //
        });
    }
};
