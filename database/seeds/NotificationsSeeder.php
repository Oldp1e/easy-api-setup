<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Notifications Seeder
 * 
 * Creates sample notifications for testing the notification system
 */
final class NotificationsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $notifications = [
            // System notifications
            [
                'id' => 1,
                'user_id' => 1,
                'type' => 'system',
                'title' => 'Welcome to the Platform!',
                'message' => 'Thank you for joining our platform. Get started by exploring the features.',
                'data' => '{"action": "welcome", "redirect": "/dashboard", "icon": "welcome"}',
                'read_at' => null,
                'priority' => 'normal'
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'type' => 'system',
                'title' => 'System Maintenance Scheduled',
                'message' => 'System maintenance is scheduled for tonight from 2:00 AM to 4:00 AM.',
                'data' => '{"action": "maintenance", "start_time": "2024-02-15 02:00:00", "end_time": "2024-02-15 04:00:00", "icon": "warning"}',
                'read_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'priority' => 'high'
            ],
            
            // Content notifications
            [
                'id' => 3,
                'user_id' => 1,
                'type' => 'content',
                'title' => 'New Article Published',
                'message' => 'A new article "Getting Started with REST APIs" has been published.',
                'data' => '{"action": "new_content", "content_id": 1, "content_type": "blog_post", "redirect": "/articles/getting-started-with-rest-apis", "icon": "article"}',
                'read_at' => null,
                'priority' => 'normal'
            ],
            [
                'id' => 4,
                'user_id' => 1,
                'type' => 'content',
                'title' => 'Content Approved',
                'message' => 'Your article "Advanced PHP Techniques" has been approved and published.',
                'data' => '{"action": "content_approved", "content_id": 2, "content_type": "blog_post", "redirect": "/articles/advanced-php-techniques", "icon": "success"}',
                'read_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'priority' => 'normal'
            ],
            
            // User notifications
            [
                'id' => 5,
                'user_id' => 1,
                'type' => 'user',
                'title' => 'Profile Update Required',
                'message' => 'Please update your profile information to keep your account secure.',
                'data' => '{"action": "profile_update", "redirect": "/profile", "icon": "user"}',
                'read_at' => null,
                'priority' => 'medium'
            ],
            [
                'id' => 6,
                'user_id' => 1,
                'type' => 'user',
                'title' => 'Password Changed Successfully',
                'message' => 'Your password has been changed successfully. If this wasn\'t you, please contact support.',
                'data' => '{"action": "password_changed", "timestamp": "' . date('Y-m-d H:i:s') . '", "icon": "security"}',
                'read_at' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
                'priority' => 'high'
            ],
            
            // Security notifications
            [
                'id' => 7,
                'user_id' => 1,
                'type' => 'security',
                'title' => 'New Login Detected',
                'message' => 'A new login was detected from Chrome on Windows. Location: New York, US.',
                'data' => '{"action": "new_login", "device": "Chrome on Windows", "location": "New York, US", "ip": "192.168.1.1", "timestamp": "' . date('Y-m-d H:i:s') . '", "icon": "security"}',
                'read_at' => null,
                'priority' => 'high'
            ],
            [
                'id' => 8,
                'user_id' => 1,
                'type' => 'security',
                'title' => 'Account Locked',
                'message' => 'Your account was temporarily locked due to multiple failed login attempts.',
                'data' => '{"action": "account_locked", "unlock_time": "' . date('Y-m-d H:i:s', strtotime('+1 hour')) . '", "attempts": 5, "icon": "lock"}',
                'read_at' => date('Y-m-d H:i:s', strtotime('-45 minutes')),
                'priority' => 'urgent'
            ],
            
            // Marketing notifications
            [
                'id' => 9,
                'user_id' => 1,
                'type' => 'marketing',
                'title' => 'Special Offer: 50% Off Premium Features',
                'message' => 'Upgrade to premium now and get 50% off for the first 3 months!',
                'data' => '{"action": "special_offer", "discount": 50, "duration": "3 months", "code": "PREMIUM50", "redirect": "/upgrade", "icon": "offer"}',
                'read_at' => null,
                'priority' => 'low'
            ],
            [
                'id' => 10,
                'user_id' => 1,
                'type' => 'marketing',
                'title' => 'Weekly Newsletter',
                'message' => 'Check out this week\'s top articles and trending topics in your field.',
                'data' => '{"action": "newsletter", "week": "' . date('W') . '", "articles": 5, "redirect": "/newsletter", "icon": "newsletter"}',
                'read_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'priority' => 'low'
            ],
            
            // Event notifications
            [
                'id' => 11,
                'user_id' => 1,
                'type' => 'event',
                'title' => 'Event Reminder: Tech Conference 2024',
                'message' => 'Don\'t forget! Tech Conference 2024 is tomorrow at 9:00 AM.',
                'data' => '{"action": "event_reminder", "event_id": 7, "event_date": "2024-09-15", "event_time": "09:00", "redirect": "/events/tech-conference-2024", "icon": "calendar"}',
                'read_at' => null,
                'priority' => 'medium'
            ],
            
            // Order/Transaction notifications
            [
                'id' => 12,
                'user_id' => 1,
                'type' => 'order',
                'title' => 'Purchase Successful',
                'message' => 'Your purchase of "Premium API Template" was successful. Download link has been sent to your email.',
                'data' => '{"action": "purchase_success", "product_id": 4, "amount": 99.99, "currency": "USD", "order_id": "ORD-001", "download_link": "/downloads/premium-api-template", "icon": "success"}',
                'read_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'priority' => 'normal'
            ]
        ];

        // Add timestamps
        foreach ($notifications as &$notification) {
            $notification['created_at'] = date('Y-m-d H:i:s', strtotime('-' . rand(0, 7) . ' days -' . rand(0, 23) . ' hours'));
            $notification['updated_at'] = $notification['created_at'];
        }

        $this->table('notifications')->insert($notifications)->saveData();

        $this->output->writeln('<info>Notifications seeded successfully:</info>');
        $this->output->writeln('<comment>System notifications:</comment> Welcome, maintenance alerts');
        $this->output->writeln('<comment>Content notifications:</comment> New articles, content approvals');
        $this->output->writeln('<comment>User notifications:</comment> Profile updates, password changes');
        $this->output->writeln('<comment>Security notifications:</comment> Login alerts, account security');
        $this->output->writeln('<comment>Marketing notifications:</comment> Special offers, newsletters');
        $this->output->writeln('<comment>Event notifications:</comment> Event reminders');
        $this->output->writeln('<comment>Order notifications:</comment> Purchase confirmations');
        $this->output->writeln('<comment>Total:</comment> ' . count($notifications) . ' notifications created');
    }
}
