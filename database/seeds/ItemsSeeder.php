<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Items Seeder
 * 
 * Creates sample items with different content types
 */
final class ItemsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $items = [
            // Blog Posts
            [
                'id' => 1,
                'title' => 'Getting Started with REST APIs',
                'slug' => 'getting-started-with-rest-apis',
                'description' => 'A comprehensive guide to understanding and building REST APIs',
                'content' => '{"type": "article", "excerpt": "Learn the fundamentals of REST API design and implementation", "reading_time": 15, "difficulty": "beginner"}',
                'type' => 'blog_post',
                'status' => 'published',
                'featured' => true,
                'priority' => 1,
                'category_id' => 1, // Technology
                'created_by' => 1
            ],
            [
                'id' => 2,
                'title' => 'Advanced PHP Techniques',
                'slug' => 'advanced-php-techniques',
                'description' => 'Modern PHP development practices and advanced techniques',
                'content' => '{"type": "article", "excerpt": "Explore advanced PHP features and best practices", "reading_time": 20, "difficulty": "advanced"}',
                'type' => 'blog_post',
                'status' => 'published',
                'featured' => false,
                'priority' => 2,
                'category_id' => 1, // Technology
                'created_by' => 1
            ],
            [
                'id' => 3,
                'title' => 'Building Scalable Business Models',
                'slug' => 'building-scalable-business-models',
                'description' => 'Strategies for creating sustainable and scalable business models',
                'content' => '{"type": "article", "excerpt": "Learn how to design business models that scale", "reading_time": 12, "difficulty": "intermediate"}',
                'type' => 'blog_post',
                'status' => 'published',
                'featured' => true,
                'priority' => 1,
                'category_id' => 3, // Business
                'created_by' => 1
            ],
            
            // Products
            [
                'id' => 4,
                'title' => 'Premium API Template',
                'slug' => 'premium-api-template',
                'description' => 'Professional REST API template with authentication and documentation',
                'content' => '{"type": "product", "price": 99.99, "currency": "USD", "features": ["JWT Authentication", "Swagger Documentation", "Database Migrations", "Email System"], "downloadable": true}',
                'type' => 'product',
                'status' => 'published',
                'featured' => true,
                'priority' => 1,
                'category_id' => 1, // Technology
                'created_by' => 1
            ],
            [
                'id' => 5,
                'title' => 'Business Strategy Course',
                'slug' => 'business-strategy-course',
                'description' => 'Complete online course on business strategy and planning',
                'content' => '{"type": "course", "price": 199.99, "currency": "USD", "duration": "8 weeks", "lessons": 32, "level": "intermediate", "certificate": true}',
                'type' => 'course',
                'status' => 'published',
                'featured' => true,
                'priority' => 1,
                'category_id' => 3, // Business
                'created_by' => 1
            ],
            
            // Documentation
            [
                'id' => 6,
                'title' => 'API Documentation',
                'slug' => 'api-documentation',
                'description' => 'Complete API documentation and usage examples',
                'content' => '{"type": "documentation", "version": "1.0", "endpoints": 25, "examples": 50, "interactive": true}',
                'type' => 'documentation',
                'status' => 'published',
                'featured' => false,
                'priority' => 3,
                'category_id' => 1, // Technology
                'created_by' => 1
            ],
            
            // Events
            [
                'id' => 7,
                'title' => 'Tech Conference 2024',
                'slug' => 'tech-conference-2024',
                'description' => 'Annual technology conference featuring latest trends and innovations',
                'content' => '{"type": "event", "date": "2024-09-15", "time": "09:00", "location": "Tech Center", "speakers": 12, "capacity": 500, "price": 299.99}',
                'type' => 'event',
                'status' => 'published',
                'featured' => true,
                'priority' => 1,
                'category_id' => 1, // Technology
                'created_by' => 1
            ],
            
            // News
            [
                'id' => 8,
                'title' => 'New PHP 8.3 Features Released',
                'slug' => 'new-php-8-3-features-released',
                'description' => 'Overview of the latest features and improvements in PHP 8.3',
                'content' => '{"type": "news", "source": "PHP Official", "urgency": "medium", "tags": ["release", "update", "features"]}',
                'type' => 'news',
                'status' => 'published',
                'featured' => false,
                'priority' => 2,
                'category_id' => 1, // Technology
                'created_by' => 1
            ],
            
            // Downloads
            [
                'id' => 9,
                'title' => 'Free API Starter Kit',
                'slug' => 'free-api-starter-kit',
                'description' => 'Basic API template to get you started quickly',
                'content' => '{"type": "download", "file_size": "2.5MB", "format": "ZIP", "includes": ["Source Code", "Documentation", "Examples"], "downloads": 1250}',
                'type' => 'download',
                'status' => 'published',
                'featured' => false,
                'priority' => 3,
                'category_id' => 1, // Technology
                'created_by' => 1
            ],
            
            // Services
            [
                'id' => 10,
                'title' => 'Custom API Development',
                'slug' => 'custom-api-development',
                'description' => 'Professional API development services for your business needs',
                'content' => '{"type": "service", "price_range": "500-5000", "currency": "USD", "delivery_time": "2-8 weeks", "revisions": 3, "support": "6 months"}',
                'type' => 'service',
                'status' => 'published',
                'featured' => true,
                'priority' => 1,
                'category_id' => 3, // Business
                'created_by' => 1
            ]
        ];

        // Add timestamps and counters
        foreach ($items as &$item) {
            $item['view_count'] = rand(50, 1000);
            $item['like_count'] = rand(5, 100);
            $item['share_count'] = rand(0, 50);
            $item['created_at'] = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
            $item['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->table('items')->insert($items)->saveData();

        // Insert item_tags relationships
        $itemTags = [
            // Blog Posts
            ['item_id' => 1, 'tag_id' => 6], // API
            ['item_id' => 1, 'tag_id' => 7], // REST
            ['item_id' => 1, 'tag_id' => 16], // Tutorial
            
            ['item_id' => 2, 'tag_id' => 1], // PHP
            ['item_id' => 2, 'tag_id' => 17], // Best Practices
            ['item_id' => 2, 'tag_id' => 19], // Guide
            
            ['item_id' => 3, 'tag_id' => 11], // Entrepreneurship
            ['item_id' => 3, 'tag_id' => 14], // Strategy
            ['item_id' => 3, 'tag_id' => 15], // Growth
            
            // Products
            ['item_id' => 4, 'tag_id' => 1], // PHP
            ['item_id' => 4, 'tag_id' => 6], // API
            ['item_id' => 4, 'tag_id' => 5], // Laravel
            
            ['item_id' => 5, 'tag_id' => 11], // Entrepreneurship
            ['item_id' => 5, 'tag_id' => 13], // Leadership
            ['item_id' => 5, 'tag_id' => 14], // Strategy
            
            // Documentation
            ['item_id' => 6, 'tag_id' => 6], // API
            ['item_id' => 6, 'tag_id' => 19], // Guide
            
            // Events
            ['item_id' => 7, 'tag_id' => 12], // Innovation
            ['item_id' => 7, 'tag_id' => 20], // News
            
            // News
            ['item_id' => 8, 'tag_id' => 1], // PHP
            ['item_id' => 8, 'tag_id' => 20], // News
            
            // Downloads
            ['item_id' => 9, 'tag_id' => 6], // API
            ['item_id' => 9, 'tag_id' => 16], // Tutorial
            
            // Services
            ['item_id' => 10, 'tag_id' => 6], // API
            ['item_id' => 10, 'tag_id' => 1] // PHP
        ];

        $this->table('item_tags')->insert($itemTags)->saveData();

        $this->output->writeln('<info>Items seeded successfully:</info>');
        $this->output->writeln('<comment>Blog Posts:</comment> 3 articles on technology and business');
        $this->output->writeln('<comment>Products:</comment> API template and business course');
        $this->output->writeln('<comment>Other Types:</comment> Documentation, events, news, downloads, services');
        $this->output->writeln('<comment>Item-Tag relationships:</comment> ' . count($itemTags) . ' tag associations created');
    }
}
