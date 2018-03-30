<?php

namespace App\Api;

use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Log\LogTrait;
use Cake\Network\Http\Client;

class TelegramApi
{
    use LogTrait;

    /**
     * @param string $apiKey
     * @param string $method
     * @param array $payload
     *
     * @return
     */
    public static function request(string $apiKey, string $method, array $payload = [])
    {
        $url = sprintf('https://api.telegram.org/bot%s/', $apiKey);

        $api = new Client();

        $url = $url . $method;

        Log::debug(__('Sending a request to: {url}', [
            'url' => $url,
        ]));
        $response = $api->post($url, $payload);

        $data = $response->body('json_decode');

        if (!$response->isOk()) {
            var_dump($data);
            throw new \RuntimeException(sprintf(__('Telegram responded error: "%s"'), $data->description), 502);
        }

        if (!$data->ok) {
            throw new \RuntimeException(__('Failed to parse Telegram response'), 502);
        }

        return $data->result;
    }
}
