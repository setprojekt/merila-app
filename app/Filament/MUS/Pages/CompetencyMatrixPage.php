<?php

namespace App\Filament\MUS\Pages;

use App\Models\CompetencyCategory;
use App\Models\CompetencyMatrixEntry;
use App\Models\User;
use App\Settings\Modules\CompetencyMatrixSettings;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CompetencyMatrixPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $slug = 'mus';

    protected static string $view = 'filament.mus.pages.competency-matrix';

    protected static ?string $navigationLabel = 'Matrika Usposobljenosti';

    protected static ?string $title = 'Matrika Usposobljenosti';

    protected static ?string $navigationGroup = 'MUS';

    protected static ?int $navigationSort = 1;

    /** Prikaži v navigaciji samo ko smo v MUS modulu, ne na strani modulov */
    public static function shouldRegisterNavigation(): bool
    {
        $path = request()->path();
        return str_contains($path, 'mus');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSuperAdmin() || $user->canAccessModule('mus'));
    }

    public array $selectedUserIds = [];

    public array $entries = [];

    public function mount(): void
    {
        $users = User::orderBy('employee_number')->orderBy('name')->get();
        $this->selectedUserIds = $users->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->loadEntries();
    }

    protected function loadEntries(): void
    {
        $existing = CompetencyMatrixEntry::whereIn('user_id', $this->selectedUserIds)
            ->get()
            ->keyBy(fn ($e) => "{$e->user_id}_{$e->competency_item_id}");

        $this->entries = [];
        foreach ($this->selectedUserIds as $userId) {
            foreach (\App\Models\CompetencyItem::all() as $item) {
                $key = "{$userId}_{$item->id}";
                $entry = $existing->get($key);
                $validUntil = $entry?->valid_until;
                $this->entries[$key] = [
                    'status' => $entry?->status ?? '',
                    'valid_until' => $validUntil?->format('Y-m-d') ?? '',
                    'validity_unlimited' => $item->allowsUnlimited() && $validUntil === null && $entry !== null,
                ];
            }
        }
    }

    public function updatedSelectedUserIds(): void
    {
        $this->loadEntries();
    }

    public function save(): void
    {
        DB::transaction(function () {
            foreach ($this->entries as $key => $data) {
                [$userId, $itemId] = explode('_', $key);
                $status = strtoupper(trim($data['status'] ?? ''));
                $status = in_array($status, ['T', 'U', 'O']) ? $status : '';
                $validityUnlimited = $data['validity_unlimited'] ?? false;
                $validUntil = $validityUnlimited ? null : (!empty($data['valid_until']) ? $data['valid_until'] : null);

                if ($status === '' && $validUntil === null && !$validityUnlimited) {
                    CompetencyMatrixEntry::where('user_id', $userId)
                        ->where('competency_item_id', $itemId)
                        ->delete();
                } elseif ($status !== '' || $validUntil !== null || $validityUnlimited) {
                    CompetencyMatrixEntry::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'competency_item_id' => $itemId,
                        ],
                        [
                            'status' => $status ?: null,
                            'valid_until' => $validUntil,
                        ]
                    );
                }
            }
        });

        Notification::make()
            ->title('Matrika shranjena')
            ->success()
            ->send();
    }

    public function getUsers()
    {
        return User::whereIn('id', $this->selectedUserIds)
            ->orderBy('employee_number')
            ->orderBy('name')
            ->get();
    }

    public function getCategories()
    {
        return CompetencyCategory::with('items')->orderBy('sort_order')->get();
    }

    public function getSettings(): CompetencyMatrixSettings
    {
        return app(CompetencyMatrixSettings::class);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Shrani Matriko')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('save'),
        ];
    }
}
