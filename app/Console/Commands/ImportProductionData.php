<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportProductionData extends Command
{
    protected $signature = 'db:import-data
                            {file : Pot do JSON datoteke (iz db:export-data)}
                            {--force : Brez potrditve}
                            {--truncate : Izprazni tabele pred uvozom (obvezno če že vsebujejo podatke)}';

    protected $description = 'Uvozi podatke iz JSON v produkcijsko bazo (po izvozu z db:export-data)';

    private const TABLES_ORDER = [
        'users',
        'instruments',
        'delivery_notes',
        'delivery_note_items',
        'settings',
        'activity_log',
        'notifications',
    ];

    private const TABLES_WITH_AUTO_INCREMENT = [
        'users',
        'instruments',
        'delivery_notes',
        'delivery_note_items',
        'settings',
        'activity_log',
    ];

    private const TABLES_TO_TRUNCATE = [
        'users',
        'instruments',
        'delivery_notes',
        'delivery_note_items',
        'settings',
        'activity_log',
        'notifications',
    ];

    public function handle(): int
    {
        $path = $this->argument('file');
        if (! is_file($path)) {
            $this->error("Datoteka ne obstaja: {$path}");
            return self::FAILURE;
        }

        $raw = file_get_contents($path);
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Neveljavna JSON datoteka: ' . json_last_error_msg());
            return self::FAILURE;
        }

        if (empty($data['tables']) || ! is_array($data['tables'])) {
            $this->error('JSON nima vsebine "tables".');
            return self::FAILURE;
        }

        $driver = DB::getDriverName();
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            $this->warn('Uvoz je preverjen za MySQL/MariaDB. Druge gonilnike uporabljate na lastno odgovornost.');
        }

        $truncate = $this->option('truncate');
        if ($truncate && ! $this->option('force')) {
            if (! $this->confirm('Izprazniti navedene tabele pred uvozom?', false)) {
                return self::SUCCESS;
            }
        }

        if (! $truncate && ! $this->option('force')) {
            $this->warn('Tabele ne bodo izpraznjene. Zagotovite, da so prazne (npr. migrate:fresh) ali uporabite --truncate.');
            if (! $this->confirm('Nadaljujem z uvozom?', true)) {
                return self::SUCCESS;
            }
        }

        $this->info('Uvažanje podatkov …');

        $useFkDisable = in_array($driver, ['mysql', 'mariadb'], true);

        if ($useFkDisable) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        }

        try {
            foreach (self::TABLES_ORDER as $table) {
                $rows = $data['tables'][$table] ?? null;
                if ($rows === null || ! is_array($rows)) {
                    continue;
                }

                if (! Schema::hasTable($table)) {
                    $this->warn("Tabela {$table} ne obstaja, preskočeno.");
                    continue;
                }

                if ($truncate && in_array($table, self::TABLES_TO_TRUNCATE, true)) {
                    DB::table($table)->truncate();
                }

                $chunkSize = 100;
                $chunks = array_chunk($rows, $chunkSize);
                $total = count($rows);

                foreach ($chunks as $chunk) {
                    $normalized = $this->normalizeRows($table, $chunk);
                    DB::table($table)->insert($normalized);
                }

                $this->line("  {$table}: {$total} vrstic");

                if (in_array($table, self::TABLES_WITH_AUTO_INCREMENT, true) && $total > 0) {
                    $max = (int) collect($rows)->max('id');
                    if ($max > 0) {
                        DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = " . ($max + 1));
                    }
                }
            }
        } finally {
            if ($useFkDisable) {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }
        }

        $this->info('Uvoz končan.');

        return self::SUCCESS;
    }

    /**
     * Prilagodi vrstice za vstavljanje (npr. odstrani null vrednosti za neobvezne stolpce).
     */
    private function normalizeRows(string $table, array $rows): array
    {
        $cols = Schema::getColumnListing($table);
        $out = [];

        foreach ($rows as $row) {
            $filtered = [];
            foreach ($row as $key => $value) {
                if (! in_array($key, $cols, true)) {
                    continue;
                }
                $filtered[$key] = $value;
            }
            $out[] = $filtered;
        }

        return $out;
    }
}
