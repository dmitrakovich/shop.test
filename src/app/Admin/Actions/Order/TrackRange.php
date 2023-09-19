<?php

namespace App\Admin\Actions\Order;

use App\Enums\DeliveryTypeEnum;
use App\Models\Orders\OrderTrack;
use App\Services\Departures\BelpostLabelService;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class TrackRange extends Action
{
    public $name = 'Добавить диапазон';

    protected $selector = '.js-trackRange';

    /**
     * Instance of the BelpostLabelService for handling Belarus Post label operations.
     */
    private BelpostLabelService $belpostLabelService;

    /**
     * TrackRange Action constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->belpostLabelService = app(BelpostLabelService::class);
    }

    /**
     * Action hadle
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $departureSeries = $request->input('departure_series');
        $rangeStartNum = (int)$request->input('range_start_num');
        $rangeEndNum = (int)$request->input('range_end_num');
        if ($rangeEndNum < $rangeStartNum) {
            throw new \Exception('Номер начала диапазона должно быть больше номера конца диапазона');
        }
        for ($i = $rangeStartNum; $i <= $rangeEndNum; $i++) {
            $labelNumber = str_pad($i, 8, '0', STR_PAD_LEFT);
            $checkSum = $this->belpostLabelService->calculateCheckSum($labelNumber);
            OrderTrack::firstOrCreate([
                'track_number' => $departureSeries . $labelNumber . $checkSum . 'BY',
            ]);
        }

        return $this->response()->success('Диапазон трек номеров успешно создан')->refresh();
    }

    public function form()
    {
        $this->text('departure_series', 'Серия отправления')->default('PE')->rules('required');
        $this->text('range_start_num', 'Номер начала диапазона')->rules('required|regex:/^\d+$/');
        $this->text('range_end_num', 'Номер конца диапазона')->rules('required|regex:/^\d+$/');
        $this->select('delivery_type_enum', 'Тип отправки')->options(DeliveryTypeEnum::list())->default(DeliveryTypeEnum::BELPOST->value)->required();
    }

    /**
     * Html installment form
     */
    public function html(): string
    {
        return <<<HTML
        <div class="btn-group pull-right" style="margin-right: 5px">
            <a target="_blank" class="js-trackRange btn btn-sm btn-default" >
                $this->name
            </a>
        </div>
        HTML;
    }
}
