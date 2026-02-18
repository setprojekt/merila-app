<?php

namespace App\Settings\Modules;

use Spatie\LaravelSettings\Settings;

class CompetencyMatrixSettings extends Settings
{
    public string $module_name;
    public string $module_number;
    public bool $send_email_notifications;
    public string $notification_recipients;
    public string $notification_time;
    public int $notification_day_of_week;
    public int $notification_interval_days;
    public int $notification_days_before_expiry;

    /** Frekvenca pregleda (leta) po vrstah usposobljenosti: [competency_item_id => leta] */
    public array $item_frequency_years = [];

    public static function group(): string
    {
        return 'competency_matrix';
    }

    /** Vrne frekvenco (leta) za dani element; če ni nastavljena, uporabi privzeto iz modela */
    public function getFrequencyYearsForItem(int $itemId, ?int $defaultFromItem = null): ?int
    {
        $val = $this->item_frequency_years[$itemId] ?? $this->item_frequency_years[(string) $itemId] ?? null;
        return $val !== null ? (int) $val : $defaultFromItem;
    }
}
