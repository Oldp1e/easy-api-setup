<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateUsersTableAddUserType extends AbstractMigration
{
    /**
     * Add user_type_id to users table
     */
    public function change(): void
    {
        $this->table('users')
            ->addColumn('user_type_id', 'integer', ['null' => true, 'signed' => false, 'after' => 'permission_level'])
            ->addColumn('avatar', 'string', ['null' => true, 'limit' => 500, 'after' => 'mobile_phone'])
            ->addColumn('bio', 'text', ['null' => true, 'after' => 'avatar'])
            ->addColumn('last_login_at', 'timestamp', ['null' => true, 'after' => 'bio'])
            ->addColumn('email_verified_at', 'timestamp', ['null' => true, 'after' => 'last_login_at'])
            ->addColumn('is_active', 'boolean', ['default' => true, 'after' => 'email_verified_at'])
            ->addIndex(['user_type_id'])
            ->addIndex(['is_active'])
            ->addIndex(['email_verified_at'])
            ->addForeignKey('user_type_id', 'user_types', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->update();
    }
}
