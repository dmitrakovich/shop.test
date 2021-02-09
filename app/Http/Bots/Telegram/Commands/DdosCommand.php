<?php

namespace App\Http\Bots\Telegram\Commands;

use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class DdosCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'ddos';

    /**
     * @var string Command Description
     */
    protected $description = 'Ddos command';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $commands = $this->telegram->getCommands();

        $text = 'ddos x 10';
        for ($i=0; $i < 10; $i++) {
            $this->replyWithMessage(['text' => "ddos #$i"]);
        }
    }
}
