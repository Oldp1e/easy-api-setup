<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserTypesTable extends AbstractMigration
{
    /**
     * Create user_types table for categorizing users
     */
    public function change(): void
    {
        $this->table('user_types', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('permissions', 'json', ['null' => true, 'comment' => 'JSON array of permissions'])
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['name'], ['unique' => true])
            ->addIndex(['is_active'])
            ->create();
    }
}
