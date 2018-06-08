<?php

namespace App\Api;

use Cake\Cache\Cache;
use Cake\Error\Debugger;
use Cake\Log\Log;
use Cake\Log\LogTrait;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Http\Client;

/**
 * Class TelegramApi
 * @package App\Api
 */
class TelegramApi
{
    use LogTrait;

    protected const CACHE_KEY_TELEGRAM_UPDATEID = 'Telegram.update_id';

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

        Log::debug(__('Sending a request to: {url}; Payload: {payload}', [
            'url' => $url,
            'payload' => Debugger::exportVar($payload),
        ]));
        $response = $api->post($url, $payload, [
            'type' => 'json'
        ]);

        $data = $response->body('json_decode');

        if ($response->getStatusCode() === 403) {
            Log::error(Debugger::exportVar($data));
            throw new ForbiddenException();
        }

        if ($response->getStatusCode() === 400) {
            Log::error(Debugger::exportVar($response) . PHP_EOL . Debugger::exportVar($data));
            return false;
        }

        if (!$response->isOk()) {
            Log::error(Debugger::exportVar($response));
            throw new \RuntimeException(sprintf(__('Telegram responded error: "%s"'), $data->description), 502);
        }

        if (!$data->ok) {
            throw new \RuntimeException(__('Failed to parse Telegram response'), 502);
        }

        return $data->result;
    }

    /**
     * @param string $updateId
     *
     * @return bool
     */
    public static function storeUpdateId(string $updateId): bool
    {
        if ($updateId <= TelegramApi::retrieveUpdateId()) {
            return false;
        }

        Cache::write(static::CACHE_KEY_TELEGRAM_UPDATEID, $updateId);

        return true;
    }

    /**
     * @return int|null
     */
    public static function retrieveUpdateId(): ?int
    {
        return Cache::read(static::CACHE_KEY_TELEGRAM_UPDATEID);
    }
}
