<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CompetencyExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $allEntries,
        public Collection $expired,
        public Collection $urgent,
        public Collection $warning
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'MUS - Opozorilo o Preteku Usposobljenosti',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.competency.expiry-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
