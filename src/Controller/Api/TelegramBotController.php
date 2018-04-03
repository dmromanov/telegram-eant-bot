<?php

namespace App\Controller\Api;

use App\Api\TelegramApi;
use App\Api\TelegramException;
use App\Controller\AppController;
use App\Controller\Component\TelegramApiComponent;
use App\Model\Entity\Chat;
use App\Model\Entity\User;
use App\Model\Table\ChatsTable;
use App\Model\Table\EventsTable;
use App\Model\Table\UsersTable;
use App\Model\Table\VotesTable;
use Cake\Error\Debugger;
use Cake\Event\Event;
use Cake\I18n\FrozenTime;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\Query;
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
 *
 * @property TelegramApiComponent $TelegramApi
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
        $this->loadComponent('TelegramApi');
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
        $this->log(Debugger::exportVar($update), LogLevel::DEBUG);

        if (empty($update)) {
            throw new BadRequestException(__('Invalid input data provided'));
        }

        $this->loadModel('Chats');

        try {
            if (isset($update['message'])) {
                $key = 'message';

                $this->messageHandler($update[$key]);

            } elseif (isset($update['callback_query'])) {
                $key = 'callback_query';

                $this->callbackHandler($update[$key]);
            }
        } catch (\Exception $e) {
            $this->log($e, LogLevel::ERROR);
//            $this->TelegramApi->request(
//                'sendMessage',
//                [
//                    'chat_id' => $chatId,
//                    'reply_to_message_id' => $update['message']['message_id'],
//                    'text' => $this->renderTemplate('internal_error'),
//                ]
//            );
        }

        TelegramApi::storeUpdateId($update['update_id']);
        $this->response->stop();
    }

    /**
     * @param array $message
     */
    protected function messageHandler(array $message)
    {
        if (!isset($message['text'])) {
            throw new BadRequestException();
        }

        list($command, $argument) = $this->TelegramApi->parse($message['text']);

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
                function(Chat $chat) use ($message) {
                    $chat->id = $message['chat']['id'];
                    $chat->type = $message['chat']['type'];

                    return $chat;
                }
            );
            $this->Chats->save($chat);

            $this->loadModel('Users');
            $user = $this->Users->findOrCreate(
                $this->Users->findById($message['from']['id']),
                function(User $user) use ($chat, $message) {
                    $this->log(\Cake\Error\Debugger::exportVar($user));
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
            $this->TelegramApi->request(
                'sendChatAction',
                [
                    'chat_id' => $message['chat']['id'],
                    'action' => 'typing',
                ]
            );

            $this->$method($template, $chat, $user, $argument, $message['message_id']);
        } catch (ForbiddenException $e) {
            $this->Chats->delete($this->Chats->get($message['chat']['id']));
        } catch (BadRequestException $e) {
            if (isset($message) && $message['chat']['type'] === 'private') {
//                $this->TelegramApi->request(
//                    env('TELEGRAM_APIKEY'),
//                    'sendMessage',
//                    [
//                        'chat_id' => $message['chat']['id'],
//                        'reply_to_message_id' => $message['message_id'],
//                        'text' => $this->renderTemplate('unknown_command'),
//                    ]
//                );
            }
        }
    }

    /**
     * @param array $callback
     */
    protected function callbackHandler(array $callback)
    {
        try {
            list($eventId, $vote) = explode(':', $callback['data'], 2);
            $this->loadModel('Chats');
            $chat = $this->Chats->get($callback['message']['chat']['id']);

            $this->loadModel('Users');
            $user = $this->Users->get($callback['from']['id']);

            $user->last_activity = new FrozenTime();
            $result = $this->Users->save($user);
            if (!$result) {
                throw new ValidationException($user);
            }

            $this->loadModel('Events');
            /** @var \App\Model\Entity\Event $event */
            $event = $this->Events->get($eventId);

            $this->loadModel('Votes');
            $vote = $this->Votes->newEntity([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'vote' => ((int)$vote > 0 ? true : false),
            ]);

            $this->Votes->getConnection()->transactional(function () use ($user, $event, $vote) {
                $this->Votes->deleteAll([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                ]);
                $result = $this->Votes->save($vote);
                if (!$result) {
                    throw new ValidationException($result);
                }
            });

            $votes = $this->Votes->find()
                ->select()
                ->where([
                    'event_id' => $event->id,
                ])
                ->contain([
                    'Users' => function (Query $q) {
                        $q->order([
                            'User.vote' => 'DESC',
                            'User.firstname' => 'ASC',
                        ]);
                        return $q;
                    }
                ]);

            $text = $this->renderTemplate('new',
            [
                'message' => $event->title, // FIXME: correct me
                'user' => $user,
                'chat' => $chat,
                'votes' => $votes
            ]);
            $response = $this->TelegramApi->request('editMessageText', [
                'chat_id' => $chat->id,
                'message_id' => $event->id_message,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'reply_markup' => $this->TelegramApi->getReplyKeyboard($event),
            ]);
            $this->log($response);
        } catch (ForbiddenException $e) {
            $this->Chats->delete($this->Chats->get($callback['chat']['id']));
        } catch (BadRequestException $e) {
            if (isset($callback) && $callback['chat']['type'] === 'private') {
                $this->TelegramApi->request(
                    'sendMessage',
                    [
                        'chat_id' => $callback['chat']['id'],
                        'reply_to_message_id' => $callback['message_id'],
                        'text' => $this->renderTemplate('unknown_command'),
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

        $response = $this->TelegramApi->request(
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
        $message = $this->renderTemplate($template,
            [
                'is_private' => $chat->type === 'private',
            ]);

        $response = $this->TelegramApi->request(
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
        if (strlen($arg) === 0) {
            $arg = PHP_EOL;
        }

        $message = $this->renderTemplate($template,
            [
                'message' => $arg,
                'user' => $user,
                'chat' => $chat,
            ]);

        $this->loadModel('Events');

        $this->Events->getConnection()->transactional(function () use ($chat, $message, $user, $arg) {
            $event = $this->Events->newEntity([
                'id' => Text::uuid(),
                'chat_id' => $chat->id,
                'author_id' => $user->id,
                'id_message' => null,
                'title' => $arg,
                'date' => new FrozenTime(),
                'min_responses' => null,
                'geopoint' => null,
            ]);
            $result = $this->Events->save($event);
            if (!$result) {
                $this->log(Debugger::exportVar($event->getErrors()));
                throw new ValidationException($event);
            }

            $response = $this->TelegramApi->request(
                'sendMessage',
                [
                    'chat_id' => $chat->id,
                    'parse_mode' => 'Markdown',
                    'text' => $message,
                    'reply_markup' => $this->TelegramApi->getReplyKeyboard($event),
                ]
            );
            $this->log(Debugger::exportVar($response->message_id));
            $event->id_message = $response->message_id;
            $result = $this->Events->save($event);
            if (!$result) {
                throw new ValidationException($event);
            }
        });

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

        $message = $this->renderTemplate($template,
            [
                'activists' => $activists,
                'inactive' => $inactive,
            ]);

        $response = $this->TelegramApi->request(
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

        $response = $this->TelegramApi->request(
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
