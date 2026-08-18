<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::statement("SET time_zone = '+05:30'");

        DB::unprepared("
            CREATE EVENT IF NOT EXISTS user_subscription_expiry_check
            ON SCHEDULE EVERY 1 DAY
            STARTS (CURRENT_DATE + INTERVAL 1 DAY)
            DO
                UPDATE UserSubscription
                SET is_delete = 1
                WHERE STR_TO_DATE(end_date, '%Y-%m-%d') < CURRENT_DATE
                  AND is_delete = 0
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("
        DROP EVENT IF EXISTS user_subscription_expiry_check
    ");
    }
};
