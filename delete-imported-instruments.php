<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Instrument;
use Carbon\Carbon;

// Izbriši vsa merila, ki so bila ustvarjena danes
$today = Carbon::today();
$deleted = Instrument::whereDate('created_at', '>=', $today)->delete();

echo "Izbrisanih meril (ustvarjenih danes): {$deleted}\n";

// Preveri število preostalih meril
$remaining = Instrument::count();
echo "Preostalih meril v bazi: {$remaining}\n";
