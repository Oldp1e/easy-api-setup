<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateItemTagsTable extends AbstractMigration
{
    /**
     * Create item_tags pivot table for many-to-many relationship
     */
    public function change(): void
    {
        $this->table('item_tags', ['id' => false, 'primary_key' => ['item_id', 'tag_id']])
            ->addColumn('item_id', 'integer', ['signed' => false])
            ->addColumn('tag_id', 'integer', ['signed' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('item_id', 'items', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('tag_id', 'tags', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
