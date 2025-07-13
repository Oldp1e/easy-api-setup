<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResets extends AbstractMigration
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
        $table = $this->table('password_resets', [
            'id' => false,
            'primary_key' => 'token',
            'engine' => 'InnoDB'
        ]);

        $table
            ->addColumn('token', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('id_user_fk', 'integer', ['signed' => false]) // ✅ compatível com users
            ->addColumn('expires_at', 'datetime')
            ->addColumn('used', 'boolean', ['default' => false])
            ->addForeignKey(
                'id_user_fk',
                'users',
                'id_user_pk',
                ['delete' => 'CASCADE', 'update' => 'NO_ACTION']
            )
            ->create();
    }

}
