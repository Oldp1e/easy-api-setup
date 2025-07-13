<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Categories Seeder
 * 
 * Creates sample categories for demonstration
 */
final class CategoriesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $categories = [
            // Root categories
            [
                'id' => 1,
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'All things related to technology and innovation',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'fas fa-laptop', 'color' => '#007bff']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Business strategies, entrepreneurship, and market insights',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'fas fa-briefcase', 'color' => '#28a745']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'Lifestyle',
                'slug' => 'lifestyle',
                'description' => 'Health, wellness, and lifestyle content',
                'parent_id' => null,
                'sort_order' => 3,
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'fas fa-heart', 'color' => '#dc3545']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            
            // Sub-categories for Technology
            [
                'id' => 4,
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Frontend, backend, and full-stack development',
                'parent_id' => 1,
                'sort_order' => 1,
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'fas fa-code', 'color' => '#6f42c1']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 5,
                'name' => 'Mobile Apps',
                'slug' => 'mobile-apps',
                'description' => 'iOS, Android, and cross-platform development',
                'parent_id' => 1,
                'sort_order' => 2,
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'fas fa-mobile-alt', 'color' => '#20c997']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 6,
                'name' => 'Artificial Intelligence',
                'slug' => 'artificial-intelligence',
                'description' => 'AI, machine learning, and data science',
                'parent_id' => 1,
                'sort_order' => 3,
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'fas fa-robot', 'color' => '#fd7e14']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            
            // Sub-categories for Business
            [
                'id' => 7,
                'name' => 'Startups',
                'slug' => 'startups',
                'description' => 'Startup culture, funding, and growth strategies',
                'parent_id' => 2,
                'sort_order' => 1,
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'fas fa-rocket', 'color' => '#e83e8c']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 8,
                'name' => 'Marketing',
                'slug' => 'marketing',
                'description' => 'Digital marketing, SEO, and brand building',
                'parent_id' => 2,
                'sort_order' => 2,
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'fas fa-bullhorn', 'color' => '#17a2b8']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->table('categories')->insert($categories)->saveData();

        $this->output->writeln('<info>Categories seeded successfully:</info>');
        $this->output->writeln('<comment>Root Categories:</comment>');
        $this->output->writeln('- Technology (with 3 subcategories)');
        $this->output->writeln('- Business (with 2 subcategories)');
        $this->output->writeln('- Lifestyle');
    }
}
