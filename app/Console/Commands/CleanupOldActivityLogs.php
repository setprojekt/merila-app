<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class CleanupOldActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activitylog:cleanup {--months=6 : Number of months to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old activity log records older than specified months';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $months = $this->option('months');
        $cutoffDate = Carbon::now()->subMonths($months);
        
        $this->info("Čiščenje Activity Log zapisov starejših od {$months} mesecev...");
        $this->info("Datum odrezka: {$cutoffDate->format('Y-m-d H:i:s')}");
        
        // Preštej zapise, ki bodo izbrisani
        $count = Activity::where('created_at', '<', $cutoffDate)->count();
        
        if ($count === 0) {
            $this->info('Ni zapisov za izbris.');
            return 0;
        }
        
        $this->warn("Najdenih {$count} zapisov za izbris.");
        
        if (!$this->confirm('Ali želite nadaljevati?', true)) {
            $this->info('Prekinjeno.');
            return 0;
        }
        
        // Izbriši stare zapise
        $deleted = Activity::where('created_at', '<', $cutoffDate)->delete();
        
        $this->info("✅ Izbrisanih {$deleted} zapisov.");
        
        // Prikaži statistiko
        $remaining = Activity::count();
        $this->info("Preostalih zapisov: {$remaining}");
        
        return 0;
    }
}
