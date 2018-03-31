<?php
use Migrations\AbstractMigration;

class Relations extends AbstractMigration
{

    public function up()
    {
        $this->table('votes')
            ->dropForeignKey([], 'votes_ibfk_1')
            ->removeIndexByName('chat_id')
            ->update();

        $this->table('votes')
            ->removeColumn('chat_id')
            ->update();

        $this->table('votes')
            ->addColumn('event_id', 'uuid', [
                'after' => 'user_id',
                'default' => null,
                'length' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_id',
                ],
                [
                    'name' => 'event_id',
                ]
            )
            ->update();

        $this->table('events')
            ->addColumn('id_message', 'string', [
                'after' => 'author_id',
                'default' => null,
                'length' => 100,
                'null' => false,
            ])
            ->update();

        $this->table('votes')
            ->addForeignKey(
                'event_id',
                'events',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE'
                ]
            )
            ->update();
    }

    public function down()
    {
        $this->table('votes')
            ->dropForeignKey(
                'event_id'
            );

        $this->table('votes')
            ->removeIndexByName('event_id')
            ->update();

        $this->table('votes')
            ->addColumn('chat_id', 'string', [
                'after' => 'id',
                'default' => null,
                'length' => 500,
                'null' => false,
            ])
            ->removeColumn('event_id')
            ->addIndex(
                [
                    'chat_id',
                ],
                [
                    'name' => 'chat_id',
                ]
            )
            ->update();

        $this->table('events')
            ->removeColumn('id_message')
            ->update();

        $this->table('votes')
            ->addForeignKey(
                'chat_id',
                'chats',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE'
                ]
            )
            ->update();
    }
}

