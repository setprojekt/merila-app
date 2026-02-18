<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('competency_matrix.item_frequency_years', []);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('competency_matrix.item_frequency_years');
    }
};
