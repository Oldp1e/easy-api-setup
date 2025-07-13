<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * User Seeder
 * 
 * Creates default users for the application.
 * Modify this seeder according to your needs.
 */
final class UserSeeder extends AbstractSeed
{
    public function run(): void
    {
        // Clear existing data
        $this->table('users')->truncate();

        // Default admin user
        $adminPassword = password_hash('admin123!', PASSWORD_DEFAULT);
        
        // Default regular user
        $userPassword = password_hash('user123!', PASSWORD_DEFAULT);

        $users = [
            [
                'username' => 'admin',
                'mail' => 'admin@example.com',
                'password' => $adminPassword,
                'mobile_phone' => '+1234567890',
                'permission_level' => 1, // Admin level
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'demo_user',
                'mail' => 'user@example.com',
                'password' => $userPassword,
                'mobile_phone' => '+1234567891',
                'permission_level' => 0, // Regular user
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'test_user',
                'mail' => 'test@example.com',
                'password' => $userPassword,
                'mobile_phone' => '+1234567892',
                'permission_level' => 0, // Regular user
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->table('users')->insert($users)->saveData();

        // Output seeding information
        $this->output->writeln('<info>Default users created:</info>');
        $this->output->writeln('<comment>Admin User:</comment> admin@example.com / admin123!');
        $this->output->writeln('<comment>Demo User:</comment> user@example.com / user123!');
        $this->output->writeln('<comment>Test User:</comment> test@example.com / user123!');
        $this->output->writeln('');
        $this->output->writeln('<fg=yellow>Remember to change these passwords in production!</>');
    }
}
