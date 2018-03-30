<?php

namespace App\Api;

use Cake\Core\Configure;
use Cake\Network\Http\Client;

class TelegramApi
{

    /**
     * @param string $method
     * @param array $payload
     */
    public static function request(string $method, array $payload = [])
    {
        $url = sprintf('https://api.telegram.org/bot%s/', Configure::read('Telegram.key'));
        $api = new Client();
        $response = $api->post($url . $method, $payload);

        $data = $response->body('json_decode');

        if (!$response->isOk()) {
            sleep(10);
            throw new \RuntimeException(sprintf(__('Telegram responded error: "%s"'), $data->description), 502);
        }

        if (!$data->ok) {
            throw new \RuntimeException(__('Failed to parse Telegram response'), 502);
        }

        return $data->result;
    }
}
