<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->deleteIfExists('competency_matrix.last_review_date');
    }

    public function down(): void
    {
        $this->migrator->add('competency_matrix.last_review_date', now()->format('Y-m-d'));
    }
};
