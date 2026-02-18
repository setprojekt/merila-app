<?php

namespace App\Console\Commands;

use App\Models\Instrument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;
use Carbon\Carbon;

class ImportInstrumentsFromPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instruments:import-from-pdf {file : Pot do PDF datoteke}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uvozi merila iz PDF datoteke';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("Datoteka ne obstaja: {$filePath}");
            return 1;
        }

        $this->info("Branje PDF datoteke: {$filePath}");

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            $this->info("PDF prebran. Izluščanje podatkov...");
            
            // Izlušči podatke iz besedila
            $instruments = $this->extractInstruments($text);

            if (empty($instruments)) {
                $this->warn("Ni bilo najdenih meril v PDF datoteki.");
                $this->info("Prvih 500 znakov besedila:");
                $this->line(substr($text, 0, 500));
                return 1;
            }

            $this->info("Najdenih meril: " . count($instruments));

            // Prikaži predogled
            if ($this->confirm('Ali želite videti predogled podatkov pred uvozom?', true)) {
                $this->table(
                    ['Notranja št.', 'Ime', 'Vrsta', 'Lokacija', 'Frekvenca', 'Datum', 'Status'],
                    array_map(function($inst) {
                        return [
                            $inst['internal_id'] ?? 'N/A',
                            substr($inst['name'] ?? 'N/A', 0, 30),
                            substr($inst['type'] ?? 'N/A', 0, 20),
                            substr($inst['location'] ?? 'N/A', 0, 15),
                            $inst['frequency_years'] ?? 'N/A',
                            $inst['last_check_date'] ?? 'N/A',
                            $inst['status'] ?? 'N/A',
                        ];
                    }, array_slice($instruments, 0, 10))
                );
            }

            if (!$this->confirm('Ali želite nadaljevati z uvozom?', true)) {
                $this->info('Uvoz preklican.');
                return 0;
            }

            // Uvozi podatke
            $imported = 0;
            $skipped = 0;

            DB::beginTransaction();

            foreach ($instruments as $data) {
                // Preveri obvezna polja
                if (empty($data['internal_id']) || empty($data['name'])) {
                    $skipped++;
                    continue;
                }

                // Preveri, ali merilo že obstaja
                if (Instrument::where('internal_id', $data['internal_id'])->exists()) {
                    $skipped++;
                    $this->warn("Merilo {$data['internal_id']} že obstaja - preskočeno");
                    continue;
                }

                // Nastavi privzete vrednosti
                $data['status'] = $data['status'] ?? 'USTREZA';
                $data['archived'] = false;
                $data['frequency_years'] = $data['frequency_years'] ?? 2.0;

                // Pretvori datum
                if (!empty($data['last_check_date'])) {
                    try {
                        $data['last_check_date'] = Carbon::parse($data['last_check_date'])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $data['last_check_date'] = null;
                    }
                }

                // Ustvari merilo
                Instrument::create($data);
                $imported++;
            }

            DB::commit();

            $this->info("Uvoz končan!");
            $this->info("Uvoženo: {$imported} meril");
            if ($skipped > 0) {
                $this->warn("Preskočeno: {$skipped} meril");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Napaka pri uvozu: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Izlušči podatke o merilih iz besedila PDF-ja
     */
    private function extractInstruments(string $text): array
    {
        $instruments = [];
        
        // Razdeli besedilo na vrstice
        $lines = explode("\n", $text);
        
        $skipHeader = true;
        
        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }

            // Preskoči glavo tabele
            if ($skipHeader && (stripos($line, 'št. merila') !== false || stripos($line, 'vrsta merila') !== false)) {
                $skipHeader = false;
                continue;
            }

            // Poskusi najti vrstico z merilom
            // Format: številka Vrsta merila Datum Velja do Status Uporabnik Frekvenca
            // Primer: "1 Mikrometer not.20-25 mm 27.05.2025 27.11.2026 USTREZA Planina - kon. obročev 1,5"
            
            // Začni z številko na začetku vrstice
            if (preg_match('/^(\d+)\s+(.+?)\s+(\d{1,2}[.\/-]\d{1,2}[.\/-]\d{2,4}|\w+\.\d{2,4})\s+(\d{1,2}[.\/-]\d{1,2}[.\/-]\d{2,4}|\w+\.\d{2,4}|)\s+(USTREZA|NE\s+USTREZA|IZLOČENO|IZLOKČENO)\s+(.+?)\s+(\d+[,.]?\d*)/i', $line, $matches)) {
                $instrument = [
                    'internal_id' => $matches[1],
                    'name' => trim($matches[2]),
                    'type' => trim($matches[2]), // Vrsta merila je tudi ime
                    'last_check_date' => $this->parseDate($matches[3]),
                    'status' => strtoupper(str_replace(' ', '_', trim($matches[5]))),
                    'location' => trim($matches[6]),
                    'frequency_years' => (float) str_replace(',', '.', $matches[7]),
                ];
                
                // Popravi status
                if ($instrument['status'] === 'NE_USTREZA') {
                    $instrument['status'] = 'NE_USTREZA';
                } elseif ($instrument['status'] === 'IZLOČENO' || $instrument['status'] === 'IZLOKČENO') {
                    $instrument['status'] = 'IZLOCENO';
                } else {
                    $instrument['status'] = 'USTREZA';
                }
                
                $instruments[] = $instrument;
                continue;
            }
            
            // Alternativni format - brez "Velja do" datuma
            if (preg_match('/^(\d+)\s+(.+?)\s+(\d{1,2}[.\/-]\d{1,2}[.\/-]\d{2,4}|\w+\.\d{2,4})\s+(USTREZA|NE\s+USTREZA|IZLOČENO|IZLOKČENO)\s+(.+?)\s+(\d+[,.]?\d*)/i', $line, $matches)) {
                $instrument = [
                    'internal_id' => $matches[1],
                    'name' => trim($matches[2]),
                    'type' => trim($matches[2]),
                    'last_check_date' => $this->parseDate($matches[3]),
                    'status' => strtoupper(str_replace(' ', '_', trim($matches[4]))),
                    'location' => trim($matches[5]),
                    'frequency_years' => (float) str_replace(',', '.', $matches[6]),
                ];
                
                // Popravi status
                if ($instrument['status'] === 'NE_USTREZA') {
                    $instrument['status'] = 'NE_USTREZA';
                } elseif ($instrument['status'] === 'IZLOČENO' || $instrument['status'] === 'IZLOKČENO') {
                    $instrument['status'] = 'IZLOCENO';
                } else {
                    $instrument['status'] = 'USTREZA';
                }
                
                $instruments[] = $instrument;
                continue;
            }
            
            // Format z TP številko (npr. "TP 1647/01")
            if (preg_match('/^TP\s+(\d+\/\d+)\s+(.+?)\s+(\d{1,2}[.\/-]\d{1,2}[.\/-]\d{2,4}|\w+\.\d{2,4})\s+(\d{1,2}[.\/-]\d{1,2}[.\/-]\d{2,4}|\w+\.\d{2,4}|)\s+(USTREZA|NE\s+USTREZA|IZLOČENO|IZLOKČENO)\s+(.+?)\s+(\d+[,.]?\d*)/i', $line, $matches)) {
                $instrument = [
                    'internal_id' => 'TP ' . $matches[1],
                    'name' => trim($matches[2]),
                    'type' => trim($matches[2]),
                    'last_check_date' => $this->parseDate($matches[3]),
                    'status' => strtoupper(str_replace(' ', '_', trim($matches[5]))),
                    'location' => trim($matches[6]),
                    'frequency_years' => (float) str_replace(',', '.', $matches[7]),
                ];
                
                // Popravi status
                if ($instrument['status'] === 'NE_USTREZA') {
                    $instrument['status'] = 'NE_USTREZA';
                } elseif ($instrument['status'] === 'IZLOČENO' || $instrument['status'] === 'IZLOKČENO') {
                    $instrument['status'] = 'IZLOCENO';
                } else {
                    $instrument['status'] = 'USTREZA';
                }
                
                $instruments[] = $instrument;
            }
        }
        
        return $instruments;
    }

    /**
     * Parsira datum iz različnih formatov
     */
    private function parseDate(?string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        // Če je format "jan.23", "jun.23" itd., ga preskočimo
        if (preg_match('/^\w+\.\d{2,4}$/', $dateString)) {
            return null;
        }

        try {
            // Poskusi različne formate
            $formats = ['d.m.Y', 'd/m/Y', 'Y-m-d', 'd.m.y', 'd/m/y'];
            
            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $dateString);
                    return $date->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // Poskusi z Carbon::parse kot zadnjo možnost
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
