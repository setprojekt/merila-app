<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-6 border border-amber-200 dark:border-amber-800">
            <div class="flex items-center gap-3">
                <div class="text-4xl">📚</div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Modul Merila (70.0001)
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Navodila za upravljanje meril, dobavnic in kalibracij
                    </p>
                </div>
            </div>
        </div>

        <!-- 1. OSNOVNI PREGLED -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>📋</span> 1. Osnovni pregled
            </h3>
            <div class="prose dark:prose-invert max-w-none">
                <p>Modul Merila omogoča centralizirano vodenje merilne opreme s sledilnostjo:</p>
                <ul>
                    <li><strong>Evidenca vseh meril</strong> - seznam aktivnih meril v uporabi</li>
                    <li><strong>Sledenje veljavnosti</strong> - avtomatsko opozarjanje na poteke</li>
                    <li><strong>Dobavnice</strong> - pošiljanje meril na kontrolo/kalibracijo</li>
                    <li><strong>Poročila in statistike</strong> - pregled stanja po statusih</li>
                </ul>
            </div>
        </div>

        <!-- 2. DODAJANJE MERILA -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>➕</span> 2. Dodajanje novega merila
            </h3>
            <div class="prose dark:prose-invert max-w-none">
                <ol>
                    <li>Pojdite na <strong>Merila → Merila</strong></li>
                    <li>Kliknite gumb <strong>"Novo merilo"</strong></li>
                    <li>Izpolnite osnovne podatke:
                        <ul>
                            <li><strong>Številka merila</strong> - interna identifikacijska številka (npr. M-001)</li>
                            <li><strong>Ime merila</strong> - opisno ime (npr. "Digitalni merilnik")</li>
                            <li><strong>Vrsta merila</strong> - kategorija merila</li>
                            <li><strong>Lokacija</strong> - kje se merilo nahaja</li>
                            <li><strong>Oddelek</strong> - kateremu oddelku pripada</li>
                        </ul>
                    </li>
                    <li>Nastavite parametre pregleda:
                        <ul>
                            <li><strong>Frekvenca pregleda</strong> - vsako X let (npr. 2.0 leta)</li>
                            <li><strong>Datum zadnjega pregleda</strong> - kdaj je bilo nazadnje pregledano</li>
                            <li><strong>Status</strong> - trenutni status (Ustreza/Ne ustreza/Izločeno)</li>
                        </ul>
                    </li>
                    <li>Po želji naložite <strong>certifikat PDF</strong></li>
                    <li>Kliknite <strong>"Shrani"</strong></li>
                </ol>
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mt-4">
                    <p class="text-sm"><strong>💡 Namig:</strong> Datum naslednjega pregleda se izračuna avtomatsko na podlagi zadnjega pregleda in frekvence.</p>
                </div>
            </div>
        </div>

        <!-- 3. PREGLED STATUSOV -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>🚦</span> 3. Razumevanje statusov
            </h3>
            <div class="space-y-3">
                <div class="flex items-start gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <span class="text-2xl">🟢</span>
                    <div>
                        <p class="font-semibold text-green-900 dark:text-green-100">Veljavna merila (>30 dni)</p>
                        <p class="text-sm text-green-700 dark:text-green-300">Merila so veljavna, naslednji pregled je čez več kot 30 dni.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <span class="text-2xl">🟡</span>
                    <div>
                        <p class="font-semibold text-yellow-900 dark:text-yellow-100">Opozorilo (≤30 dni)</p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">Merilo kmalu poteče - potrebna je kontrola v naslednjih 30 dneh.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <span class="text-2xl">🔴</span>
                    <div>
                        <p class="font-semibold text-red-900 dark:text-red-100">Pretečeno</p>
                        <p class="text-sm text-red-700 dark:text-red-300">Merilo je že poteklo - takojšnja kontrola obvezna!</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <span class="text-2xl">🔵</span>
                    <div>
                        <p class="font-semibold text-blue-900 dark:text-blue-100">V kontroli</p>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Merilo je na dobavnici, poslano na kontrolo/kalibracijo.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-900/20 rounded-lg">
                    <span class="text-2xl">⚫</span>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">Izločeno</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">Merilo je izločeno iz uporabe, avtomatsko arhivirano.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. POŠILJANJE NA KONTROLO -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>📦</span> 4. Pošiljanje meril na kontrolo
            </h3>
            <div class="prose dark:prose-invert max-w-none">
                <ol>
                    <li>Pojdite na <strong>Merila → Merila</strong></li>
                    <li>Označite merila, ki jih želite poslati na kontrolo (checkboxi ob vsaki vrstici)</li>
                    <li>V spodnjem meniju izberite <strong>"Pošlji na kontrolo"</strong></li>
                    <li>Potrdite dejanje</li>
                    <li>Sistem avtomatsko:
                        <ul>
                            <li>Ustvari novo <strong>dobavnico</strong> s podatki iz nastavitev</li>
                            <li>Doda izbrana merila na dobavnico</li>
                            <li>Spremeni status meril na <strong>"V kontroli"</strong></li>
                            <li>Preusmeri na dobavnico za urejanje</li>
                        </ul>
                    </li>
                </ol>
            </div>
        </div>

        <!-- 5. UPRAVLJANJE DOBAVNIC -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>📄</span> 5. Dobavnice
            </h3>
            <div class="prose dark:prose-invert max-w-none">
                <h4>Pregled dobavnice:</h4>
                <ul>
                    <li>Pojdite na <strong>Dobavnice → Dobavnice</strong></li>
                    <li>Kliknite na dobavnico za pregled</li>
                    <li>Kliknite <strong>"Natisni"</strong> za PDF verzijo</li>
                </ul>
                
                <h4>Vračilo meril:</h4>
                <ol>
                    <li>Odprite dobavnico v <strong>način urejanja</strong></li>
                    <li>Pri vsakem merilu lahko označite:
                        <ul>
                            <li><strong>Vrnjeno</strong> - označite checkbox</li>
                            <li><strong>Datum vrnitve</strong> - kdaj je bilo vrnjeno</li>
                            <li><strong>Datum pregleda</strong> - kdaj je bilo pregledano</li>
                            <li><strong>Rezultat kontrole</strong> - Ustreza/Ne ustreza/Izločeno</li>
                            <li><strong>Opombe</strong> - dodatne opombe</li>
                        </ul>
                    </li>
                    <li>Ko označite merilo kot vrnjeno, sistem avtomatsko:
                        <ul>
                            <li>Posodobi <strong>datum zadnjega pregleda</strong> merila</li>
                            <li>Izračuna nov <strong>datum naslednjega pregleda</strong></li>
                            <li>Posodobi <strong>status</strong> merila</li>
                        </ul>
                    </li>
                    <li>Ko so vsa merila vrnjena, lahko dobavnico <strong>arhivirate</strong></li>
                </ol>
            </div>
        </div>

        <!-- 6. FILTRIRANJE IN ISKANJE -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>🔍</span> 6. Filtriranje in iskanje
            </h3>
            <div class="prose dark:prose-invert max-w-none">
                <p>Na seznamu meril lahko uporabite:</p>
                <ul>
                    <li><strong>Iskalno polje</strong> - iščite po številki, imenu, vrsti, lokaciji</li>
                    <li><strong>Filtri</strong> (ikona lijaka):
                        <ul>
                            <li><strong>Status</strong> - filtrirajte po statusu</li>
                            <li><strong>Potrebuje pozornost</strong> - prikaže pretečena + opozorila</li>
                            <li><strong>Pretečena</strong> - samo pretečena merila</li>
                            <li><strong>Opozorilo</strong> - samo merila z opozorilom</li>
                            <li><strong>Veljavna merila</strong> - samo veljavna</li>
                            <li><strong>Prikaži arhivirana</strong> - prikaže arhivirana merila</li>
                        </ul>
                    </li>
                    <li><strong>Sortiranje</strong> - kliknite na glavo stolpca za sortiranje</li>
                    <li><strong>Toggle stolpci</strong> - prikažite/skrijte stolpce</li>
                </ul>
            </div>
        </div>

        <!-- 7. DASHBOARD IN STATISTIKE -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>📊</span> 7. Dashboard in statistike
            </h3>
            <div class="prose dark:prose-invert max-w-none">
                <p>Na nadzorni plošči (<strong>Merila → Nadzorna plošča</strong>) vidite:</p>
                <ul>
                    <li><strong>5 kartic s statistikami:</strong>
                        <ul>
                            <li>Veljavna merila - klikljivo (odpre filtrirano tabelo)</li>
                            <li>Opozorilo - merila, ki potečejo v 30 dneh</li>
                            <li>Pretečena merila - takojšnja pozornost</li>
                            <li>Merila v kontroli - poslana na pregled</li>
                            <li>Arhivirana merila - izločena iz uporabe</li>
                        </ul>
                    </li>
                    <li><strong>Hitre povezave</strong> - vsaka kartica vas pelje na ustrezno stran</li>
                </ul>
            </div>
        </div>

        <!-- 8. NASTAVITVE -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>⚙️</span> 8. Nastavitve modula
            </h3>
            <div class="prose dark:prose-invert max-w-none">
                <p>Super Admin lahko nastavi (<strong>Super Admin → Nastavitve Meril</strong>):</p>
                <ul>
                    <li><strong>Identifikacija modula:</strong>
                        <ul>
                            <li>Ime modula (npr. "Merila", "Vodenje Meril")</li>
                            <li>Številka modula (npr. "70.0001")</li>
                        </ul>
                    </li>
                    <li><strong>Dobavnica nastavitve:</strong>
                        <ul>
                            <li>Privzeto ime in naslov pošiljatelja</li>
                            <li>Privzeto ime in naslov prejemnika</li>
                        </ul>
                    </li>
                    <li><strong>Email obvestila:</strong>
                        <ul>
                            <li>Omogoči/onemogoči email opozorila</li>
                            <li>Seznam prejemnikov opozoril</li>
                            <li>Testno pošiljanje</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <!-- 9. HITRE POVEZAVE -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>🔗</span> 9. Hitre povezave
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <a href="/merila" class="flex items-center gap-2 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/30 transition">
                    <span class="text-xl">🏠</span>
                    <span class="font-medium">Nadzorna plošča</span>
                </a>
                <a href="/merila/instruments" class="flex items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition">
                    <span class="text-xl">📋</span>
                    <span class="font-medium">Seznam meril</span>
                </a>
                <a href="/merila/delivery-notes" class="flex items-center gap-2 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition">
                    <span class="text-xl">📦</span>
                    <span class="font-medium">Dobavnice</span>
                </a>
                <a href="/merila/instruments-in-control" class="flex items-center gap-2 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition">
                    <span class="text-xl">🔍</span>
                    <span class="font-medium">Merila v kontroli</span>
                </a>
            </div>
        </div>

        <!-- 10. PODPORA -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start gap-3">
                <span class="text-3xl">💬</span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                        Potrebujete pomoč?
                    </h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Za dodatna vprašanja, težave ali predloge se obrnite na administratorja sistema.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
