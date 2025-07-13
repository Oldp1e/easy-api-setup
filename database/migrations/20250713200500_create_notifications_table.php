<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateNotificationsTable extends AbstractMigration
{
    /**
     * Create notifications table for user notifications
     */
    public function change(): void
    {
        $this->table('notifications', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('type', 'string', ['limit' => 50])
            ->addColumn('title', 'string', ['limit' => 255])
            ->addColumn('message', 'text')
            ->addColumn('data', 'json', ['null' => true, 'comment' => 'Additional notification data'])
            ->addColumn('read_at', 'timestamp', ['null' => true])
            ->addColumn('action_url', 'string', ['null' => true, 'limit' => 500])
            ->addColumn('priority', 'enum', ['values' => ['low', 'normal', 'high'], 'default' => 'normal'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['user_id'])
            ->addIndex(['type'])
            ->addIndex(['read_at'])
            ->addIndex(['priority'])
            ->addIndex(['created_at'])
            ->addForeignKey('user_id', 'users', 'id_user_pk', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
