<?php

namespace App\Http\Bots\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

/**
 * Class HelpCommand.
 */
class UpdateCommandsListCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'updCmdList';
    /**
     * @var string Command Description
     */
    protected $description = 'Обновить список доступных команд';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $commands = $this->telegram->getCommands();
        Telegram::addCommands($commands);

        $this->replyWithMessage(['text' => 'ok']);
    }
}
