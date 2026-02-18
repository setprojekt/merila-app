<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClearAllActivityLogs extends Command
{
    protected $signature = 'activitylog:clear
                            {--force : Brez potrditve (za skripte)}';

    protected $description = 'Izbriši vse zapise aktivnosti – začetek od nule';

    public function handle(): int
    {
        $table = config('activitylog.table_name', 'activity_log');

        $count = DB::table($table)->count();

        if ($count === 0) {
            Cache::forget('stats.activities.total');
            $this->info('Tabela aktivnosti je že prazna.');
            return 0;
        }

        if (!$this->option('force') && !$this->confirm("Izbrisati vseh {$count} zapisov aktivnosti? Tega ni mogoče razveljaviti.", false)) {
            $this->info('Prekinjeno.');
            return 0;
        }

        DB::table($table)->truncate();
        Cache::forget('stats.activities.total');
        $this->info("Izbrisanih {$count} zapisov. Aktivnosti so zdaj prazne.");

        return 0;
    }
}
