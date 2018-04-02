<?php

namespace App\Controller\Api;

use App\Api\TelegramApi;
use App\Api\TelegramException;
use App\Controller\AppController;
use App\Model\Entity\Chat;
use App\Model\Entity\User;
use App\Model\Table\ChatsTable;
use App\Model\Table\EventsTable;
use App\Model\Table\UsersTable;
use App\Model\Table\VotesTable;
use Cake\Event\Event;
use Cake\I18n\FrozenTime;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\ForbiddenException;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use Cake\View\View;
use JsonSchema\Exception\ValidationException;
use Psr\Log\LogLevel;

/**
 * Class TelegramBotController
 * @package App\Controller\Api
 *
 * @property ChatsTable $Chats
 * @property EventsTable $Events
 * @property UsersTable $Users
 * @property VotesTable $Votes
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
            if (isset($update['message'])) {
                $this->messageHandler($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->callbackHandler($update['callback_query']);
            }
        } catch (\Exception $e) {
            $this->log($e, LogLevel::ERROR);
            \App\Api\TelegramApi::request(
                env('TELEGRAM_APIKEY'),
                'sendMessage',
                [
                    'chat_id' => $update['message']['chat']['id'],
                    'reply_to_message_id' => $update['message']['message_id'],
                    'text' => file_get_contents(APP . 'Template/Commands/Errors/internal_error.markdown'),
                ]
            );
        }

        TelegramApi::storeUpdateId($update['update_id']);
        $this->response->stop();
    }

    /**
     * @param array $message
     */
    protected function messageHandler(array $message)
    {
            $argument = '';
            $command = $message['text'];

        if (strpos($command, '@') !== false) {
            list($command) = explode('@', $command, 2);
        }
        if (strpos($command, ' ') !== false) {
            list($command, $argument) = explode(' ', $command, 2);
        }

            $method = sprintf('command%s', Inflector::camelize(ucfirst(ltrim($command, '/'))));
            $template = ltrim($command, '/');

        try {
            if (!is_callable([$this, $method])) {
                throw new BadRequestException(__('Command handler does not exist'));
            }

            $this->log(
                __('Processing "{command}" command.', ['command' => $command]),
                LogLevel::DEBUG
            );

            $this->loadModel('Chats');
            $chat = $this->Chats->findOrCreate(
                $this->Chats->findById($message['chat']['id']),
                function (Chat $chat) use ($message) {
                    $chat->id = $message['chat']['id'];
                    $chat->type = $message['chat']['type'];

                    return $chat;
                }
            );
            $this->Chats->save($chat);

            $this->loadModel('Users');
            $user = $this->Users->findOrCreate(
                $this->Users->findById($message['from']['id']),
                function (User $user) use ($chat, $message) {
                    $this->log(print_r($user, true));
                    $user->id = $message['from']['id'];
                    $user->last_activity = new FrozenTime();
                    $user->chat_id = $chat->id;
                    $user->firstname = $message['from']['first_name'];
                    $user->lastname = $message['from']['last_name'];
                    $user->username = $message['from']['username'];
                    $user->is_bot = (bool)$message['from']['is_bot'];

                    return $user;
                }
            );
            $user->last_activity = new FrozenTime();
            $result = $this->Users->save($user);
            if (!$result) {
                throw new ValidationException($user);
            }
            \App\Api\TelegramApi::request(
                env('TELEGRAM_APIKEY'),
                'sendChatAction',
                [
                    'chat_id' => $message['chat']['id'],
                    'action' => 'typing',
                ]
            );

            $this->$method($template, $chat, $user, $argument);
        } catch (ForbiddenException $e) {
            $this->Chats->delete($this->Chats->get($message['chat']['id']));
        } catch (BadRequestException $e) {
            if (isset($message) && $message['chat']['type'] === 'private') {
                \App\Api\TelegramApi::request(
                    env('TELEGRAM_APIKEY'),
                    'sendMessage',
                    [
                        'chat_id' => $message['chat']['id'],
                        'reply_to_message_id' => $message['message_id'],
                        'text' => file_get_contents(APP . 'Template/Commands/Errors/unknown_command.markdown'),
                    ]
                );
            }
        }
    }

    /**
     * @param array $callback
     */
    protected function callbackHandler(array $callback)
    {
        try {
            $this->loadModel('Chats');
            $chat = $this->Chats->findOrCreate(
                $this->Chats->findById($callback['message']['chat']['id']),
                function (Chat $chat) {

                    return $chat;
                }
            );
            $chat->type = $callback['message']['chat']['type'];
            $this->Chats->save($chat);

            $this->loadModel('Users');
            $user = $this->Users->findOrCreate($this->Users->findById($callback['from']['id']), function (User $user) {
                return $user;
            });
            $user->id = $callback['from']['id'];
            $user->last_activity = new FrozenTime();
            $user->chat_id = $chat->id;
            $user->firstname = $callback['from']['first_name'];
            $user->lastname = $callback['from']['last_name'];
            $user->username = $callback['from']['username'];
            $user->is_bot = (bool)$callback['from']['is_bot'];
            $result = $this->Users->save($user);
            if (!$result) {
                throw new ValidationException($user);
            }

            $response = $callback['data'];
            if ($response) {
                $this->loadModel('Users');
                $user->last_activity = new FrozenTime();
                $result = $this->Users->save($user);
                if (!$result) {
                    throw new ValidationException($user);
                }
            }

            $text = $this->renderTemplate('new', [
                'message' => $callback['message']['text'],
                'user' => $user,
                'chat' => $chat,
//                'votes' => $event->votes
            ]);
        } catch (ForbiddenException $e) {
            $this->Chats->delete($this->Chats->get($callback['chat']['id']));
        } catch (BadRequestException $e) {
            if (isset($callback) && $callback['chat']['type'] === 'private') {
                \App\Api\TelegramApi::request(
                    env('TELEGRAM_APIKEY'),
                    'sendMessage',
                    [
                        'chat_id' => $callback['chat']['id'],
                        'reply_to_message_id' => $callback['message_id'],
                        'text' => file_get_contents(APP . 'Template/Commands/Errors/unknown_command.markdown'),
                    ]
                );
            }
        }
    }

    /**
     * @param string $template
     * @param string $chat
     */
    protected function commandStart(string $template, Chat $chat, User $user)
    {
        $message = $this->renderTemplate($template);

        $response = \App\Api\TelegramApi::request(
            env('TELEGRAM_APIKEY'),
            'sendMessage',
            [
                'chat_id' => $chat->id,
                'parse_mode' => 'Markdown',
                'text' => $message,
            ]
        );
    }

    /**
     * @param string $template
     * @param string $chat
     */
    protected function commandHelp(string $template, Chat $chat, User $user)
    {
        $message = $this->renderTemplate($template, [
            'is_private' => $chat->type === 'private',
        ]);

        $response = \App\Api\TelegramApi::request(
            env('TELEGRAM_APIKEY'),
            'sendMessage',
            [
                'chat_id' => $chat->id,
                'parse_mode' => 'Markdown',
                'text' => $message,
            ]
        );
    }

    /**
     * @param string $template
     * @param string $chat
     * @param string $user
     * @param string $arg
     */
    protected function commandNew(string $template, Chat $chat, User $user, $arg = '')
    {
        // TODO: suggest title if $arg is not set

        $message = $this->renderTemplate($template, [
            'message' => $arg,
            'user' => $user,
            'chat' => $chat,
        ]);

        $response = \App\Api\TelegramApi::request(
            env('TELEGRAM_APIKEY'),
            'sendMessage',
            [
                'chat_id' => $chat->id,
                'parse_mode' => 'Markdown',
                'text' => $message,
                'reply_markup' => json_encode([
                    "inline_keyboard" => [
                        [
                            ["text" => "Y", "callback_data" => 1, ],
                            ["text" => "N", "callback_data" => -1, ],
                        ],
                    ],
                ]),
            ]
        );

        // updating stats
        $this->loadModel('Users');
        $user->organized_events = $user->organized_events + 1;
        $result = $this->Users->save($user);
        if (!$result) {
            throw new ValidationException($user);
        }
    }

    /**
     * @param string $template
     * @param string $chat
     * @param string $user
     */
    protected function commandUserstats(string $template, Chat $chat, User $user)
    {
        $reportingDate = new FrozenTime('-3 months');
        $this->loadModel('Users');

        $activists = $this->Users->find()
            ->where([
                'chat_id' => $chat->id,
                'organized_events >' => 0,
            ])
            ->all();
        $inactive = $this->Users->find()
            ->where([
                'chat_id' => $chat->id,
                'last_activity <' => $reportingDate,
            ])
            ->all();

        $message = $this->renderTemplate($template, [
            'activists' => $activists,
            'inactive' => $inactive,
        ]);

        $response = \App\Api\TelegramApi::request(
            env('TELEGRAM_APIKEY'),
            'sendMessage',
            [
                'chat_id' => $chat->id,
                'parse_mode' => 'Markdown',
                'text' => $message,
            ]
        );
    }

    /**
     * @param string $template
     * @param string $chat
     */
    protected function commandToggleInformalParsing(string $template, Chat $chat, User $user)
    {
        $message = $this->renderTemplate($template);

        $response = \App\Api\TelegramApi::request(
            env('TELEGRAM_APIKEY'),
            'sendMessage',
            [
                'chat_id' => $chat->id,
                'parse_mode' => 'Markdown',
                'text' => $message,
            ]
        );
    }

    /**
     * @param string $template
     * @param array $args
     *
     * @return string
     */
    private function renderTemplate(string $template, array $args = []): string
    {
        $view = new View($this->request, $this->response, $this->getEventManager());
        $view->enableAutoLayout(false);
        $view->setTemplatePath('Commands');
        $view->setTemplate(sprintf('%s.markdown', $template));
        $view->set($args);

        return $view->render();
    }
}
