<?php
namespace App\Shell;

use App\Api\TelegramApi;
use Cake\Console\Shell;
use Cake\Filesystem\File;

/**
 * Setup shell command.
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
     *
     * @return bool|int|null Success or error code.
     */
    public function webhookRegister(string $domain, string $certificatePath, $maxConnections = null)
    {
        $this->out(__('Enabling webhook.'));
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
                'url' => sprintf('https://%s/api/telegram-bot/webhook/%s', $domain, $this->param('apiKey')),
            //                'certificate' => $certificate->handle,
                'max_connections' => $maxConnections,
            //            'allowed_updates' => ['message']
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
        var_export($result);

        $this->success(__('Done.'));

        return Shell::CODE_SUCCESS;
    }
}
