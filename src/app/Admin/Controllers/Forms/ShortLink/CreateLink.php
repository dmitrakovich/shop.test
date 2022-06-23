<?php

namespace App\Admin\Controllers\Forms\ShortLink;

use App\Models\ShortLink;
use Illuminate\Http\Request;
use Encore\Admin\Widgets\StepForm;

class CreateLink extends StepForm
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Короткая ссылка';

    /**
     * The description of form.
     *
     * @var string
     */
    public $description = 'Короткая ссылка';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->url('short_link', 'Короткая ссылка')->readonly();
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        $fullLink = $this->all()['generate']['out_link'];
        $shortLink = ShortLink::createShortLink($fullLink);

        return [
            'short_link' => route('short-link', $shortLink, true)
        ];
    }

    protected function addFooter()
    {
        $newLinkUrl = route('admin.short-link');

        $footer = <<<HTML
        <a href="{$newLinkUrl}" class="btn btn-warning pull-left">Сгенерировать новую ссылку</a>
        <button type="button" class="btn btn-info pull-right js-copy-btn" onclick="copyShortUrl()">Скопировать</button>
        <script>
            function copyShortUrl() {
                const inputWithUrl = document.querySelector('input#short_link');
                navigator.clipboard.writeText(inputWithUrl.value);
                toastr.success('Ссылка скопирована!', null, {timeOut: 1500, progressBar: false});
            }
        </script>
        HTML;

        $this->html($footer);
    }
}
