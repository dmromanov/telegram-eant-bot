<?php
use Migrations\AbstractMigration;

class NewFields extends AbstractMigration
{

    public function up()
    {

        $this->table('chats')
            ->addColumn('type', 'string', [
                'after' => 'id',
                'default' => null,
                'length' => 50,
                'null' => true,
            ])
            ->update();

        $this->table('users')
            ->addColumn('username', 'string', [
                'after' => 'lastname',
                'default' => null,
                'length' => 52,
                'null' => true,
            ])
            ->addColumn('lang', 'string', [
                'after' => 'organized_events',
                'default' => null,
                'length' => 6,
                'null' => true,
            ])
            ->update();
    }

    public function down()
    {

        $this->table('chats')
            ->removeColumn('type')
            ->update();

        $this->table('users')
            ->removeColumn('username')
            ->removeColumn('lang')
            ->update();
    }
}

