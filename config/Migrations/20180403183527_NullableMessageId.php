<?php
use Migrations\AbstractMigration;

class NullableMessageId extends AbstractMigration
{

    public function up()
    {

        $this->table('events')
            ->changeColumn('id_message', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->update();
    }

    public function down()
    {

        $this->table('events')
            ->changeColumn('id_message', 'string', [
                'default' => null,
                'length' => 100,
                'null' => false,
            ])
            ->update();
    }
}

