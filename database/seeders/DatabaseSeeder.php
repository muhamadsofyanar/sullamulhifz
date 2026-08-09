<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductionSeeder::class,
            PlatformFoundationV210Seeder::class,
            AcademyExpansionV220Seeder::class,
            IntegratedLearningEcosystemV230Seeder::class,
            TahfizhLearningEngineV250Seeder::class,
            QuranJourneyV260Seeder::class,
            CommunicationV410Seeder::class,
        ]);
    }
}
