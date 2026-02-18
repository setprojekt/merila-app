<?php

namespace App\Settings\Modules;

use Spatie\LaravelSettings\Settings;

class InstrumentsSettings extends Settings
{
    // Modul nastavitve
    public string $module_name;
    public string $module_number;
    
    // Dobavnica nastavitve
    public string $delivery_note_sender_name;
    public string $delivery_note_sender_address;
    public string $delivery_note_recipient_name;
    public string $delivery_note_recipient_address;
    
    // Email nastavitve specifične za merila
    public bool $send_email_notifications;
    public string $notification_recipients; // Comma-separated emails
    public string $notification_time; // HH:MM - ura pošiljanja
    public int $notification_day_of_week; // 1=ponedeljek … 7=nedelja (za obvestila pri 6–30 dneh)
    
    // Opozorila
    public int $expiry_warning_days; // Št. dni pred potekom za "warning" status
    public int $expiry_alert_days; // Št. dni pred potekom za "expired" status
    
    // Arhiviranje
    public bool $auto_archive_expired;
    public int $auto_archive_after_days; // Št. dni po poteku za avtomatsko arhiviranje
    
    public static function group(): string
    {
        return 'instruments';
    }
}
