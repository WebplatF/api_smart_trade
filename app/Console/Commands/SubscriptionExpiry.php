<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SubscriptionExpiry extends Command
{
    protected $signature = 'subscription:expiry';
    protected $description = 'Check and expire user subscriptions';
    public function handle()
    {
        DB::table('UserSubscription')
            ->whereRaw(
                "STR_TO_DATE(end_date, '%Y-%m-%d') < ?",
                [Carbon::today()->format('Y-m-d')]
            )
            ->where('status', 'approved')
            ->where('is_delete', 0)
            ->update([
                'is_delete' => 1,
            ]);
        $this->info('Subscription expiry check completed.');
        return 0;
    }
}
