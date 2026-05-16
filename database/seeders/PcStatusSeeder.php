<?php

namespace Database\Seeders;

use App\Models\PcStatus;
use Illuminate\Database\Seeder;

class PcStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PcStatus::firstOrCreate(['status' => 'on']);
        PcStatus::firstOrCreate(['status' => 'off']);
    }
}
