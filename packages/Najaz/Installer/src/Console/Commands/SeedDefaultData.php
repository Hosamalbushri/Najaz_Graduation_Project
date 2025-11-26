<?php

namespace Najaz\Installer\Console\Commands;

use Illuminate\Console\Command;
use Najaz\Installer\Database\Seeders\DatabaseSeeder;

class SeedDefaultData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'najaz:seed-defaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Najaz default data (citizen types and attribute types)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Seeding Najaz default data...');

        $defaultLocale = config('app.locale');
        $allowedLocales = config('app.allowed_locales', [$defaultLocale]);

        $parameters = [
            'default_locale' => $defaultLocale,
            'allowed_locales' => is_array($allowedLocales) ? $allowedLocales : [$defaultLocale],
        ];

        $seeder = new DatabaseSeeder();
        $seeder->setCommand($this);
        $seeder->run($parameters);

        $this->info('Najaz default data seeded successfully!');

        return Command::SUCCESS;
    }
}

