<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WatchHistoryPartition extends Command
{
    protected $signature = 'partition:watchhistory';
    protected $description = 'Create or extend WatchHistory partitions';

    public function handle()
    {
        $table = "WatchHistory";

        // Ensure primary key includes partition column
        DB::statement("
            ALTER TABLE {$table}
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (id, created_at)
        ");

        // Check existing partitions
        $partitions = DB::select("
            SELECT PARTITION_NAME
            FROM INFORMATION_SCHEMA.PARTITIONS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = '{$table}'
            AND PARTITION_NAME IS NOT NULL
        ");

        if (count($partitions) == 0) {

            $this->info("No partitions found. Creating first 5 months.");

            $start = Carbon::now()->startOfMonth();
            $partitionSQL = [];

            for ($i = 0; $i < 5; $i++) {

                $month = $start->copy()->addMonths($i);
                $nextMonth = $month->copy()->addMonth();

                $partitionName = 'p' . $month->format('Y_m');
                $partitionDate = $nextMonth->format('Y-m-01');

                $partitionSQL[] =
                    "PARTITION {$partitionName} VALUES LESS THAN ('{$partitionDate}')";
            }

            $partitionSQL[] = "PARTITION pmax VALUES LESS THAN (MAXVALUE)";

            DB::statement("
                ALTER TABLE {$table}
                PARTITION BY RANGE COLUMNS(created_at) (
                    " . implode(",", $partitionSQL) . "
                )
            ");

        } else {

            $this->info("Partitions already exist. Extending next 5 months.");

            $lastPartition = collect($partitions)
                ->pluck('PARTITION_NAME')
                ->filter(fn($p) => $p !== 'pmax')
                ->sort()
                ->last();

            $lastDate = Carbon::createFromFormat('Y_m', substr($lastPartition, 1));

            $addSQL = [];

            for ($i = 1; $i <= 5; $i++) {

                $month = $lastDate->copy()->addMonths($i);
                $nextMonth = $month->copy()->addMonth();

                $partitionName = 'p' . $month->format('Y_m');
                $partitionDate = $nextMonth->format('Y-m-01');

                $addSQL[] =
                    "PARTITION {$partitionName} VALUES LESS THAN ('{$partitionDate}')";
            }

            DB::statement("
                ALTER TABLE {$table}
                REORGANIZE PARTITION pmax INTO (
                    " . implode(",", $addSQL) . ",
                    PARTITION pmax VALUES LESS THAN (MAXVALUE)
                )
            ");
        }

        $this->info("Partition operation completed.");
    }
}