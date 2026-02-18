<?php

namespace App\Filament\Resources\InstrumentResource\Pages;

use App\Filament\Resources\InstrumentResource;
use App\Models\Instrument;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ListInstruments extends ListRecords
{
    protected static string $resource = InstrumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo merilo'),
            Action::make('import')
                ->label('Uvozi merila')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('CSV datoteka')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'text/plain', 'application/csv'])
                        ->required()
                        ->disk('local')
                        ->directory('imports')
                        ->visibility('private')
                        ->helperText('Naložite CSV datoteko z merili. Prva vrstica mora vsebovati glavo stolpcev.'),
                    Select::make('update_existing')
                        ->label('Obstoječa merila')
                        ->options([
                            'skip' => 'Preskoči (ne posodobi)',
                            'update' => 'Posodobi z novimi podatki',
                        ])
                        ->default('skip')
                        ->required()
                        ->helperText('Izberite, kako obravnavati merila, ki že obstajajo v bazi.'),
                ])
                ->action(function (array $data) {
                    $file = $data['file'];
                    $updateExisting = $data['update_existing'] ?? 'skip';
                    
                    // Uporabi Storage facade za dostop do datoteke
                    $disk = Storage::disk('local');
                    
                    // Poskusi najti datoteko na različnih poteh
                    $path = null;
                    
                    // Poskusi direktno preko Storage path
                    try {
                        if ($disk->exists($file)) {
                            $path = $disk->path($file);
                        }
                    } catch (\Exception $e) {
                        // Ignoriraj
                    }
                    
                    // Če ni najdena, poskusi različne poti
                    if (!$path || !file_exists($path)) {
                        $possiblePaths = [
                            storage_path('app/private/' . $file),
                            storage_path('app/' . $file),
                            storage_path('app/imports/' . basename($file)),
                        ];
                        
                        foreach ($possiblePaths as $possiblePath) {
                            if (file_exists($possiblePath)) {
                                $path = $possiblePath;
                                break;
                            }
                        }
                    }
                    
                    if (!$path || !file_exists($path)) {
                        Notification::make()
                            ->title('Napaka')
                            ->body('Datoteka ni najdena. Preverite, ali je datoteka pravilno naložena.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    $handle = fopen($path, 'r');
                    if (!$handle) {
                        Notification::make()
                            ->title('Napaka')
                            ->body('Datoteke ni mogoče odpreti.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Določi ločilo - preberi prvo vrstico in preveri
                    $firstLine = fgets($handle);
                    rewind($handle);
                    $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

                    // Preberi glavo z določenim ločilom
                    $header = fgetcsv($handle, 0, $delimiter);
                    
                    if (!$header || count($header) < 2) {
                        fclose($handle);
                        Notification::make()
                            ->title('Napaka')
                            ->body('Datoteka je prazna ali nima glave.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Odstrani BOM (Byte Order Mark) iz prvega stolpca
                    if (!empty($header[0])) {
                        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
                        $header[0] = trim($header[0], "\xEF\xBB\xBF");
                    }

                    // Normaliziraj glavo (odstrani presledke, pretvori v lowercase)
                    $header = array_map(function($col) {
                        return strtolower(trim($col));
                    }, $header);

                    // Preveri, ali so ključni stolpci prisotni (z različnimi variacijami)
                    $hasInternalId = false;
                    $hasName = false;
                    
                    foreach ($header as $col) {
                        if (stripos($col, 'notranja') !== false && (stripos($col, 'stevilka') !== false || stripos($col, 'številka') !== false)) {
                            $hasInternalId = true;
                        }
                        if (stripos($col, 'ime') !== false && stripos($col, 'merila') !== false) {
                            $hasName = true;
                        }
                    }
                    
                    if (!$hasInternalId || !$hasName) {
                        fclose($handle);
                        $missing = [];
                        if (!$hasInternalId) $missing[] = 'notranja številka/stevilka';
                        if (!$hasName) $missing[] = 'ime merila';
                        Notification::make()
                            ->title('Napaka v glavi CSV datoteke')
                            ->body('Manjkajo naslednji stolpci: ' . implode(', ', $missing) . "\nNajdeni stolpci: " . implode(', ', $header))
                            ->danger()
                            ->send();
                        return;
                    }

                    // Mapiranje stolpcev
                    $columnMap = [
                        'notranja številka' => 'internal_id',
                        'notranja stevilka' => 'internal_id',
                        'notranja_stevilka' => 'internal_id',
                        'notranja_številka' => 'internal_id',
                        'internal_id' => 'internal_id',
                        'ime merila' => 'name',
                        'ime_merila' => 'name',
                        'name' => 'name',
                        'vrsta merila' => 'type',
                        'vrsta_merila' => 'type',
                        'type' => 'type',
                        'lokacija' => 'location',
                        'location' => 'location',
                        'frekvenca pregleda (leta)' => 'frequency_years',
                        'frekvenca pregleda' => 'frequency_years',
                        'frekvenca_pregleda' => 'frequency_years',
                        'frequency_years' => 'frequency_years',
                        'datum zadnjega pregleda' => 'last_check_date',
                        'datum_zadnjega_pregleda' => 'last_check_date',
                        'last_check_date' => 'last_check_date',
                        'status' => 'status',
                        ' odgovoren uporabnik ' => 'user_email',
                        'odgovoren uporabnik' => 'user_email',
                        'odgovoren_uporabnik' => 'user_email',
                        'user_email' => 'user_email',
                    ];

                    $imported = 0;
                    $updated = 0;
                    $skipped = 0;
                    $errors = [];
                    
                    // Preberi vse obstoječe internal_id iz baze (pred transakcijo)
                    $existingInstruments = Instrument::pluck('id', 'internal_id')->toArray();
                    $processedIds = []; // Za sledenje ID-jev znotraj iste transakcije

                    DB::beginTransaction();

                    try {
                        $lineNumber = 1;
                        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                            $lineNumber++;
                            
                            // Preskoči prazne vrstice
                            if (empty(array_filter($row))) {
                                continue;
                            }

                            // Ustvari asociativni array
                            $rowData = [];
                            foreach ($header as $index => $colName) {
                                if (isset($row[$index]) && isset($columnMap[$colName])) {
                                    $mappedCol = $columnMap[$colName];
                                    $rowData[$mappedCol] = trim($row[$index]);
                                }
                            }

                            // Preveri obvezna polja
                            $internalId = $rowData['internal_id'] ?? '';
                            $name = $rowData['name'] ?? '';
                            
                            if (empty($internalId) || empty($name)) {
                                $skipped++;
                                $reason = [];
                                if (empty($internalId)) $reason[] = 'manjka notranja številka';
                                if (empty($name)) $reason[] = 'manjka ime merila';
                                $errors[] = "Vrstica {$lineNumber}: " . implode(', ', $reason) . " (Podatki: " . implode(' | ', array_slice($row, 0, 3)) . ")";
                                continue;
                            }

                            // Preveri, ali merilo že obstaja (v bazi ali v tej transakciji)
                            $existingInstrumentId = $existingInstruments[$internalId] ?? null;
                            $alreadyProcessed = in_array($internalId, $processedIds);
                            
                            if ($existingInstrumentId && $updateExisting === 'skip') {
                                $skipped++;
                                $errors[] = "Vrstica {$lineNumber}: Merilo z notranjo številko '{$internalId}' že obstaja - preskočeno";
                                continue;
                            }
                            
                            if ($alreadyProcessed) {
                                $skipped++;
                                $errors[] = "Vrstica {$lineNumber}: Merilo z notranjo številko '{$internalId}' je že bilo obdelano v tej transakciji";
                                continue;
                            }

                            // Pretvori datum - nastavi na NULL, če je prazen
                            if (isset($rowData['last_check_date'])) {
                                $dateStr = trim($rowData['last_check_date']);
                                if (empty($dateStr)) {
                                    $rowData['last_check_date'] = null;
                                } else {
                                    try {
                                        // Poskusi različne formate
                                        if (preg_match('/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{2,4})$/', $dateStr, $matches)) {
                                            $day = $matches[1];
                                            $month = $matches[2];
                                            $year = strlen($matches[3]) == 2 ? '20' . $matches[3] : $matches[3];
                                            $rowData['last_check_date'] = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                                        } elseif (preg_match('/^(\d{1,2})\/(\d{4})$/', $dateStr, $matches)) {
                                            // Format: 11/2024
                                            $rowData['last_check_date'] = Carbon::createFromDate($matches[2], $matches[1], 1)->format('Y-m-d');
                                        } else {
                                            $rowData['last_check_date'] = Carbon::parse($dateStr)->format('Y-m-d');
                                        }
                                    } catch (\Exception $e) {
                                        $rowData['last_check_date'] = null;
                                    }
                                }
                            } else {
                                $rowData['last_check_date'] = null;
                            }

                            // Pretvori frekvenco
                            if (!empty($rowData['frequency_years'])) {
                                $rowData['frequency_years'] = (float) str_replace(',', '.', $rowData['frequency_years']);
                            } else {
                                $rowData['frequency_years'] = 2.0;
                            }

                            // Očisti prazne stringe za nullable polja
                            if (isset($rowData['type']) && trim($rowData['type']) === '') {
                                $rowData['type'] = null;
                            }
                            if (isset($rowData['location']) && trim($rowData['location']) === '') {
                                $rowData['location'] = null;
                            }
                            
                            // Nastavi privzete vrednosti in normaliziraj status
                            $status = $rowData['status'] ?? 'USTREZA';
                            // Normaliziraj status (NE USTREZA -> NE_USTREZA)
                            $status = strtoupper(trim($status));
                            $status = str_replace(' ', '_', $status);
                            // Preveri, ali je status veljaven
                            $validStatuses = ['USTREZA', 'NE_USTREZA', 'IZLOCENO', 'V_KONTROLI'];
                            if (!in_array($status, $validStatuses)) {
                                $status = 'USTREZA'; // Privzeto, če ni veljaven
                            }
                            $rowData['status'] = $status;
                            $rowData['archived'] = false;

                            // Poišči uporabnika po emailu, če je podan
                            if (!empty($rowData['user_email'])) {
                                $user = \App\Models\User::where('email', $rowData['user_email'])->first();
                                if ($user) {
                                    $rowData['user_id'] = $user->id;
                                }
                                unset($rowData['user_email']);
                            }

                            // Ustvari ali posodobi merilo z obravnavanjem napak
                            try {
                                if ($existingInstrumentId && $updateExisting === 'update') {
                                    // Posodobi obstoječe merilo
                                    $instrument = Instrument::find($existingInstrumentId);
                                    $instrument->update($rowData);
                                    $processedIds[] = $internalId;
                                    $updated++;
                                } else {
                                    // Ustvari novo merilo
                                    $instrument = Instrument::create($rowData);
                                    $processedIds[] = $internalId;
                                    $imported++;
                                }
                            } catch (\Illuminate\Database\QueryException $e) {
                                // Ujami duplicate entry napake
                                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                                    $skipped++;
                                    $errors[] = "Vrstica {$lineNumber}: Merilo z notranjo številko '{$internalId}' že obstaja v bazi";
                                } else {
                                    $skipped++;
                                    $errors[] = "Vrstica {$lineNumber}: Napaka pri vstavljanju - " . $e->getMessage();
                                }
                            } catch (\Exception $e) {
                                $skipped++;
                                $errors[] = "Vrstica {$lineNumber}: Napaka - " . $e->getMessage();
                            }
                        }

                        fclose($handle);
                        DB::commit();

                        $message = "Uvoženo: {$imported} novih meril";
                        if ($updated > 0) {
                            $message .= ", posodobljenih: {$updated}";
                        }
                        if ($skipped > 0) {
                            $message .= ", preskočeno: {$skipped}";
                        }

                        Notification::make()
                            ->title('Uvoz končan')
                            ->body($message)
                            ->success()
                            ->send();

                        if (!empty($errors)) {
                            $errorCount = count($errors);
                            $errorMessage = $errorCount <= 20 
                                ? implode("\n", $errors)
                                : implode("\n", array_slice($errors, 0, 20)) . "\n\n... in še " . ($errorCount - 20) . " napak";
                            
                            Notification::make()
                                ->title("Opozorila ({$errorCount} napak)")
                                ->body($errorMessage)
                                ->warning()
                                ->persistent()
                                ->send();
                        }

                    } catch (\Exception $e) {
                        DB::rollBack();
                        if (isset($handle)) {
                            fclose($handle);
                        }
                        
                        Notification::make()
                            ->title('Napaka pri uvozu')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
