<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile(__DIR__.'/gradivo/merila.pdf');
$text = $pdf->getText();

echo "=== PRVIH 3000 ZNAKOV PDF BESEDILA ===\n\n";
echo substr($text, 0, 3000);
echo "\n\n=== KONEC ===\n";
