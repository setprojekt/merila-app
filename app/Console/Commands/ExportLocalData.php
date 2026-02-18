<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportLocalData extends Command
{
    protected $signature = 'db:export-data
                            {--output= : Pot do izhodne JSON datoteke}
                            {--include-activity : Vključi activity_log}
                            {--include-notifications : Vključi notifications}';

    protected $description = 'Izvozi vnešene podatke iz lokalne baze v JSON (za uvoz na produkcijo)';

    /** @var array<int, string> Redosled tabel glede na odvisnosti (FK) */
    private array $tables = [
        'users',
        'instruments',
        'delivery_notes',
        'delivery_note_items',
        'settings',
    ];

    public function handle(): int
    {
        $this->info('Izvažanje podatkov iz lokalne baze …');

        $includeActivity = $this->option('include-activity');
        $includeNotifications = $this->option('include-notifications');

        $export = [
            'exported_at' => now()->toIso8601String(),
            'tables'      => [],
        ];

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                $this->warn("Tabela {$table} ne obstaja, preskočeno.");
                continue;
            }

            $rows = DB::table($table)->orderBy('id')->get();
            $data = $rows->map(fn ($row) => (array) $row)->values()->all();
            $export['tables'][$table] = $data;
            $this->line("  {$table}: " . count($data) . ' vrstic');
        }

        if ($includeActivity && Schema::hasTable('activity_log')) {
            $rows = DB::table('activity_log')->orderBy('id')->get();
            $export['tables']['activity_log'] = $rows->map(fn ($r) => (array) $r)->values()->all();
            $this->line('  activity_log: ' . count($export['tables']['activity_log']) . ' vrstic');
        }

        if ($includeNotifications && Schema::hasTable('notifications')) {
            $rows = DB::table('notifications')->orderBy('created_at')->get();
            $export['tables']['notifications'] = $rows->map(fn ($r) => (array) $r)->values()->all();
            $this->line('  notifications: ' . count($export['tables']['notifications']) . ' vrstic');
        }

        $path = $this->option('output')
            ?? storage_path('app/merila-data-export-' . now()->format('Y-m-d-His') . '.json');

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $json = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $this->error('Napaka pri kodiranju JSON.');
            return self::FAILURE;
        }

        file_put_contents($path, $json);
        $this->info('Izvoženo v: ' . $path);

        return self::SUCCESS;
    }
}
