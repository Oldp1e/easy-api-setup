<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Tags Seeder
 * 
 * Creates sample tags for content tagging
 */
final class TagsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $tags = [
            // Technology tags
            ['id' => 1, 'name' => 'PHP', 'slug' => 'php', 'description' => 'PHP programming language', 'color' => '#777bb4'],
            ['id' => 2, 'name' => 'JavaScript', 'slug' => 'javascript', 'description' => 'JavaScript programming language', 'color' => '#f7df1e'],
            ['id' => 3, 'name' => 'React', 'slug' => 'react', 'description' => 'React.js library', 'color' => '#61dafb'],
            ['id' => 4, 'name' => 'Vue.js', 'slug' => 'vuejs', 'description' => 'Vue.js framework', 'color' => '#4fc08d'],
            ['id' => 5, 'name' => 'Laravel', 'slug' => 'laravel', 'description' => 'Laravel PHP framework', 'color' => '#ff2d20'],
            ['id' => 6, 'name' => 'API', 'slug' => 'api', 'description' => 'Application Programming Interface', 'color' => '#0066cc'],
            ['id' => 7, 'name' => 'REST', 'slug' => 'rest', 'description' => 'RESTful web services', 'color' => '#009688'],
            ['id' => 8, 'name' => 'Docker', 'slug' => 'docker', 'description' => 'Containerization platform', 'color' => '#2496ed'],
            ['id' => 9, 'name' => 'MySQL', 'slug' => 'mysql', 'description' => 'MySQL database', 'color' => '#4479a1'],
            ['id' => 10, 'name' => 'MongoDB', 'slug' => 'mongodb', 'description' => 'MongoDB NoSQL database', 'color' => '#47a248'],
            
            // Business tags
            ['id' => 11, 'name' => 'Entrepreneurship', 'slug' => 'entrepreneurship', 'description' => 'Starting and running businesses', 'color' => '#ff6b35'],
            ['id' => 12, 'name' => 'Innovation', 'slug' => 'innovation', 'description' => 'New ideas and creative solutions', 'color' => '#8b5cf6'],
            ['id' => 13, 'name' => 'Leadership', 'slug' => 'leadership', 'description' => 'Management and leadership skills', 'color' => '#059669'],
            ['id' => 14, 'name' => 'Strategy', 'slug' => 'strategy', 'description' => 'Business strategy and planning', 'color' => '#dc2626'],
            ['id' => 15, 'name' => 'Growth', 'slug' => 'growth', 'description' => 'Business growth and scaling', 'color' => '#16a34a'],
            
            // General tags
            ['id' => 16, 'name' => 'Tutorial', 'slug' => 'tutorial', 'description' => 'Step-by-step guides', 'color' => '#3b82f6'],
            ['id' => 17, 'name' => 'Best Practices', 'slug' => 'best-practices', 'description' => 'Recommended approaches', 'color' => '#10b981'],
            ['id' => 18, 'name' => 'Tips', 'slug' => 'tips', 'description' => 'Quick tips and tricks', 'color' => '#f59e0b'],
            ['id' => 19, 'name' => 'Guide', 'slug' => 'guide', 'description' => 'Comprehensive guides', 'color' => '#6366f1'],
            ['id' => 20, 'name' => 'News', 'slug' => 'news', 'description' => 'Latest news and updates', 'color' => '#ef4444']
        ];

        // Add timestamps and usage_count
        foreach ($tags as &$tag) {
            $tag['usage_count'] = 0;
            $tag['created_at'] = date('Y-m-d H:i:s');
            $tag['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->table('tags')->insert($tags)->saveData();

        $this->output->writeln('<info>Tags seeded successfully:</info>');
        $this->output->writeln('<comment>Technology tags:</comment> PHP, JavaScript, React, Vue.js, Laravel, API, REST, Docker, MySQL, MongoDB');
        $this->output->writeln('<comment>Business tags:</comment> Entrepreneurship, Innovation, Leadership, Strategy, Growth');
        $this->output->writeln('<comment>General tags:</comment> Tutorial, Best Practices, Tips, Guide, News');
    }
}
