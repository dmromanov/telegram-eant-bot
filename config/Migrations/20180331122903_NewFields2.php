<?php
use Migrations\AbstractMigration;

class NewFields2 extends AbstractMigration
{

    public function up()
    {

        $this->table('users')
            ->addColumn('is_bot', 'boolean', [
                'after' => 'lang',
                'default' => '0',
                'length' => null,
                'null' => false,
            ])
            ->update();
    }

    public function down()
    {

        $this->table('users')
            ->removeColumn('is_bot')
            ->update();
    }
}

