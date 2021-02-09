<?php

namespace App\Http\Bots\Telegram\Commands;

use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class ShowMyIdCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'showMyId';
    /**
     * @var string Command Description
     */
    protected $description = 'Показать мой идентификатор чата';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $text = $this->update->getChat()->get('id');

        $this->replyWithMessage(compact('text'));
    }
}
