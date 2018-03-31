<?php

namespace App\Controller\Api;

use App\Api\TelegramApi;
use App\Api\TelegramException;
use App\Controller\AppController;
use App\Model\Table\ChatsTable;
use Cake\Event\Event;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\ForbiddenException;
use Cake\Utility\Inflector;
use Cake\View\View;
use Psr\Log\LogLevel;

/**
 * Class TelegramBotController
 * @package App\Controller\Api
 *
 * @property ChatsTable $Chats
 */
class TelegramBotController extends AppController
{
    /**
     * @param Event $event
     *
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->loadComponent('RequestHandler');
        $this->RequestHandler->accepts('json');
    }

    /**
     * @param string $apikey
     *
     * @throws \Exception
     */
    public function webhook(string $apikey)
    {
        $this->request->allowMethod(['POST']);

        if ($apikey !== env('TELEGRAM_APIKEY')) {
            throw new TelegramException(sprintf('Invalid apikey provided: "%s"', $apikey));
        }

        $update = $this->request->getData();
        $this->log(print_r($update, true), LogLevel::DEBUG);

        if (empty($update)) {
            throw new BadRequestException(__('Invalid input data provided'));
        }

        $this->loadModel('Chats');

        try {
            if (!isset($update['message'])) {
                throw new BadRequestException(__('Missing "message" attribute.'));
            }

            $command = $update['message']['text'];

            $this->log($update['message']['chat']['id'], LogLevel::INFO);
            $chat = $this->Chats->newEntity([
                'id' => $update['message']['chat']['id']
            ]);
            $this->Chats->save($chat);

            if (strpos($command, '@') !== false) {
                list($command) = explode('@', $command, 2);
            }
            if (strpos($command, ' ') !== false) {
                list($command, $arguments) = explode(' ', $command, 2);
            }

            $method = sprintf('command%s', Inflector::camelize(ucfirst(ltrim($command, '/'))));
            $template = ltrim($command, '/');

            if (!is_callable([$this, $method])) {
                throw new BadRequestException(__('Command handler does not exist'));
            }

            \App\Api\TelegramApi::request(
                env('TELEGRAM_APIKEY'),
                'sendChatAction',
                [
                    'chat_id' => $update['message']['chat']['id'],
                    'action' => 'typing',
                ]
            );

            $this->log(
                __('Processing "{command}" command.', ['command' => '/' . $command]),
                LogLevel::DEBUG
            );

            $this->$method($template, $update['update_id'], $update['message']['chat']['id'], $arguments);
        } catch (ForbiddenException $e) {
            $this->Chats->delete($this->Chats->get($update['message']['chat']['id']));
        } catch (BadRequestException $e) {
            \App\Api\TelegramApi::request(
                env('TELEGRAM_APIKEY'),
                'sendMessage',
                [
                    'chat_id' => $update['message']['chat']['id'],
                    'reply_to_message_id' => $update['message']['message_id'],
                    'text' => file_get_contents(APP . 'Template/Commands/Errors/unknown_command.markdown'),
                ]
            );
        } catch (\Exception $e) {
            \App\Api\TelegramApi::request(
                env('TELEGRAM_APIKEY'),
                'sendMessage',
                [
                    'chat_id' => $update['message']['chat']['id'],
                    'reply_to_message_id' => $update['message']['message_id'],
                    'text' => file_get_contents(APP . 'Template/Commands/Errors/internal_error.markdown'),
                ]
            );
            throw $e;
        }

        TelegramApi::storeUpdateId($update['update_id']);
        $this->response->stop();
    }

    /**
     * @param string $updateId
     * @param string $chatId
     */
    protected function commandStart(string $template, string $updateId, string $chatId)
    {
        $message = $this->renderTemplate($template);

        $response = \App\Api\TelegramApi::request(
            env('TELEGRAM_APIKEY'),
            'sendMessage',
            [
                'chat_id' => $chatId,
                'parse_mode' => 'Markdown',
                'text' => $message,
            ]
        );
    }

    /**
     * @param string $updateId
     * @param string $chatId
     */
    protected function commandHelp(string $template, string $updateId, string $chatId)
    {
        $message = $this->renderTemplate($template);

        $response = \App\Api\TelegramApi::request(
            env('TELEGRAM_APIKEY'),
            'sendMessage',
            [
                'chat_id' => $chatId,
                'parse_mode' => 'Markdown',
                'text' => $message,
            ]
        );
    }

    /**
     * @param string $updateId
     * @param string $chatId
     */
    protected function commandNew(string $template, string $updateId, string $chatId, $arg = '')
    {
        $message = $this->renderTemplate($template, [
            'message' => $arg
        ]);

        $response = \App\Api\TelegramApi::request(
            env('TELEGRAM_APIKEY'),
            'sendMessage',
            [
                'chat_id' => $chatId,
                'parse_mode' => 'Markdown',
                'text' => $message,
            ]
        );
    }

    /**
     * @param string $updateId
     * @param string $chatId
     */
    protected function commandUserstats(string $template, string $updateId, string $chatId)
    {
        $message = $this->renderTemplate($template);

        $response = \App\Api\TelegramApi::request(
            env('TELEGRAM_APIKEY'),
            'sendMessage',
            [
                'chat_id' => $chatId,
                'parse_mode' => 'Markdown',
                'text' => $message,
            ]
        );
    }

    /**
     * @param string $updateId
     * @param string $chatId
     */
    protected function commandToggleInformalParsing(string $template, string $updateId, string $chatId)
    {
        $message = $this->renderTemplate($template);

        $response = \App\Api\TelegramApi::request(
            env('TELEGRAM_APIKEY'),
            'sendMessage',
            [
                'chat_id' => $chatId,
                'parse_mode' => 'Markdown',
                'text' => $message,
            ]
        );
    }

    private function renderTemplate(string $template, array $args = []): string {
        $view = new View($this->request, $this->response, $this->getEventManager());
        $view->enableAutoLayout(false);
        $view->setTemplatePath('Commands');
        $view->setTemplate(sprintf('%s.markdown', $template));
        $view->set($args);
        return $view->render();
    }
}
