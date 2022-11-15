<?php

namespace App\Admin\Controllers\Forms\ShortLink;

use App\Http\Controllers\Controller;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\MultipleSteps;

class ShortLinkController extends Controller
{
    public function __invoke(Content $content)
    {
        $steps = [
            'generate' => GenerateLink::class,
            'create' => CreateLink::class,
        ];

        return $content
            ->title('Генератор коротких ссылок')
            ->body(MultipleSteps::make($steps));
    }
}
