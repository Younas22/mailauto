<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL',    'admin@mailauto.com');
        $name     = env('ADMIN_NAME',     'Admin');
        $password = env('ADMIN_PASSWORD', 'password');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'role'     => 'admin',
                'password' => Hash::make($password),
            ]
        );

        // Ensure existing users seeded before role column existed have admin role
        if ($user->wasRecentlyCreated === false && $user->role !== 'admin') {
            $user->update(['role' => 'admin']);
        }

        $this->command->info("Admin user ready: {$email}");
    }
}
