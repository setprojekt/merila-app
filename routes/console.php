<?php

use App\Settings\Modules\InstrumentsSettings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Email scheduler - čas pošiljanja iz Nastavitev meril (format HH:MM)
$notificationTime = '08:00';
try {
    $instrumentsSettings = app(InstrumentsSettings::class);
    $notificationTime = $instrumentsSettings->notification_time ?? '08:00';
    if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $notificationTime, $m)) {
        $notificationTime = sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
    }
} catch (\Throwable $e) {
    // Ob prvem zagonu ali če nastavitve še niso na voljo
}
Schedule::command('instruments:send-reminders')
    ->dailyAt($notificationTime)
    ->timezone('Europe/Ljubljana');

// MUS - obveščanje o preteku usposobljenosti
$competencyNotificationTime = '08:00';
try {
    $competencySettings = app(\App\Settings\Modules\CompetencyMatrixSettings::class);
    $competencyNotificationTime = $competencySettings->notification_time ?? '08:00';
} catch (\Throwable $e) {}
Schedule::command('competency:send-expiry-reminders')
    ->dailyAt($competencyNotificationTime)
    ->timezone('Europe/Ljubljana');

// Activity Log cleanup - počisti stare zapise enkrat na mesec
Schedule::command('activitylog:cleanup --months=6')
    ->monthly()
    ->timezone('Europe/Ljubljana');
