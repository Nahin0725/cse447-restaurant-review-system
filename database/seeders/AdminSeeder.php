<?php

namespace Database\Seeders;

use App\Models\AdminLimit;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'username' => 'admin',
            'email' => 'nahin.afrin@g.bracu.ac.bd',
            'contact_info' => '01234567890',
            'password' => 'AdminPassword123',
            'role' => 'admin',
        ]);

        // Create admin limit record
        AdminLimit::create([
            'admin_id' => $admin->user_id,
            'current_pending_count' => 0,
            'max_pending_limit' => 5,
            'status' => 'active',
        ]);

        // Create user activity record for admin
        UserActivity::create([
            'user_id' => $admin->user_id,
            'remaining_reviews' => 5,
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: nahin.afrin@g.bracu.ac.bd');
        $this->command->info('Password: AdminPassword123');
    }
}
