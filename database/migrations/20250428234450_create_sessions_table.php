<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSessionsTable extends AbstractMigration
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
        $this->table('sessions', ['id' => 'id_session_pk'])
        ->addColumn('id_user_fk', 'integer', ['signed' => false])
        ->addColumn('session_token', 'string', ['limit' => 255])
        ->addColumn('login_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
        ->addIndex(['id_user_fk'])
        ->addForeignKey('id_user_fk', 'users', 'id_user_pk', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
        ->create();
    }
}
