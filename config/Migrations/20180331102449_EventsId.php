<?php
use Migrations\AbstractMigration;

class EventsId extends AbstractMigration
{

    public function up()
    {

        $this->table('events')
            ->changeColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->update();
    }

    public function down()
    {

        $this->table('events')
            ->changeColumn('id', 'string', [
                'default' => null,
                'length' => 100,
                'null' => false,
            ])
            ->update();
    }
}

