<x-filament-panels::page>
    <style>
        .fi-main {
            background-color: rgb(243 244 246) !important;
        }
        .dark .fi-main {
            background-color: rgb(3 7 18) !important;
        }
    </style>
    <div class="space-y-4">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Dobrodošli
                    </h2>
                    <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
                        Izberite modul za začetek dela
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <x-filament::icon
                        icon="heroicon-o-squares-2x2"
                        class="w-10 h-10 text-primary-500"
                    />
                </div>
            </div>
        </div>

        <!-- Module Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->getModules() as $module)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all hover:shadow-md hover:border-{{ $module['color'] }}-300 {{ $module['enabled'] ? '' : 'opacity-60' }}">
                    <a href="{{ $module['enabled'] ? $module['url'] : '#' }}" class="block {{ $module['enabled'] ? '' : 'cursor-not-allowed' }}">
                        <!-- Card Header -->
                        <div class="bg-{{ $module['color'] }}-600 dark:bg-{{ $module['color'] }}-700 px-4 pt-4 pb-2">
                            <div class="flex items-center justify-between">
                                <x-filament::icon
                                    :icon="$module['icon']"
                                    class="w-10 h-10 text-gray-900 dark:text-white"
                                />
                                @if($module['badge'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white">
                                        {{ $module['badge'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="px-4 pb-4 pt-2">
                            @if(isset($module['module_number']))
                                <h2 class="text-xl font-bold text-{{ $module['color'] }}-700 dark:text-{{ $module['color'] }}-400 mb-2">
                                    {{ $module['module_number'] }}
                                </h2>
                            @endif
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                {{ $module['name'] }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                {{ $module['description'] }}
                            </p>

                            <!-- Stats -->
                            @if(count($module['stats']) > 0)
                                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    @foreach($module['stats'] as $stat)
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $stat['label'] }}
                                            </p>
                                            <p class="text-xl font-bold text-{{ $module['color'] }}-600 dark:text-{{ $module['color'] }}-400">
                                                {{ $stat['value'] }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Action -->
                            @if($module['enabled'])
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-sm font-medium text-{{ $module['color'] }}-600 dark:text-{{ $module['color'] }}-400 inline-flex items-center">
                                        Odpri modul
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </span>
                                </div>
                            @else
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-sm text-gray-400 dark:text-gray-500">
                                        Še ni na voljo
                                    </span>
                                </div>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start">
                <x-filament::icon
                    icon="heroicon-o-information-circle"
                    class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-2.5"
                />
                <div>
                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-0.5">
                        Potrebujete pomoč?
                    </h4>
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Za vprašanja in podporo se obrnite na administratorja sistema ali preverite dokumentacijo.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
