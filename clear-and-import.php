<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Instrument;
use Illuminate\Support\Facades\DB;

// Izbriši vsa merila
echo "Brisanje vseh meril...\n";
$deleted = Instrument::query()->delete();
echo "Izbrisanih: {$deleted}\n\n";

// Preveri, ali so res izbrisana
$count = Instrument::count();
echo "Preostalih meril: {$count}\n\n";

if ($count > 0) {
    echo "Napaka: Merila niso bila izbrisana!\n";
    exit(1);
}

echo "Baza je prazna. Sedaj lahko uvoziš podatke.\n";
