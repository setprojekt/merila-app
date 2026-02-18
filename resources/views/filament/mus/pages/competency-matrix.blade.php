<x-filament-panels::page>
    <style>
        .matrix-date-input {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }
        .matrix-date-input:focus {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }
        .matrix-date-input::-webkit-calendar-picker-indicator {
            opacity: 0.4;
            cursor: pointer;
        }
        .matrix-vertical-header {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            transform: rotate(180deg);
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            width: 2rem;
            min-width: 2rem;
            height: 16rem;
            padding: 0.5rem 0.25rem;
            vertical-align: middle;
            text-align: center;
            line-height: 1.25;
            font-size: 0.75rem;
        }
        .matrix-vertical-header .vertical-text {
            display: inline-block;
            text-align: center;
            max-width: 100%;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .matrix-data-cell {
            width: 2rem;
            min-width: 2rem;
        }
        .matrix-date-cell {
            min-width: 8rem !important;
            width: 8rem !important;
        }
        .matrix-employee-select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: none !important;
        }
    </style>
    <div class="space-y-4">
        {{-- Izbor zaposlenih --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Izberi zaposlene za matriko</h3>
            <select wire:model.live="selectedUserIds" multiple class="matrix-employee-select fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm min-h-[120px]">
                @foreach(\App\Models\User::orderBy('employee_number')->orderBy('name')->get() as $u)
                    <option value="{{ $u->id }}">
                        {{ $u->employee_number ? "{$u->employee_number} - " : '' }}{{ $u->full_name }}{{ $u->function ? " ({$u->function})" : '' }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Držite Ctrl (Cmd) za izbiro več zaposlenih</p>
        </div>

        {{-- Identifikacija modula --}}
        <div class="text-sm text-gray-600 dark:text-gray-400">
            <strong>{{ $this->getSettings()->module_number }}</strong>
        </div>

        {{-- Matrika --}}
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <th class="border border-gray-200 dark:border-gray-600 px-2 py-2 text-left font-semibold sticky left-0 bg-gray-100 dark:bg-gray-700 z-10 min-w-[80px] align-middle">Zap. št.</th>
                        <th class="border border-gray-200 dark:border-gray-600 px-2 py-2 text-left font-semibold sticky left-[80px] bg-gray-100 dark:bg-gray-700 z-10 min-w-[150px] align-middle">Ime in priimek</th>
                        <th class="border border-gray-200 dark:border-gray-600 px-2 py-2 text-left font-semibold min-w-[120px] align-middle">Funkcija</th>
                        @foreach($this->getCategories() as $category)
                            @foreach($category->items as $item)
                                <th class="matrix-vertical-header border border-gray-200 dark:border-gray-600 font-medium {{ $item->requires_validity ? 'matrix-date-cell' : '' }} {{ $loop->first ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-gray-100 dark:bg-gray-700' }}" title="{{ $item->name }}">
                                    <span class="vertical-text">
                                        {{ $item->name }}
                                        @if($item->requires_validity)
                                            <span class="block text-amber-600 dark:text-amber-400 mt-0.5">velja do</span>
                                        @endif
                                    </span>
                                </th>
                            @endforeach
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->getUsers() as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="border border-gray-200 dark:border-gray-600 px-2 py-1 sticky left-0 bg-white dark:bg-gray-800 align-middle">{{ $user->employee_number ?? '-' }}</td>
                            <td class="border border-gray-200 dark:border-gray-600 px-2 py-1 sticky left-[80px] bg-white dark:bg-gray-800 font-medium align-middle">{{ $user->full_name }}</td>
                            <td class="border border-gray-200 dark:border-gray-600 px-2 py-1 align-middle">{{ $user->function ?? '-' }}</td>
                            @foreach($this->getCategories() as $category)
                                @foreach($category->items as $item)
                                    @php $key = "{$user->id}_{$item->id}"; $entry = $this->entries[$key] ?? ['status' => '', 'valid_until' => '', 'validity_unlimited' => false]; @endphp
                                    <td class="matrix-data-cell {{ $item->requires_validity ? 'matrix-date-cell' : '' }} border border-gray-200 dark:border-gray-600 p-0 align-middle text-center" wire:key="cell-{{ $key }}">
                                        @if($item->allowsUnlimited())
                                            {{-- Samo Izpit za vožnjo viličarja: samo Neomejeno, brez statusa in datuma --}}
                                            <label class="flex items-center justify-center gap-1.5 text-sm font-medium cursor-pointer px-1 py-1 min-h-[2.5rem] text-gray-900 dark:text-white">
                                                <input type="checkbox" wire:model.live="entries.{{ $key }}.validity_unlimited" class="rounded border-gray-400">
                                                <span>Neomejeno</span>
                                            </label>
                                        @elseif($item->requires_validity)
                                            {{-- Samo datum; prazna polja prikazujejo " .  . " za preglednost; enaka širina pri praznem in vnešenem datumu --}}
                                            <div class="relative min-h-[2.5rem] w-full min-w-[8rem] flex items-center justify-center">
                                                @if(empty($entry['valid_until']))
                                                    <span class="text-gray-400 dark:text-gray-500 text-base font-medium w-full text-center"> .  . </span>
                                                    <input type="date" wire:model.live="entries.{{ $key }}.valid_until" class="matrix-date-input absolute inset-0 w-full opacity-0 cursor-pointer" title="Klikni za izbiro datuma">
                                                @else
                                                    <input type="date" wire:model.live="entries.{{ $key }}.valid_until" class="matrix-date-input block w-full min-h-[2.5rem] min-w-[8rem] text-base font-medium border-0 bg-transparent dark:bg-transparent text-gray-900 dark:text-white px-1 py-1 text-center focus:ring-0 focus:outline-none" title="Klikni za izbiro datuma">
                                                @endif
                                            </div>
                                        @else
                                            <input type="text" wire:model.live="entries.{{ $key }}.status" maxlength="1" placeholder="" title="T, U ali O" class="matrix-status-input block w-full min-h-[2.5rem] rounded border-0 bg-transparent dark:bg-transparent text-gray-900 dark:text-white px-1 py-1 text-center font-semibold focus:ring-0 focus:outline-none" style="text-transform: uppercase;">
                                        @endif
                                    </td>
                                @endforeach
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legenda --}}
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">LEGENDA</h4>
            <div class="flex flex-wrap gap-4 text-sm">
                <span><strong>(Prazno polje):</strong> Usposabljanje ni potrebno</span>
                <span><strong>U:</strong> Usposabljanje potrebno in se izvaja / Planirano</span>
                <span><strong>O:</strong> Usposobljen za samostojno delo</span>
                <span><strong>T:</strong> Usposobljen - lahko prenaša znanja</span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
