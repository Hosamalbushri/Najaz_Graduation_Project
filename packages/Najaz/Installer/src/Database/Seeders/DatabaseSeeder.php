<?php

namespace Najaz\Installer\Database\Seeders;

use Illuminate\Database\Seeder;
use Najaz\Installer\Database\Seeders\Citizen\CitizenTypeTableSeeder;
use Najaz\Installer\Database\Seeders\Service\CitizenAttributeTypeTableSeeder;
use Najaz\Installer\Database\Seeders\Service\AttributeGroupTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        $this->call(CitizenTypeTableSeeder::class, false, ['parameters' => $parameters]);
        $this->call(CitizenAttributeTypeTableSeeder::class, false, ['parameters' => $parameters]);
        $this->call(AttributeGroupTableSeeder::class, false, ['parameters' => $parameters]);
    }
}

