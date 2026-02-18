<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Dan v tednu za obvestila ob 6–30 dneh (1=ponedeljek … 7=nedelja)
        $this->migrator->add('instruments.notification_day_of_week', 1);
    }

    public function down(): void
    {
        $this->migrator->delete('instruments.notification_day_of_week');
    }
};
