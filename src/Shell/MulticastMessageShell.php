<?php
namespace App\Shell;

use App\Api\TelegramApi;
use App\Model\Table\ChatsTable;
use Cake\Console\Shell;

/**
 * MulticastMessage shell command.
 * @property ChatsTable $chats
 */
class MulticastMessageShell extends Shell
{

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->setDescription(__('Send a message to all available chats'));

        $parser->addArgument('message', [
            'required' => true,
            'help' => __('Message to send.'),
        ]);

        return $parser;
    }

    /**
     * main() method.
     *
     * @param string $message Message to send
     *
     * @return bool|int|null Success or error code.
     */
    public function main(string $message)
    {
        $this->loadModel('Chats');
        $chats = $this->Chats
            ->find()
            ->extract('id');

        foreach ($chats as $chatId) {
            $this->out(__('Sending a message to <info>{chatId}</info>', [
                'chatId' => $chatId,
            ]));

            $result = TelegramApi::request(
                env('TELEGRAM_APIKEY'),
                'sendMessage',
                [
                    'chat_id' => $chatId,
                    'parse_mode' => 'Markdown',
                    'text' => $message,
                ]
            );
        }

        $this->success(__('Done.'));

        return Shell::CODE_SUCCESS;
    }
}
