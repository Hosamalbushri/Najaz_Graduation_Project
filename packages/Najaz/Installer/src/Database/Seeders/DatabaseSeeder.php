<?php

namespace Najaz\Installer\Database\Seeders;

use Illuminate\Database\Seeder;
use Najaz\Installer\Database\Seeders\Citizen\CitizenTypeTableSeeder;
use Najaz\Installer\Database\Seeders\Core\DatabaseSeeder as CoreSeeder;
use Najaz\Installer\Database\Seeders\Service\AttributeGroupTableSeeder;
use Najaz\Installer\Database\Seeders\Service\CitizenAttributeTypeTableSeeder;
use Najaz\Installer\Database\Seeders\Service\ServiceCategoryTableSeeder;
use Najaz\Installer\Database\Seeders\SocialLogin\DatabaseSeeder as SocialLoginSeeder;
use Najaz\Installer\Database\Seeders\User\DatabaseSeeder as UserSeeder;

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
        // Core seeders from original package
        $this->call(CoreSeeder::class, false, ['parameters' => $parameters]);
        $this->call(SocialLoginSeeder::class, false, ['parameters' => $parameters]);
        $this->call(UserSeeder::class, false, ['parameters' => $parameters]);

        // Najaz custom seeders
        $this->call(CitizenTypeTableSeeder::class, false, ['parameters' => $parameters]);
        $this->call(ServiceCategoryTableSeeder::class, false, ['parameters' => $parameters]);
        $this->call(CitizenAttributeTypeTableSeeder::class, false, ['parameters' => $parameters]);
        $this->call(AttributeGroupTableSeeder::class, false, ['parameters' => $parameters]);
    }
}
