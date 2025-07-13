<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateItemsTable extends AbstractMigration
{
    /**
     * Create items table for generic items/products/content
     */
    public function change(): void
    {
        $this->table('items', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('title', 'string', ['limit' => 255])
            ->addColumn('slug', 'string', ['limit' => 255])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('content', 'longtext', ['null' => true])
            ->addColumn('type', 'string', ['limit' => 50, 'default' => 'general'])
            ->addColumn('status', 'enum', ['values' => ['draft', 'published', 'archived'], 'default' => 'draft'])
            ->addColumn('category_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('featured_image', 'string', ['null' => true, 'limit' => 500])
            ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
            ->addColumn('metadata', 'json', ['null' => true, 'comment' => 'Additional item data'])
            ->addColumn('view_count', 'integer', ['default' => 0])
            ->addColumn('is_featured', 'boolean', ['default' => false])
            ->addColumn('published_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['type'])
            ->addIndex(['status'])
            ->addIndex(['category_id'])
            ->addIndex(['user_id'])
            ->addIndex(['is_featured'])
            ->addIndex(['published_at'])
            ->addForeignKey('category_id', 'categories', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('user_id', 'users', 'id_user_pk', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
