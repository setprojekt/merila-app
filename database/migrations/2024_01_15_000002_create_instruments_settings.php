<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Instruments Module Settings - Dobavnica nastavitve
        $this->migrator->add('instruments.delivery_note_sender_name', 'SET Trade d.o.o.');
        $this->migrator->add('instruments.delivery_note_sender_address', 'Ulica 1, 1000 Ljubljana, Slovenija');
        $this->migrator->add('instruments.delivery_note_recipient_name', 'Kontrolni Laboratorij');
        $this->migrator->add('instruments.delivery_note_recipient_address', 'Testna ulica 2, 1000 Ljubljana, Slovenija');
        
        // Email nastavitve
        $this->migrator->add('instruments.send_email_notifications', true);
        $this->migrator->add('instruments.notification_recipients', 'merila@set-trade.si');
        
        // Opozorila
        $this->migrator->add('instruments.expiry_warning_days', 30);
        $this->migrator->add('instruments.expiry_alert_days', 5);
        
        // Arhiviranje
        $this->migrator->add('instruments.auto_archive_expired', false);
        $this->migrator->add('instruments.auto_archive_after_days', 90);
    }
    
    public function down(): void
    {
        // Instruments Module Settings
        $this->migrator->delete('instruments.delivery_note_sender_name');
        $this->migrator->delete('instruments.delivery_note_sender_address');
        $this->migrator->delete('instruments.delivery_note_recipient_name');
        $this->migrator->delete('instruments.delivery_note_recipient_address');
        
        $this->migrator->delete('instruments.send_email_notifications');
        $this->migrator->delete('instruments.notification_recipients');
        
        $this->migrator->delete('instruments.expiry_warning_days');
        $this->migrator->delete('instruments.expiry_alert_days');
        
        $this->migrator->delete('instruments.auto_archive_expired');
        $this->migrator->delete('instruments.auto_archive_after_days');
    }
};
