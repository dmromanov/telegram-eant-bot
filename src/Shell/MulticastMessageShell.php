<?php
namespace App\Shell;

use App\Api\TelegramApi;
use App\Model\Table\ChatsTable;
use Cake\Collection\Iterator\ExtractIterator;
use Cake\Console\Shell;
use Cake\Network\Exception\ForbiddenException;
use Cake\Shell\Helper\ProgressHelper;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * MulticastMessage shell command.
 * @property ChatsTable $Chats
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
        /** @var ExtractIterator $chats */
        $chats = $this->Chats
            ->find()
            ->extract('id');

        $this->helper('Progress')->output([
            'total' => iterator_count($chats),
            'callback' => function (ProgressHelper $progress) use ($chats, $message) {

                // multicasting
                foreach ($chats as $chatId) {
                    $this->log(__('Sending a message to {chatId}', [
                        'chatId' => $chatId,
                    ]), LogLevel::INFO);

                    try {
                        $result = TelegramApi::request(
                            env('TELEGRAM_APIKEY'),
                            'sendMessage',
                            [
                                'chat_id' => $chatId,
                                'parse_mode' => 'Markdown',
                                'text' => $message,
                            ]
                        );
                    } catch (ForbiddenException $e) {
                        $this->log(__('I no longer have an access to that chat, removing.'), LogLevel::INFO);
                        $this->Chats->delete($this->Chats->get($chatId));
                    }

                    $progress->increment(1);
                    $progress->draw();
                }
            }
        ]);

        $this->success(__('Done.'));

        return Shell::CODE_SUCCESS;
    }
}
