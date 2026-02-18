<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Instrument;

// Preveri, koliko meril je v bazi
$count = Instrument::count();
echo "Število meril v bazi: {$count}\n";

// Vprašaj za potrditev
echo "\nAli želite izbrisati VSA merila? (da/ne): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$answer = trim(strtolower($line));
fclose($handle);

if ($answer === 'da' || $answer === 'd' || $answer === 'yes' || $answer === 'y') {
    // Izbriši vsa merila
    $deleted = Instrument::query()->delete();
    echo "Izbrisanih meril: {$deleted}\n";
    echo "Baza je ponastavljena.\n";
} else {
    echo "Operacija preklicana.\n";
}
