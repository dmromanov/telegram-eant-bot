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

    public function request(string $method, array $payload = [])
    {
        return TelegramApi::request(env('TELEGRAM_APIKEY'), $method, $payload);
    }

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
}
