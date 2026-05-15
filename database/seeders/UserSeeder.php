<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name'  => 'Roberto Medina',
            'email' => 'roberto@balanza.test',
            'role'  => 'operador',
        ]);

        User::factory()->create([
            'name'  => 'Nacho García',
            'email' => 'nacho@balanza.test',
            'role'  => 'admin',
        ]);

        User::factory()->create([
            'name'  => 'Nicolas Fernandez',
            'email' => 'nfernandez@evolvere.com.ar',
            'role'  => 'admin',
        ]);

        User::factory()->create([
            'name'  => 'Nicolas Fernandez',
            'email' => 'nfernandez+1@evolvere.com.ar',
            'role'  => 'operador',
        ]);
    }
}
