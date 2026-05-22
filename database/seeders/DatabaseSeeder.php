<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        match (app()->environment()) {
            'local'       => $this->call([DevSeeder::class]),
            // 'staging'  => $this->call([StagingSeeder::class]),
            // 'production'=> $this->call([ProductionSeeder::class]),
            default       => null,
        };
    }
}