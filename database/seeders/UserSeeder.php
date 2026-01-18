<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the original user
        if (!User::where('email', 'dhaval48@gmail.com')->exists()) {
            User::create([
                'name' => 'Dhaval',
                'email' => 'dhaval48@gmail.com',
                'password' => Hash::make('Tunafishm@48'),
            ]);
        }

        // if (!User::where('email', 'ramaniparth725@gmail.com')->exists()) {
        //         User::create([
        //             'name' => 'Parth',
        //             'email' => 'ramaniparth725@gmail.com',
        //             'password' => Hash::make('Parth@725'),
        //         ]);
        //     }
    }
}
