<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('competency_matrix.module_name', 'MUS Matrika usposobljenosti');
        $this->migrator->add('competency_matrix.module_number', 'SET 40.013');
        $this->migrator->add('competency_matrix.last_review_date', now()->format('Y-m-d'));
        $this->migrator->add('competency_matrix.send_email_notifications', true);
        $this->migrator->add('competency_matrix.notification_recipients', '');
        $this->migrator->add('competency_matrix.notification_time', '08:00');
        $this->migrator->add('competency_matrix.notification_day_of_week', 1);
        $this->migrator->add('competency_matrix.notification_interval_days', 7);
        $this->migrator->add('competency_matrix.notification_days_before_expiry', 60);
    }

    public function down(): void
    {
        $this->migrator->delete('competency_matrix.module_name');
        $this->migrator->delete('competency_matrix.module_number');
        $this->migrator->delete('competency_matrix.last_review_date');
        $this->migrator->delete('competency_matrix.send_email_notifications');
        $this->migrator->delete('competency_matrix.notification_recipients');
        $this->migrator->delete('competency_matrix.notification_time');
        $this->migrator->delete('competency_matrix.notification_day_of_week');
        $this->migrator->delete('competency_matrix.notification_interval_days');
        $this->migrator->delete('competency_matrix.notification_days_before_expiry');
    }
};
