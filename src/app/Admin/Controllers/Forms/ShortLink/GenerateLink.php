<?php

namespace App\Admin\Controllers\Forms\ShortLink;

use App\Models\Enum\OrderMethod;
use Encore\Admin\Widgets\StepForm;
use Illuminate\Http\Request;

class GenerateLink extends StepForm
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Генератор ссылок';

    /**
     * The description of form.
     *
     * @var string
     */
    public $description = 'Генератор ссылок';

    /**
     * Handle the form request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        return $this->next($request->all());
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->url('init_link', 'Исходная ссылка')
            ->required()
            ->placeholder('https://barocco.by...')
            ->rules(['url', 'required', 'starts_with:https://barocco.by']);

        $orderMethods = OrderMethod::getOptionsForSelect();
        unset(
            $orderMethods[OrderMethod::UNDEFINED],
            $orderMethods[OrderMethod::PHONE],
        );
        $this->select('source', 'Источник заказа')
            ->options($orderMethods);

        $this->text('out_link', 'Сгенерированная ссылка')
            ->required()
            ->placeholder('Сгенерированная ссылка')
            ->rules(['url', 'required', 'starts_with:https://barocco.by'])
            ->setScript($this->getScript());
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        //
    }

    /**
     * Js code
     */
    protected function getScript(): string
    {
        $authUserLogin = auth()->user()->username;
        $currentDate = date('ymd');

        $utms = [];
        foreach (OrderMethod::getValues() as $value) {
            $utms[$value] = OrderMethod::getUtmSources($value);
        }
        $utms = json_encode($utms);

        return <<<JS
        let state = { initLinkInput: '', sourceSelect: null };
        const utms = $utms;
        const adminLogin = '$authUserLogin';
        const currentDate = '$currentDate';

        const outLinkInput = document.querySelector('input[name="out_link"]');
        const generateLink = function (state) {
            try {
                const url = new URL(state.initLinkInput);
                const utm = utms[state.sourceSelect] ?? null;

                url.searchParams.delete('utm_source');
                url.searchParams.delete('utm_medium');
                url.searchParams.delete('utm_campaign');
                url.searchParams.delete('utm_content');
                url.searchParams.delete('utm_term');

                if (utm) {
                    url.searchParams.append('utm_source', utm[0]);
                    url.searchParams.append('utm_medium', utm[1]);
                    url.searchParams.append('utm_campaign', utm[2]);
                }
                url.searchParams.append('utm_content', adminLogin);
                url.searchParams.append('utm_term', currentDate);

                outLinkInput.value = url.href;
            } catch (error) {
                return;
            }
        }

        const initLinkInput = document.querySelector('input[name="init_link"]');
        initLinkInput.addEventListener('paste', (event) => {
            state.initLinkInput = (event.clipboardData || window.clipboardData).getData('text');
            generateLink(state);
        });
        initLinkInput.addEventListener('keyup', (event) => {
            if (!event.key || (event.key.length === 1 && event.keyCode >= 48 && event.keyCode <= 90) || event.keyCode === 8 || event.keyCode === 46) {
                state.initLinkInput = event.target.value;
                generateLink(state);
            }
        });

        const sourceSelect = document.querySelector('select[name="source"]');
        sourceSelect.onchange = (event) => {
            state.sourceSelect = event.target.value;
            generateLink(state);
        };
        JS;
    }

    /**
     * Redefinition addFooter function
     *
     * @return void
     */
    protected function addFooter()
    {
        $this->html('<button class="btn btn-info pull-right">Сгенерировать</button>');
    }
}
