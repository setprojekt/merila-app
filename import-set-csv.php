<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Instrument;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$filePath = __DIR__ . '/gradivo/SET 70.001 .csv';

if (!file_exists($filePath)) {
    echo "Datoteka ne obstaja: {$filePath}\n";
    exit(1);
}

echo "Branje CSV datoteke: {$filePath}\n";

$handle = fopen($filePath, 'r');
if (!$handle) {
    echo "Datoteke ni mogoče odpreti.\n";
    exit(1);
}

// Preberi glavo (prva vrstica)
$header = fgetcsv($handle, 0, ';');
if (!$header) {
    echo "Datoteka je prazna ali nima glave.\n";
    fclose($handle);
    exit(1);
}

// Odstrani BOM (Byte Order Mark) iz prvega stolpca
if (!empty($header[0])) {
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    $header[0] = trim($header[0], "\xEF\xBB\xBF");
}

// Normaliziraj glavo
$header = array_map(function($col) {
    return strtolower(trim($col));
}, $header);

echo "Glava: " . implode(', ', $header) . "\n\n";

$imported = 0;
$skipped = 0;
$errors = [];

$lineNumber = 1;
$processedIds = [];

// Ne uporabljaj transakcije - commit po vsakem uspešnem uvozu
while (($row = fgetcsv($handle, 0, ';')) !== false) {
        $lineNumber++;
        
        // Preskoči prazne vrstice
        if (empty(array_filter($row))) {
            continue;
        }
        
        // Debug: prikaži prve 3 vrstice
        if ($lineNumber <= 5) {
            echo "Vrstica {$lineNumber}: " . implode(' | ', array_slice($row, 0, 3)) . "\n";
        }

        // Ustvari asociativni array
        $data = [];
        foreach ($header as $index => $colName) {
            if (isset($row[$index])) {
                $data[trim($colName)] = trim($row[$index]);
            }
        }

        // Preveri obvezna polja (poskusi različne variante imen stolpcev)
        $internalId = '';
        $name = '';
        
        foreach ($data as $key => $value) {
            $normalizedKey = strtolower(trim($key));
            if (strpos($normalizedKey, 'notranja') !== false && strpos($normalizedKey, 'številka') !== false) {
                $internalId = trim($value);
            }
            if (strpos($normalizedKey, 'ime') !== false && strpos($normalizedKey, 'merila') !== false) {
                $name = trim($value);
            }
        }

        if (empty($internalId) || empty($name)) {
            $skipped++;
            $errors[] = "Vrstica {$lineNumber}: Manjka notranja številka ali ime merila (ID: '{$internalId}', Name: '{$name}')";
            continue;
        }

        // Preveri tudi v lokalnem seznamu (za preprečevanje duplikatov v isti transakciji)
        if (in_array($internalId, $processedIds)) {
            $skipped++;
            echo "Merilo {$internalId} že v procesu uvoza (duplikat v CSV) - preskočeno\n";
            continue;
        }
        $processedIds[] = $internalId;
        
        // Preveri, ali merilo že obstaja v bazi
        if (Instrument::where('internal_id', $internalId)->exists()) {
            $skipped++;
            echo "Merilo {$internalId} že obstaja v bazi - preskočeno\n";
            continue;
        }

        // Pripravi podatke za vnos
        $instrumentData = [
            'internal_id' => $internalId,
            'name' => $name,
            'type' => $data['vrsta merila'] ?? null,
            'location' => $data['lokacija'] ?? null,
            'frequency_years' => !empty($data['frekvenca pregleda (leta)']) 
                ? (float) str_replace(',', '.', $data['frekvenca pregleda (leta)']) 
                : 2.0,
            'status' => normalizeStatus($data['status'] ?? 'USTREZA'),
            'archived' => false,
        ];

        // Pretvori datum
        if (!empty($data['datum zadnjega pregleda'])) {
            try {
                $dateStr = $data['datum zadnjega pregleda'];
                // Poskusi različne formate
                if (preg_match('/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{2,4})$/', $dateStr, $matches)) {
                    $day = $matches[1];
                    $month = $matches[2];
                    $year = strlen($matches[3]) == 2 ? '20' . $matches[3] : $matches[3];
                    $instrumentData['last_check_date'] = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                } elseif (preg_match('/^(\d{1,2})\/(\d{4})$/', $dateStr, $matches)) {
                    // Format: 11/2024
                    $instrumentData['last_check_date'] = Carbon::createFromDate($matches[2], $matches[1], 1)->format('Y-m-d');
                } else {
                    $instrumentData['last_check_date'] = Carbon::parse($dateStr)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $instrumentData['last_check_date'] = null;
            }
        }

        // Poišči uporabnika po emailu, če je podan
        if (!empty($data['odgovoren uporabnik'])) {
            $user = \App\Models\User::where('email', trim($data['odgovoren uporabnik']))->first();
            if ($user) {
                $instrumentData['user_id'] = $user->id;
            }
        }

        // Ustvari merilo
        try {
            $instrument = Instrument::create($instrumentData);
            $imported++;
            echo "Uvoženo: {$internalId} - {$name}\n";
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $skipped++;
                echo "Merilo {$internalId} že obstaja - preskočeno\n";
            } else {
                $skipped++;
                echo "Napaka pri uvozu {$internalId}: " . $e->getMessage() . "\n";
                $errors[] = "Vrstica {$lineNumber}: {$e->getMessage()}";
            }
        } catch (\Exception $e) {
            $skipped++;
            echo "Napaka pri uvozu {$internalId}: " . $e->getMessage() . "\n";
            $errors[] = "Vrstica {$lineNumber}: {$e->getMessage()}";
        }
    }

fclose($handle);

echo "\n=== UVOZ KONČAN ===\n";
echo "Uvoženo: {$imported} meril\n";
if ($skipped > 0) {
    echo "Preskočeno: {$skipped} meril\n";
}
if (!empty($errors) && count($errors) <= 20) {
    echo "\nOpozorila:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

function normalizeStatus($status) {
    $status = trim(strtoupper($status));
    
    if (empty($status) || $status === 'USTREZA') {
        return 'USTREZA';
    }
    
    if (strpos($status, 'NE') !== false || $status === 'NE USTREZA') {
        return 'NE_USTREZA';
    }
    
    if (strpos($status, 'IZLO') !== false || $status === 'IZLOČENO' || $status === 'IZLOKČENO') {
        return 'IZLOCENO';
    }
    
    return 'USTREZA';
}
