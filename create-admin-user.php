<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::firstOrCreate(
    ['email' => 'admin@example.com'],
    [
        'name' => 'Admin',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
    ]
);

echo "User created/updated: {$user->email}\n";
echo "Password: password\n";
