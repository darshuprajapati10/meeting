<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $faker = Faker::create();
        $types = ['business', 'individual'];
        $gstStatuses = ['registered', 'unregistered'];
        $cities = ['Mumbai', 'Delhi', 'Bangalore', 'Pune', 'Hyderabad', 'Chennai', 'Kolkata', 'Ahmedabad'];
        
        // Create 20 organizations
        for ($i = 1; $i <= 20; $i++) {
            $type = $faker->randomElement($types);
            $gstStatus = $faker->randomElement($gstStatuses);
            $city = $faker->randomElement($cities);
            $name = $faker->company();
            
            $organization = Organization::create([
                'name' => $name,
                'slug' => Str::slug($name . '-' . $i),
                'description' => $faker->sentence(10),
                'type' => $type,
                'email' => $faker->unique()->companyEmail(),
                'phone' => $faker->phoneNumber(),
                'address' => $faker->address(),
                'gst_status' => $gstStatus,
                'gst_in' => $gstStatus === 'registered' ? $faker->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}') : null,
                'place_of_supply' => $city,
                'shipping_address' => $faker->address(),
                'shipping_city' => $city,
                'shipping_zip' => $faker->postcode(),
                'shipping_phone' => $faker->phoneNumber(),
                'billing_address' => $faker->address(),
                'billing_city' => $city,
                'billing_zip' => $faker->postcode(),
                'billing_phone' => $faker->phoneNumber(),
                'status' => 'active',
            ]);

            // Attach random user(s) to organization
            $randomUser = $users->random();
            if (!$randomUser->organizations()->where('organizations.id', $organization->id)->exists()) {
                $role = $faker->randomElement(['admin', 'member']);
                $randomUser->organizations()->attach($organization->id, ['role' => $role]);
            }
        }
    }
}
