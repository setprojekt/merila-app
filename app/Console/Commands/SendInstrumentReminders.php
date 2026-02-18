<?php

namespace App\Console\Commands;

use App\Models\Instrument;
use App\Mail\InstrumentReminderMail;
use App\Settings\Modules\InstrumentsSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendInstrumentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instruments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pošlji email opozorila za merila, ki kmalu potečejo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $instrumentsSettings = app(InstrumentsSettings::class);

        if (!$instrumentsSettings->send_email_notifications) {
            $this->info('Email obvestila za merila so onemogočena v Nastavitvah meril.');
            return 0;
        }
        
        $today = Carbon::today();
        $dayOfWeek = $today->dayOfWeek; // Carbon: 0=nedelja, 1=ponedeljek, ..., 6=sobota
        $configuredDay = $instrumentsSettings->notification_day_of_week ?? 1; // 1=ponedeljek … 7=nedelja
        $configuredDayCarbon = $configuredDay === 7 ? 0 : $configuredDay; // pretvorba v Carbon dan
        
        $warningDays = $instrumentsSettings->expiry_warning_days; // 30
        $alertDays = $instrumentsSettings->expiry_alert_days; // 5
        
        // Vsa aktivna merila (ne arhivirana, ne v kontroli), z nastavljenim next_check_date
        $instruments = Instrument::where('archived', false)
            ->where('status', '!=', 'V_KONTROLI')
            ->whereNotNull('next_check_date')
            ->get();
        
        $instrumentsToNotify = collect();
        
        foreach ($instruments as $instrument) {
            $daysUntilExpiry = $today->diffInDays($instrument->next_check_date, false);
            
            // Ob ponedeljkih: pošlji vsa merila, ki so v kriterijih (≤ 30 dni ali pretečeno)
            if ($dayOfWeek === 1) {
                if ($daysUntilExpiry <= $warningDays || $daysUntilExpiry < 0) {
                    $instrumentsToNotify->push($instrument);
                }
                continue;
            }
            
            // Pretečena merila (že preteklo): vsak dan
            if ($daysUntilExpiry < 0) {
                $instrumentsToNotify->push($instrument);
                continue;
            }
            
            // 5 dni in manj do preteka: vsak dan (samo še nepregledana)
            if ($daysUntilExpiry < $alertDays) {
                $instrumentsToNotify->push($instrument);
                continue;
            }
            
            // 6–30 dni do preteka: samo na nastavljeni dan v tednu
            if ($daysUntilExpiry >= $alertDays && $daysUntilExpiry <= $warningDays) {
                if ($dayOfWeek === $configuredDayCarbon) {
                    $instrumentsToNotify->push($instrument);
                }
            }
        }
        
        // Če ni nobenega merila v kriteriju, ne pošiljamo nič
        if ($instrumentsToNotify->isEmpty()) {
            $this->info('Ni meril v kriteriju – email se ne pošlje.');
            return 0;
        }
        
        // TODO: Implementiraj email pošiljanje
        // Za zdaj samo logiramo
        $this->info('Najdenih ' . $instrumentsToNotify->count() . ' meril za opozorilo.');
        
        // Razdeli po kategorijah
        $expired = $instrumentsToNotify->filter(function($i) use ($today) {
            return $today->diffInDays($i->next_check_date, false) < 0;
        });
        $urgent = $instrumentsToNotify->filter(function($i) use ($today, $alertDays) {
            $days = $today->diffInDays($i->next_check_date, false);
            return $days >= 0 && $days < $alertDays;
        });
        $warning = $instrumentsToNotify->filter(function($i) use ($today, $warningDays, $alertDays) {
            $days = $today->diffInDays($i->next_check_date, false);
            return $days >= $alertDays && $days <= $warningDays;
        });
        
        $this->table(
            ['Kategorija', 'Število'],
            [
                ['Pretečena', $expired->count()],
                ['Nujno (<' . $alertDays . ' dni)', $urgent->count()],
                ['Opozorilo (' . $alertDays . '-' . $warningDays . ' dni)', $warning->count()],
            ]
        );
        
        // Pošlji email
        $recipients = explode(',', $instrumentsSettings->notification_recipients);
        $recipients = array_map('trim', $recipients);
        
        foreach ($recipients as $recipient) {
            if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                Mail::to($recipient)->send(new InstrumentReminderMail(
                    $instrumentsToNotify,
                    $expired,
                    $urgent,
                    $warning
                ));
                $this->info("Email poslan na: {$recipient}");
            }
        }
        
        return 0;
    }
}
