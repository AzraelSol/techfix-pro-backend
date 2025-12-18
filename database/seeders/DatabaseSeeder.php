<?php

namespace Database\Seeders;

use App\Models\HardwareType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call user seeder first
        $this->call([
            UserSeeder::class,
        ]);

        // Create hardware types
        $hardwareTypes = [
            [
                'name' => 'Smartphone',
                'slug' => 'smartphone',
                'description' => 'Mobile phone repairs including screen replacement, battery replacement, charging port issues, and software problems.',
                'icon' => 'smartphone',
            ],
            [
                'name' => 'Laptop',
                'slug' => 'laptop',
                'description' => 'Laptop repairs including screen repair, keyboard replacement, battery issues, overheating, and hardware upgrades.',
                'icon' => 'laptop',
            ],
            [
                'name' => 'Desktop Computer',
                'slug' => 'desktop',
                'description' => 'Desktop PC repairs including hardware troubleshooting, component replacement, and system optimization.',
                'icon' => 'desktop',
            ],
            [
                'name' => 'Tablet',
                'slug' => 'tablet',
                'description' => 'Tablet repairs including screen replacement, battery issues, charging problems, and software fixes.',
                'icon' => 'tablet',
            ],
            [
                'name' => 'Gaming Console',
                'slug' => 'gaming-console',
                'description' => 'Gaming console repairs for PlayStation, Xbox, Nintendo Switch, and other gaming devices.',
                'icon' => 'gamepad',
            ],
            [
                'name' => 'Printer',
                'slug' => 'printer',
                'description' => 'Printer repairs including paper jam issues, print quality problems, and connectivity issues.',
                'icon' => 'printer',
            ],
            [
                'name' => 'Monitor',
                'slug' => 'monitor',
                'description' => 'Monitor repairs including display issues, backlight problems, and connectivity troubleshooting.',
                'icon' => 'monitor',
            ],
            [
                'name' => 'Other Hardware',
                'slug' => 'other',
                'description' => 'Repairs for other electronic devices and hardware not listed above.',
                'icon' => 'cpu',
            ],
        ];

        foreach ($hardwareTypes as $type) {
            HardwareType::create($type);
        }

        // Call booking seeder after hardware types are created
        $this->call([
            BookingSeeder::class,
        ]);
    }
}
