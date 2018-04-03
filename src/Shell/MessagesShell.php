<?php
namespace App\Shell;

use App\Api\TelegramApi;
use Cake\Console\Shell;
use Cake\Error\Debugger;

/**
 * Messages shell command.
 */
class MessagesShell extends Shell
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

        $parser1 = clone $parser;
        $parser1->addArgument('chat_id', [
            'help' => __('Chat ID.'),
            'required' => true,
        ]);
        $parser1->addArgument('message_id', [
            'help' => __('Message ID.'),
            'required' => true,
        ]);
        $parser->addSubcommand('delete', [
            'help' => __('Deletes a message in a chat.'),
            'parser' => $parser1
        ]);

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        $this->out($this->OptionParser->help());
    }

    public function delete(string $chatId, string $messageId) {
        $chatId = trim($chatId, "'");
        $messageId = trim($messageId, "'");
        $this->out(__('Listing all messages in a chat <info>{chatId}</info>', compact('chatId')));

        $response = TelegramApi::request(env('TELEGRAM_APIKEY'), 'getChat', [
            'chat_id' => $chatId,
        ]);
        $this->out(Debugger::exportVar($response));

        $this->out(__('Deleting a message <info>{messageId}</info>', compact('messageId')));
        $response = TelegramApi::request(env('TELEGRAM_APIKEY'), 'deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
        $this->out(Debugger::exportVar($response));

        $this->success(__('Done.'));
        return Shell::CODE_SUCCESS;
    }

}
