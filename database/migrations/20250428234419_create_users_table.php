<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $this->table('users', ['id' => false, 'primary_key' => ['id_user_pk']])
        ->addColumn('id_user_pk', 'integer', ['identity' => true, 'signed' => false])
        ->addColumn('username', 'string', ['limit' => 255])
        ->addColumn('mail', 'string', ['limit' => 255])
        ->addColumn('password', 'string', ['limit' => 255])
        ->addColumn('mobile_phone', 'string', ['limit' => 20, 'null' => true])
        ->addColumn('permission_level', 'integer', ['default' => 0])
        ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
        ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
        ->addIndex(['username'], ['unique' => true])
        ->addIndex(['mail'], ['unique' => true])
        ->create();
    }
}
