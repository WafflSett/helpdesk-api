<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()
            ->count(5)
            ->has(Ticket::factory()->count(2))
            ->create();

        User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'a@a.c',
            'password'=>'1234',
            'role'=>'admin'
        ]);
    }
}
