<x-filament-panels::page>
    <div class="space-y-4">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Super Admin Nadzorna plošča
                    </h2>
                    <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
                        Upravljanje sistema, uporabnikov in nastavitev
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <x-filament::icon
                        icon="heroicon-o-shield-check"
                        class="w-10 h-10 text-red-500"
                    />
                </div>
            </div>
        </div>

        <!-- Module Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->getModules() as $module)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all hover:shadow-md">
                    <a href="{{ $module['url'] }}" class="block">
                        <!-- Card Header -->
                        <div class="{{ $module['bg_color'] }} px-4 pt-4 pb-2">
                            <div class="flex items-center justify-between">
                                <x-filament::icon
                                    :icon="$module['icon']"
                                    class="w-10 h-10 text-gray-900 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="px-4 pb-4 pt-2">
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
                                            <p class="text-xl font-bold {{ $module['text_color'] }}">
                                                {{ $stat['value'] }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Action -->
                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-sm font-medium {{ $module['text_color'] }} inline-flex items-center">
                                    Odpri
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Info Box -->
        <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 border border-red-200 dark:border-red-800">
            <div class="flex items-start">
                <x-filament::icon
                    icon="heroicon-o-shield-exclamation"
                    class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-2.5"
                />
                <div>
                    <h4 class="text-sm font-semibold text-red-900 dark:text-red-100 mb-0.5">
                        Super Admin Opozorilo
                    </h4>
                    <p class="text-sm text-red-700 dark:text-red-300">
                        Imate popoln dostop do sistema. Spremembe tukaj vplivajo na vse uporabnike in module.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
