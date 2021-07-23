<?php

namespace App\Http\Bots\Telegram\Commands;

use App\Facades\CurrencyFacade;
use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class CurrenciesCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'currencies';
    /**
     * @var string Command Description
     */
    protected $description = 'Получить курсы валют с bellavka.ru';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $text = '';
        foreach (CurrencyFacade::list() as $currency) {
            $text .= "$currency->code => $currency->rate ($currency->updated_at) \n";
        }

        $this->replyWithMessage(compact('text'));
    }
}
