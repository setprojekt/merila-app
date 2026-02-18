<?php

namespace App\Console\Commands;

use App\Models\Instrument;
use App\Models\DeliveryNoteItem;
use Illuminate\Console\Command;

class FixOrphanedInstrumentsInControl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instruments:fix-orphaned-in-control';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Popravi merila, ki so v statusu V_KONTROLI, ampak niso povezana z nobeno dobavnico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iskanje meril v kontroli brez dobavnice...');
        
        // Najdi vsa merila s statusom V_KONTROLI
        $instrumentsInControl = Instrument::where('status', 'V_KONTROLI')
            ->where('archived', false)
            ->get();
        
        $this->info("Najdenih {$instrumentsInControl->count()} meril v kontroli.");
        
        $fixed = 0;
        
        foreach ($instrumentsInControl as $instrument) {
            // Preveri, ali ima aktivno povezavo z dobavnico
            $hasActiveDeliveryNote = DeliveryNoteItem::query()
                ->where('instrument_id', $instrument->id)
                ->whereHas('deliveryNote', function ($query) {
                    $query->where('archived', false);
                })
                ->exists();
            
            if (!$hasActiveDeliveryNote) {
                // Merilo nima aktivne dobavnice - resetiraj status
                $this->warn("Merilo #{$instrument->internal_id} ({$instrument->name}) nima aktivne dobavnice.");
                
                // Določi pravilen status glede na datum
                $newStatus = 'NE_USTREZA'; // Privzeto
                
                if ($instrument->next_check_date) {
                    if ($instrument->next_check_date->isFuture()) {
                        $newStatus = 'USTREZA';
                    } else {
                        $newStatus = 'NE_USTREZA';
                    }
                } else {
                    // Ni datum vnešen
                    $newStatus = 'NE_USTREZA';
                }
                
                $instrument->update(['status' => $newStatus]);
                
                $dateInfo = $instrument->next_check_date 
                    ? "Datum: {$instrument->next_check_date->format('d.m.Y')}" 
                    : "Ni datum";
                    
                $this->info("  → Status spremenjen na: {$newStatus} ({$dateInfo})");
                $fixed++;
            }
        }
        
        $this->newLine();
        
        if ($fixed === 0) {
            $this->info('✅ Vsa merila v kontroli so pravilno povezana z dobavnicami.');
        } else {
            $this->info("✅ Popravljeno {$fixed} meril.");
        }
        
        return 0;
    }
}
