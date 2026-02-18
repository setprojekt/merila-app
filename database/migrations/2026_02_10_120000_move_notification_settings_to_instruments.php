<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Čas pošiljanja obvestil v nastavitve meril
        $this->migrator->add('instruments.notification_time', '08:00');

        // Odstrani nastavitve obvestil iz globalnih (premaknjene v merila)
        $this->migrator->delete('global.enable_notifications');
        $this->migrator->delete('global.notification_time');
        $this->migrator->delete('global.warning_days_before_expiry');
    }

    public function down(): void
    {
        $this->migrator->delete('instruments.notification_time');

        $this->migrator->add('global.enable_notifications', true);
        $this->migrator->add('global.notification_time', '08:00');
        $this->migrator->add('global.warning_days_before_expiry', 30);
    }
};
