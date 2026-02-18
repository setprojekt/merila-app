<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->addEncrypted('global.mail_password', null);
    }

    public function down(): void
    {
        $this->migrator->delete('global.mail_password');
    }
};
