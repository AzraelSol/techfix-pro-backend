<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\HardwareType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users and incharges
        $users = User::where('user_type', 'user')->get();
        $incharges = User::where('user_type', 'incharge')->get();
        $hardwareTypes = HardwareType::all();

        if ($users->isEmpty() || $hardwareTypes->isEmpty()) {
            $this->command->warn('No users or hardware types found. Please run UserSeeder and DatabaseSeeder first.');
            return;
        }

        // Sample booking data
        $bookings = [
            // Pending bookings
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'smartphone')->first()?->id ?? $hardwareTypes->first()->id,
                'device_brand' => 'Apple',
                'device_model' => 'iPhone 14 Pro',
                'serial_number' => 'DNPX7F8KQ1',
                'issue_description' => 'Screen is cracked and not responding to touch. Need urgent replacement.',
                'priority' => 'high',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'laptop')->first()?->id ?? $hardwareTypes->first()->id,
                'device_brand' => 'Dell',
                'device_model' => 'XPS 15 9520',
                'serial_number' => 'DL9520X7891',
                'issue_description' => 'Laptop overheating and shutting down randomly. Fan making loud noise.',
                'priority' => 'medium',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(12),
            ],
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'tablet')->first()?->id ?? $hardwareTypes->first()->id,
                'device_brand' => 'Samsung',
                'device_model' => 'Galaxy Tab S8',
                'serial_number' => 'SGT8S456123',
                'issue_description' => 'Battery draining very fast, only lasts 2 hours. Charging port also seems loose.',
                'priority' => 'low',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(6),
            ],

            // Assigned bookings
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'desktop')->first()?->id ?? $hardwareTypes->first()->id,
                'incharge_id' => $incharges->random()->id,
                'device_brand' => 'HP',
                'device_model' => 'Pavilion Desktop',
                'serial_number' => 'HP789456123',
                'issue_description' => 'Computer won\'t boot up. Power button lights up but nothing on screen.',
                'priority' => 'high',
                'status' => 'assigned',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'smartphone')->first()?->id ?? $hardwareTypes->first()->id,
                'incharge_id' => $incharges->random()->id,
                'device_brand' => 'Samsung',
                'device_model' => 'Galaxy S23 Ultra',
                'serial_number' => 'SGS23U789456',
                'issue_description' => 'Phone got water damage. Not turning on at all.',
                'priority' => 'urgent',
                'status' => 'assigned',
                'created_at' => Carbon::now()->subDays(1)->subHours(5),
            ],

            // In Progress bookings
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'laptop')->first()?->id ?? $hardwareTypes->first()->id,
                'incharge_id' => $incharges->random()->id,
                'device_brand' => 'Lenovo',
                'device_model' => 'ThinkPad X1 Carbon',
                'serial_number' => 'LNV123456789',
                'issue_description' => 'Keyboard keys are sticking. Some keys not working at all. Need replacement.',
                'priority' => 'medium',
                'status' => 'in_progress',
                'diagnosis' => 'Keyboard needs full replacement. Liquid damage detected under keys.',
                'estimated_cost' => 150.00,
                'estimated_completion_date' => Carbon::now()->addDays(3),
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'gaming-console')->first()?->id ?? $hardwareTypes->first()->id,
                'incharge_id' => $incharges->random()->id,
                'device_brand' => 'Sony',
                'device_model' => 'PlayStation 5',
                'serial_number' => 'PS5123456789',
                'issue_description' => 'Console making disc reading errors. Sometimes games won\'t load.',
                'priority' => 'medium',
                'status' => 'in_progress',
                'diagnosis' => 'Disc drive laser needs cleaning and recalibration.',
                'estimated_cost' => 80.00,
                'estimated_completion_date' => Carbon::now()->addDays(2),
                'created_at' => Carbon::now()->subDays(4),
            ],

            // Waiting for parts
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'monitor')->first()?->id ?? $hardwareTypes->first()->id,
                'incharge_id' => $incharges->random()->id,
                'device_brand' => 'LG',
                'device_model' => 'UltraGear 27GP950',
                'serial_number' => 'LG27GP950123',
                'issue_description' => 'Monitor has dead pixels and backlight bleeding on the left side.',
                'priority' => 'low',
                'status' => 'waiting_parts',
                'diagnosis' => 'Panel replacement required. Ordering replacement panel.',
                'estimated_cost' => 350.00,
                'estimated_completion_date' => Carbon::now()->addDays(7),
                'incharge_notes' => 'Replacement panel ordered from supplier. Expected arrival in 5 days.',
                'created_at' => Carbon::now()->subDays(5),
            ],

            // Completed bookings
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'smartphone')->first()?->id ?? $hardwareTypes->first()->id,
                'incharge_id' => $incharges->random()->id,
                'device_brand' => 'Google',
                'device_model' => 'Pixel 7 Pro',
                'serial_number' => 'GP7P456123789',
                'issue_description' => 'Camera not focusing properly. Photos are blurry.',
                'priority' => 'medium',
                'status' => 'completed',
                'diagnosis' => 'Camera module was loose. Reseated and cleaned the module.',
                'estimated_cost' => 50.00,
                'final_cost' => 45.00,
                'completed_at' => Carbon::now()->subDays(1),
                'incharge_notes' => 'Camera module reseated successfully. Tested all camera functions.',
                'created_at' => Carbon::now()->subDays(6),
            ],
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'printer')->first()?->id ?? $hardwareTypes->first()->id,
                'incharge_id' => $incharges->random()->id,
                'device_brand' => 'HP',
                'device_model' => 'LaserJet Pro M404dn',
                'serial_number' => 'HPM404DN789',
                'issue_description' => 'Printer jamming paper frequently. Print quality degraded.',
                'priority' => 'low',
                'status' => 'completed',
                'diagnosis' => 'Rollers were worn out and needed replacement. Toner was also low.',
                'estimated_cost' => 120.00,
                'final_cost' => 135.00,
                'completed_at' => Carbon::now()->subDays(2),
                'incharge_notes' => 'Replaced paper rollers and cleaned print heads. Installed new toner.',
                'created_at' => Carbon::now()->subDays(8),
            ],

            // Picked up bookings
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'laptop')->first()?->id ?? $hardwareTypes->first()->id,
                'incharge_id' => $incharges->random()->id,
                'device_brand' => 'ASUS',
                'device_model' => 'ROG Zephyrus G14',
                'serial_number' => 'ASROG14789456',
                'issue_description' => 'Gaming laptop running very slow. Takes forever to boot up.',
                'priority' => 'medium',
                'status' => 'picked_up',
                'diagnosis' => 'SSD was failing. Replaced with new NVMe SSD and reinstalled OS.',
                'estimated_cost' => 200.00,
                'final_cost' => 220.00,
                'completed_at' => Carbon::now()->subDays(5),
                'picked_up_at' => Carbon::now()->subDays(3),
                'incharge_notes' => 'Data backup done. New SSD installed. Fresh Windows 11 install.',
                'created_at' => Carbon::now()->subDays(10),
            ],

            // Cancelled booking
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'smartphone')->first()?->id ?? $hardwareTypes->first()->id,
                'device_brand' => 'OnePlus',
                'device_model' => '11 Pro',
                'serial_number' => 'OP11P123456',
                'issue_description' => 'Phone screen flickering occasionally.',
                'priority' => 'low',
                'status' => 'cancelled',
                'admin_notes' => 'Customer cancelled - decided to buy a new phone instead.',
                'created_at' => Carbon::now()->subDays(7),
            ],

            // More pending for testing
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'desktop')->first()?->id ?? $hardwareTypes->first()->id,
                'device_brand' => 'Custom Build',
                'device_model' => 'Gaming PC RTX 4080',
                'serial_number' => null,
                'issue_description' => 'PC blue screening randomly. Might be RAM or GPU issue.',
                'priority' => 'urgent',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'user_id' => $users->random()->id,
                'hardware_type_id' => $hardwareTypes->where('slug', 'other')->first()?->id ?? $hardwareTypes->first()->id,
                'device_brand' => 'Bose',
                'device_model' => 'QuietComfort 45',
                'serial_number' => 'BOSE45789123',
                'issue_description' => 'Headphones not pairing via Bluetooth. Works with cable only.',
                'priority' => 'low',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(1),
            ],
        ];

        foreach ($bookings as $bookingData) {
            Booking::create($bookingData);
        }

        $this->command->info('Created ' . count($bookings) . ' dummy bookings.');
    }
}

