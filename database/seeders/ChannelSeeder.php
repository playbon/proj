<?php

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    public function run(): void
    {
        Channel::create([
            'name' => 'Stable',
            'slug' => 'stable',
            'description' => 'Production-ready releases',
            'is_default' => true,
        ]);

        Channel::create([
            'name' => 'Beta',
            'slug' => 'beta',
            'description' => 'Pre-release testing versions',
            'is_default' => false,
        ]);

        Channel::create([
            'name' => 'Nightly',
            'slug' => 'nightly',
            'description' => 'Development builds',
            'is_default' => false,
        ]);
    }
}
