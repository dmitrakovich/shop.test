<?php

namespace App\Http\Bots\Telegram\Commands;

use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class GitPullCommand extends Command
{
    protected $sitesList = [
        'https://bellavka.ru',
        'https://bellavka.by',
        'http://bellavka.it',
    ];
    /**
     * @var string Command Name
     */
    protected $name = 'pull';
    /**
     * @var string Command Description
     */
    protected $description = 'Получить ссылки для деплоя';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $text = '';
        $route = str_replace(request()->root(), '', route('deploy'));

        foreach ($this->sitesList as $site) {
            $text .= $site . $route . PHP_EOL;
        }

        $this->replyWithMessage(compact('text'));
    }
}
