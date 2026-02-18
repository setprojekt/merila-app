<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <div class="mb-6 p-4 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-700 rounded-lg">
            <div class="flex items-center gap-3">
                <x-filament::icon
                    icon="heroicon-o-exclamation-triangle"
                    class="h-6 w-6 text-warning-600 dark:text-warning-400"
                />
                <div>
                    <h3 class="text-lg font-semibold text-warning-900 dark:text-warning-100">
                        Sprememba gesla je obvezna
                    </h3>
                    <p class="text-sm text-warning-700 dark:text-warning-300 mt-1">
                        Administrator je resetiral vaše geslo. Za varnost morate nastaviti novo geslo, preden lahko nadaljujete z uporabo aplikacije.
                    </p>
                </div>
            </div>
        </div>

        <x-filament-panels::form wire:submit="save">
            {{ $this->form }}
            
            <x-filament-panels::form.actions
                :actions="$this->getFormActions()"
                :full-width="true"
            />
        </x-filament-panels::form>
    </div>
</x-filament-panels::page>
