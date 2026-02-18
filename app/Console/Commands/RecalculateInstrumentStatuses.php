<?php

namespace App\Console\Commands;

use App\Models\Instrument;
use Illuminate\Console\Command;

class RecalculateInstrumentStatuses extends Command
{
    protected $signature = 'instruments:recalculate-statuses';

    protected $description = 'Preračuna statuse vseh meril glede na datum naslednjega pregleda';

    public function handle()
    {
        $this->info('Preračunavanje statusov meril...');
        
        // Najdi vsa merila, ki NISO v kontroli in NISO arhivirana
        $instruments = Instrument::where('status', '!=', 'V_KONTROLI')
            ->where('status', '!=', 'IZLOCENO')
            ->where('archived', false)
            ->get();
        
        $this->info("Najdenih {$instruments->count()} meril za preračun.");
        
        $updated = 0;
        
        foreach ($instruments as $instrument) {
            $oldStatus = $instrument->status;
            $newStatus = null;
            
            // Če ni datum ali je pretečen
            if (!$instrument->next_check_date) {
                $newStatus = 'NE_USTREZA';
                $reason = 'Ni datum';
            } elseif ($instrument->next_check_date->isPast()) {
                $newStatus = 'NE_USTREZA';
                $reason = "Potekel {$instrument->next_check_date->format('d.m.Y')}";
            } else {
                $newStatus = 'USTREZA';
                $reason = "Velja do {$instrument->next_check_date->format('d.m.Y')}";
            }
            
            // Posodobi samo, če se je status spremenil
            if ($oldStatus !== $newStatus) {
                $instrument->update(['status' => $newStatus]);
                $this->warn("#{$instrument->internal_id} ({$instrument->name})");
                $this->info("  {$oldStatus} → {$newStatus} ({$reason})");
                $updated++;
            }
        }
        
        $this->newLine();
        
        if ($updated === 0) {
            $this->info('✅ Vsi statusi so pravilni.');
        } else {
            $this->info("✅ Posodobljenih {$updated} meril.");
        }
        
        return 0;
    }
}
