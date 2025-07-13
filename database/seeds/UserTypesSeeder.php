<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * UserTypes Seeder
 * 
 * Creates default user types for the application
 */
final class UserTypesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $userTypes = [
            [
                'id' => 1,
                'name' => 'Administrator',
                'description' => 'Full system access with all permissions',
                'permissions' => json_encode([
                    'users.create', 'users.read', 'users.update', 'users.delete',
                    'categories.create', 'categories.read', 'categories.update', 'categories.delete',
                    'items.create', 'items.read', 'items.update', 'items.delete',
                    'tags.create', 'tags.read', 'tags.update', 'tags.delete',
                    'notifications.create', 'notifications.read', 'notifications.update', 'notifications.delete',
                    'system.settings', 'system.analytics'
                ]),
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Editor',
                'description' => 'Can manage content and categories',
                'permissions' => json_encode([
                    'categories.read', 'categories.create', 'categories.update',
                    'items.create', 'items.read', 'items.update', 'items.delete',
                    'tags.create', 'tags.read', 'tags.update',
                    'notifications.read'
                ]),
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'Author',
                'description' => 'Can create and manage own content',
                'permissions' => json_encode([
                    'categories.read',
                    'items.create', 'items.read', 'items.update.own',
                    'tags.read', 'tags.create',
                    'notifications.read'
                ]),
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'name' => 'Subscriber',
                'description' => 'Read-only access to public content',
                'permissions' => json_encode([
                    'categories.read',
                    'items.read.published',
                    'tags.read',
                    'notifications.read.own'
                ]),
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 5,
                'name' => 'Guest',
                'description' => 'Temporary or limited access user',
                'permissions' => json_encode([
                    'items.read.published'
                ]),
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->table('user_types')->insert($userTypes)->saveData();

        $this->output->writeln('<info>User types seeded successfully:</info>');
        foreach ($userTypes as $type) {
            $this->output->writeln("<comment>- {$type['name']}</comment>: {$type['description']}");
        }
    }
}
