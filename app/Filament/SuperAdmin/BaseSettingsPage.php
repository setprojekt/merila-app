<?php

namespace App\Filament\SuperAdmin;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Concerns\HasUnsavedDataChangesAlert;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Throwable;

/**
 * Bazna stran za nastavitve – nadomestek za Filament\Pages\SettingsPage
 * (deluje tudi brez filament/spatie-laravel-settings-plugin na strežniku)
 */
abstract class BaseSettingsPage extends Page
{
    use CanUseDatabaseTransactions;
    use HasUnsavedDataChangesAlert;
    use InteractsWithFormActions;

    protected static string $settings;

    protected static string $view = 'filament.super-admin.pages.settings-page';

    public ?array $data = [];

    abstract public static function getSettings(): string;

    abstract protected function getSettingsFormSchema(): array;

    /** Override getFormSchema – InteractsWithForms sicer prepiše naš schema s praznim array */
    protected function getFormSchema(): array
    {
        return $this->getSettingsFormSchema();
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');
        try {
            $settings = app(static::getSettings());
            $data = $this->mutateFormDataBeforeFill($settings->toArray());
            $this->form->fill($data);
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Napaka pri nalaganju nastavitev')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
        $this->callHook('afterFill');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();
            $this->callHook('beforeValidate');
            $data = $this->form->getState();
            $this->callHook('afterValidate');
            $data = $this->mutateFormDataBeforeSave($data);
            $this->callHook('beforeSave');
            $settings = app(static::getSettings());
            $settings->fill($data);
            $settings->save();
            $this->callHook('afterSave');
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction()
                ? $this->rollBackDatabaseTransaction()
                : $this->commitDatabaseTransaction();
            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();
            throw $exception;
        }

        $this->commitDatabaseTransaction();
        $this->rememberData();
        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode($redirectUrl));
        }
    }

    public function getSavedNotification(): ?Notification
    {
        $title = $this->getSavedNotificationTitle();
        return $title
            ? Notification::make()->success()->title($title)
            : null;
    }

    public function getSavedNotificationTitle(): ?string
    {
        return __('Shranjeno');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    public function getFormActions(): array
    {
        return [$this->getSaveFormAction()];
    }

    public function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('Shrani'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema(fn () => $this->getSettingsFormSchema())
                    ->statePath('data')
                    ->columns(2)
                    ->inlineLabel($this->hasInlineLabels()),
            ),
        ];
    }

    public function getRedirectUrl(): ?string
    {
        return null;
    }
}
