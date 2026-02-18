<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Global Settings
        $this->migrator->add('global.mail_from_address', 'opomnik@set-trade.si');
        $this->migrator->add('global.mail_from_name', 'SET Merila - Opomnik');
        $this->migrator->add('global.notification_email', 'opomnik@set-trade.si');
        
        $this->migrator->add('global.app_name', 'SET Merila');
        $this->migrator->add('global.company_name', 'SET Trade d.o.o.');
        $this->migrator->add('global.company_address', 'Ulica 1\n1000 Ljubljana\nSlovenija');
        $this->migrator->add('global.company_phone', '+386 1 234 5678');
        $this->migrator->add('global.company_email', 'info@set-trade.si');
        
        $this->migrator->add('global.enable_notifications', true);
        $this->migrator->add('global.notification_time', '08:00');
        $this->migrator->add('global.warning_days_before_expiry', 30);
    }
    
    public function down(): void
    {
        // Global Settings
        $this->migrator->delete('global.mail_from_address');
        $this->migrator->delete('global.mail_from_name');
        $this->migrator->delete('global.notification_email');
        
        $this->migrator->delete('global.app_name');
        $this->migrator->delete('global.company_name');
        $this->migrator->delete('global.company_address');
        $this->migrator->delete('global.company_phone');
        $this->migrator->delete('global.company_email');
        
        $this->migrator->delete('global.enable_notifications');
        $this->migrator->delete('global.notification_time');
        $this->migrator->delete('global.warning_days_before_expiry');
    }
};
