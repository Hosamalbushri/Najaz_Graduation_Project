<?php

namespace Webkul\Installer\Database\Seeders\Attribute;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttributeFamilyTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        // Attribute module is disabled, skip this seeder
        if (! Schema::hasTable('attribute_families')) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('attribute_families')->delete();

        $defaultLocale = $parameters['default_locale'] ?? config('app.locale');

        DB::table('attribute_families')->insert([
            [
                'id'              => 1,
                'code'            => 'default',
                'name'            => trans('installer::app.seeders.attribute.attribute-families.default', [], $defaultLocale),
                'status'          => 0,
                'is_user_defined' => 1,
            ],
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
