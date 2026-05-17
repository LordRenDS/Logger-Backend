<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PcStatusSeeder::class,
        ]);

        $adminName = config('app.admin.name');
        $adminEmail = config('app.admin.email');
        $adminPassword = config('app.admin.password');

        if ($adminName && $adminEmail && $adminPassword) {
            User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => Hash::make($adminPassword),
                    'role' => 'admin',
                ]
            );
        }
    }
}
