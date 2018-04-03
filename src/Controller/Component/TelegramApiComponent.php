<?php

namespace App\Controller\Component;

use App\Api\TelegramApi;
use App\Model\Entity\Event;
use App\Model\Entity\Vote;
use Cake\Controller\Component;

/**
 * TelegramApi component
 */
class TelegramApiComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * @param string $method
     * @param array $payload
     *
     * @return bool
     */
    public function request(string $method, array $payload = [])
    {
        return TelegramApi::request(env('TELEGRAM_APIKEY'), $method, $payload);
    }

    /**
     * @param Event $event
     *
     * @return string
     */
    public function getReplyKeyboard(Event $event): string
    {
        return json_encode([
            "inline_keyboard" => [
                [
                    ["text" => Vote::YES, "callback_data" => sprintf('%s:%d', $event->id, 1),],
                    ["text" => Vote::NO, "callback_data" => sprintf('%s:%d', $event->id, -1),],
                ],
            ],
        ]);
    }

    /**
     * Parse a message
     *
     * Extracts a command and an argument of a message
     *
     * @param string $message Telegram message
     *
     * @return array Array of a command and an argument
     */
    public function parse(string $message): array
    {
        $argument = '';
        $command = $message;

        if (strpos($command, ' ') !== false) {
            list($command, $argument) = explode(' ', $command, 2);
        }

        if (strpos($command, '@') !== false) {
            list($command) = explode('@', $command, 2);
        }

        return [$command, $argument];
    }
}
