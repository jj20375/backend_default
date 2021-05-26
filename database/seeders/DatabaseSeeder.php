<?php

namespace Database\Seeders;

use App\Services\User\UserSystemService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(UserSystemSeeder::class);
        $this->call(GroupSeeder::class);
        $this->call(PermissionDefaultSeeder::class);
        $this->call(UserPermissionGroupSeeder::class);
    }
}
