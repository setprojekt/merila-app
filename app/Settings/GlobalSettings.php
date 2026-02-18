<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GlobalSettings extends Settings
{
    // Email nastavitve (geslo samo iz .env – MAIL_PASSWORD)
    public string $mail_from_address;
    public string $mail_from_name;
    public string $notification_email;

    // Sistemske nastavitve
    public string $app_name;
    public string $company_name;
    public string $company_address;
    public string $company_phone;
    public string $company_email;
    
    // Varnostne nastavitve
    public int $auto_logout_timeout; // Čas nedejavnosti v sekundah (0 = onemogočeno)
    
    public static function group(): string
    {
        return 'global';
    }
}
