<?php

namespace App\Filament\Widgets;

use App\Models\Instrument;
use App\Models\DeliveryNoteItem;
use App\Filament\Resources\InstrumentResource;
use App\Filament\Pages\InstrumentsInControl;
use App\Filament\Pages\ArchivedInstruments;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class InstrumentsStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Cache rezultate za 5 minut za hitrejši prikaz
        $cacheKey = 'instruments_stats_' . Carbon::today()->format('Y-m-d');
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $today = Carbon::today();
            $thirtyDaysFromNow = $today->copy()->addDays(30);
            
            // Optimizacija: uporabi eno poizvedbo z agregacijo namesto 3 ločenih
            // Upoštevamo NULL vrednosti za next_check_date
            // POMEMBNO: Izločimo arhivirana merila in merila v kontroli
            $stats = Instrument::query()
                ->where('archived', false)
                ->where('status', '!=', 'V_KONTROLI')
                ->where('status', '!=', 'IZLOCENO')
                ->whereNotNull('next_check_date')
                ->selectRaw('
                    SUM(CASE WHEN next_check_date > ? THEN 1 ELSE 0 END) as valid_count,
                    SUM(CASE WHEN next_check_date >= ? AND next_check_date <= ? THEN 1 ELSE 0 END) as warning_count,
                    SUM(CASE WHEN next_check_date < ? THEN 1 ELSE 0 END) as expired_count
                ', [
                    $thirtyDaysFromNow->format('Y-m-d'),
                    $today->format('Y-m-d'),
                    $thirtyDaysFromNow->format('Y-m-d'),
                    $today->format('Y-m-d'),
                ])
                ->first();
            
            $validCount = (int) ($stats->valid_count ?? 0);
            $warningCount = (int) ($stats->warning_count ?? 0);
            $expiredCount = (int) ($stats->expired_count ?? 0);
            
            // Število arhiviranih meril
            $archivedCount = Instrument::query()
                ->where('archived', true)
                ->count();
            
            // Število meril v kontroli
            $inControlCount = DeliveryNoteItem::query()
                ->whereHas('instrument', function ($query) {
                    $query->where('status', 'V_KONTROLI');
                })
                ->whereHas('deliveryNote', function ($query) {
                    $query->where('archived', false);
                })
                ->count();
            
            return [
                Stat::make('Veljavna merila', $validCount)
                    ->description('Več kot 30 dni do poteka')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success')
                    ->url(InstrumentResource::getUrl('index') . '?tableFilters[show_valid][value]=1')
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                    ]),
                
                Stat::make('Opozorilo', $warningCount)
                    ->description('Kmalu poteče (≤30 dni)')
                    ->descriptionIcon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->url(InstrumentResource::getUrl('index') . '?tableFilters[warning][value]=1')
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                    ]),
                
                Stat::make('Pretečena merila', $expiredCount)
                    ->description('Zahtevajo takojšnjo pozornost')
                    ->descriptionIcon('heroicon-o-x-circle')
                    ->color('danger')
                    ->url(InstrumentResource::getUrl('index') . '?tableFilters[expired][value]=1')
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                    ]),
                
                Stat::make('Merila v kontroli', $inControlCount)
                    ->description('Čakajo na pregled')
                    ->descriptionIcon('heroicon-o-clipboard-document-check')
                    ->color('info')
                    ->url(InstrumentsInControl::getUrl())
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                    ]),
                
                Stat::make('Arhivirana merila', $archivedCount)
                    ->description('Izločena iz uporabe')
                    ->descriptionIcon('heroicon-o-archive-box')
                    ->color('gray')
                    ->url(ArchivedInstruments::getUrl())
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                    ]),
            ];
        });
    }
    
    // Cache invalidation - osveži cache ob spremembi meril
    public static function clearCache(): void
    {
        Cache::forget('instruments_stats_' . Carbon::today()->format('Y-m-d'));
    }
}
