<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed data awal aplikasi Portal PPID.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            InformasiPublikSeeder::class,
            FaqSeeder::class,
            PermohonanSeeder::class,
        ]);
    }
}
