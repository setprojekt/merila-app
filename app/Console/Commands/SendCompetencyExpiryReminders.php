<?php

namespace App\Console\Commands;

use App\Mail\CompetencyExpiryReminderMail;
use App\Models\CompetencyMatrixEntry;
use App\Settings\Modules\CompetencyMatrixSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCompetencyExpiryReminders extends Command
{
    protected $signature = 'competency:send-expiry-reminders';

    protected $description = 'Pošlji email opozorila za zakonsko predpisane usposobljenosti, ki kmalu potečejo';

    public function handle(): int
    {
        $settings = app(CompetencyMatrixSettings::class);

        if (!$settings->send_email_notifications) {
            $this->info('Email obvestila za usposobljenost so onemogočena.');
            return 0;
        }

        $today = Carbon::today();
        $dayOfWeek = $today->dayOfWeek;
        $configuredDay = $settings->notification_day_of_week ?? 1;
        $configuredDayCarbon = $configuredDay === 7 ? 0 : $configuredDay;
        $defaultDaysBefore = $settings->notification_days_before_expiry ?? 60;

        $entries = CompetencyMatrixEntry::with(['user', 'competencyItem.category'])
            ->whereNotNull('valid_until')
            ->orderBy('valid_until')
            ->get();

        $toNotify = collect();

        foreach ($entries as $entry) {
            $item = $entry->competencyItem;
            if ($item && $item->allow_unlimited) {
                continue;
            }

            $daysBeforeForItem = $defaultDaysBefore;
            if ($item) {
                $freqYears = $settings->getFrequencyYearsForItem($item->id, $item->validity_years);
                if ($freqYears !== null) {
                    $daysBeforeForItem = (int) ($freqYears * 30);
                }
            }

            if ($entry->valid_until->gt($today->copy()->addDays($daysBeforeForItem))) {
                continue;
            }

            $daysUntilExpiry = $today->diffInDays($entry->valid_until, false);

            if ($daysUntilExpiry < 0) {
                $toNotify->push($entry);
                continue;
            }

            if ($daysUntilExpiry <= 30) {
                $toNotify->push($entry);
                continue;
            }

            if ($dayOfWeek === $configuredDayCarbon && $daysUntilExpiry <= $daysBeforeForItem) {
                $toNotify->push($entry);
            }
        }

        if ($toNotify->isEmpty()) {
            $this->info('Ni usposobljenosti v kriteriju za obvestilo.');
            return 0;
        }

        $expired = $toNotify->filter(fn ($e) => $today->gt($e->valid_until));
        $urgent = $toNotify->filter(fn ($e) => $today->lte($e->valid_until) && $today->diffInDays($e->valid_until) < 30);
        $warning = $toNotify->filter(fn ($e) => $today->lte($e->valid_until) && $today->diffInDays($e->valid_until) >= 30);

        $recipients = array_map('trim', explode(',', $settings->notification_recipients ?? ''));

        foreach ($recipients as $recipient) {
            if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                Mail::to($recipient)->send(new CompetencyExpiryReminderMail(
                    $toNotify,
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
