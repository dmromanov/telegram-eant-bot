<?php
use Migrations\AbstractMigration;

class StatsTables extends AbstractMigration
{

    public function up()
    {

        $this->table('events', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('chat_id', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => false,
            ])
            ->addColumn('author_id', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('min_responses', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('geopoint', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'author_id',
                ]
            )
            ->addIndex(
                [
                    'chat_id',
                ]
            )
            ->create();

        $this->table('users', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('chat_id', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => false,
            ])
            ->addColumn('firstname', 'string', [
                'default' => null,
                'limit' => 52,
                'null' => false,
            ])
            ->addColumn('lastname', 'string', [
                'default' => null,
                'limit' => 52,
                'null' => false,
            ])
            ->addColumn('last_activity', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('organized_events', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'chat_id',
                ]
            )
            ->create();

        $this->table('votes')
            ->addColumn('chat_id', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => false,
            ])
            ->addColumn('user_id', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('vote', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'chat_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('events')
            ->addForeignKey(
                'author_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'chat_id',
                'chats',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('users')
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
            ->addForeignKey(
                'user_id',
                'users',
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
        $this->table('events')
            ->dropForeignKey(
                'author_id'
            )
            ->dropForeignKey(
                'chat_id'
            );

        $this->table('users')
            ->dropForeignKey(
                'chat_id'
            );

        $this->table('votes')
            ->dropForeignKey(
                'chat_id'
            )
            ->dropForeignKey(
                'user_id'
            );

        $this->dropTable('events');

        $this->dropTable('users');

        $this->dropTable('votes');
    }
}

