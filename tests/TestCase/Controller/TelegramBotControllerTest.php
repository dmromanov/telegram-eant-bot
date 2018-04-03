<?php

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestCase;

/**
 * Class TelegramBotControllerTest
 * @package App\Test\TestCase\Controller\Api
 */
class TelegramBotControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.chats',
        'app.users',
        'app.events',
        'app.votes',
    ];

    /**
     * Test setUp
     */
    public function setUp()
    {
        $_ENV['TELEGRAM_APIKEY'] = '123';
    }

    /**
     * Test webhook "/new" command
     */
    public function testWebhookNewCommand()
    {
        // Enable PSR-7 integration testing.
        $this->post(
            '/api/telegram-bot/webhook/123',
            [
                'update_id' => 686221431,
                'message' => [
                    'message_id' => 707,
                    'from' => [
                        'id' => 151939862,
                        'is_bot' => false,
                        'first_name' => 'Foo',
                        'username' => 'foobar',
                        'language_code' => 'en',
                    ],

                    'chat' => [
                        'id' => 151939862,
                        'first_name' => 'Foo',
                        'username' => 'foobar',
                        'type' => 'private',
                    ],

                    'date' => 1522683651,
                    'text' => '/new test',
                    'entities' => [
                        0 => [
                            'offset' => 0,
                            'length' => 4,
                            'type' => 'bot_command',
                        ],

                    ],

                ],
            ]
        );
        $this->assertResponseOk();
    }
}
