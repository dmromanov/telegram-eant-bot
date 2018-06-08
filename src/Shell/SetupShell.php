<?php
namespace App\Shell;

use App\Api\TelegramApi;
use App\Model\Table\ChatsTable;
use Cake\Console\Shell;
use Cake\Filesystem\File;
use Cake\Http\Client;
use function sprintf;

/**
 * Setup shell command.
 *
 * @property ChatsTable $Chats
 */
class SetupShell extends Shell
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

        $parser->addOption('apiKey', [
            'help' => __('Telegram API key.'),
            'default' => env('TELEGRAM_APIKEY'),
        ]);

        $parser1 = clone $parser;
        $parser1->addArgument('domain', [
            'required' => true,
            'help' => __('Domain where bot is running, e.g. example.com'),
        ]);
        $parser1->addArgument('certificate', [
            'required' => true,
            'help' => __('Public key certificate file.'),
        ]);
        $parser1->addArgument('max-connections', [
            'default' => 40,
            'help' => __('Maximum connections (1-100).'),
        ]);
        $parser->addSubcommand('webhook-register', [
            'help' => 'Registers webhook in Telegram servers.',
            'parser' => $parser1,
        ]);

        $parser2 = clone $parser;
        $parser->addSubcommand('webhook-unregister', [
            'help' => 'Registers webhook in Telegram servers.',
            'parser' => $parser2,
        ]);

        $parser3 = clone $parser;
        $parser->addSubcommand('clean_db', [
            'help' => 'Cleans a database. Useful when switching the app to a different bot account.',
            'parser' => $parser3,
        ]);

        $parser->setDescription(__('Controls bot\'s integration with Telegram'));

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

    /**
     * @param string $domain
     * @param string $certificatePath
     * @param int $maxConnections
     *
     * @return bool|int|null Success or error code.
     */
    public function webhookRegister(string $domain, string $certificatePath, int $maxConnections = 5)
    {
        $url = sprintf('https://%s/api/telegram-bot/webhook/%s', $domain, $this->param('apiKey'));
        $this->out(__('Checking whether <info>{0}</info> is accessible', $url));

        $client = new Client();
        $response = $client->get($url);
        if (!$response->isOk()) {
            $this->err(__('Received HTTP {0}, {1}', $response->getStatusCode(), $response->body()));
            $this->abort(__('Provided URL is unaccessible.'));
        }

        $this->out(__('Enabling webhook.'));
        $this->out(__('Using <info>{0}</info>', $this->param('apiKey')));
        $certificate = new File($certificatePath);
        try {
            if (!$certificate->readable()) {
                throw new \RuntimeException(__('Certificate does not exist or is not readable.'));
            }
            if (!$certificate->open()) {
                throw new \RuntimeException(__('Could not read Certificate.'));
            }
        } catch (\RuntimeException $e) {
            $this->abort($e->getMessage());
        }

        $result = TelegramApi::request(
            $this->param('apiKey'),
            'setWebhook',
            [
                'url' => $url,
            //                'certificate' => $certificate->handle,
                'max_connections' => $maxConnections,
                'allowed_updates' => [
            //                    'message',
            //                    'edited_message',
            //                    'channel_post',
            //                    'edited_channel_post',
                ]
            ]
        );
        var_export($result);

        $this->success(__('Done.'));

        return Shell::CODE_SUCCESS;
    }

    /**
     * @param string $certificatePath
     *
     * @return bool|int|null Success or error code.
     */
    public function webhookUnregister()
    {
        $result = TelegramApi::request(env('TELEGRAM_APIKEY'), 'deleteWebhook', []);

        $this->success(__('Done.'));

        return Shell::CODE_SUCCESS;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function cleanDb()
    {
        // TODO: get all tables from schema and iterate over it.

        $this->loadModel('Chats');
        $this->Chats->getConnection()->transactional(function ($conn) {
            $sqls = $this->Chats->getSchema()->truncateSql($this->Chats->getConnection());
            foreach ($sqls as $sql) {
                $this->Chats->getConnection()->execute($sql)->execute();
            }
        });

        $this->success(__('Done.'));

        return Shell::CODE_SUCCESS;
    }
}
