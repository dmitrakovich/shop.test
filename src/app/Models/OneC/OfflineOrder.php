<?php

namespace App\Models\OneC;

use App\Admin\Models\AvailableSizesFull;
use App\Models\Product;
use App\Models\Size;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * @property string $ID
 * @property string $CODE
 * @property string $DESCR object description
 * @property string $ISMARK Flag Object is Market
 * @property string $VERSTAMP Version stamp
 * @property string $SP6089 ДисконтнаяКарта
 * @property string $SP6090 КодДК
 * @property string $SP6091 Товар
 * @property int $SP6092 КодТовара
 * @property string $SP6093 Артикул
 * @property string $SP6094 НаименованиеТовар
 * @property string $SP6095 Магазин
 * @property int $SP6096 КодМагазина
 * @property string $SP6097 ДатаПродажи
 * @property string $SP6098 НомерЧека
 * @property string $SP6099 Количество
 * @property int $SP6100 Размер
 * @property float $SP6101 Сумма
 * @property string $SP6102 Телефон
 * @property string $SP6107 ВремяПродаж
 * @property string $SP6108 ДатаВозврата
 * @property string $SP6109 ВремяВозврата
 * @property string $SP6110 НомерВозврата
 *
 * @property-read Stock|null $stock
 * @property-read Product|null $product
 * @property-read Size|null $size
 */
class OfflineOrder extends AbstractOneCModel
{
    use \Sushi\Sushi;

    // protected $guarded = ['id'];
    protected $rows = [
        [
            'ROW_ID' => '21366',
            // "ID" => "GHI001",
            'CODE' => 1,
            'DESCR' => 'NONE',
            'ISMARK' => '0',
            'VERSTAMP' => '0',
            'SP6089' => '0',
            'SP6090' => 'NONE',
            'SP6091' => '4J9001',
            'SP6092' => '7688',
            'SP6093' => '1024-68 черный',
            'SP6094' => 'Женск. кож. ботинки',
            'SP6095' => 'P009',
            'SP6096' => '1',
            'SP6097' => '2023-01-02 00:00:00.000',
            'SP6098' => '0090000001',
            'SP6099' => '1',
            'SP6100' => '37',
            'SP6101' => '197.00',
            'SP6102' => 'NONE',
            'SP6107' => '11:00:16',
            'SP6108' => '1753-01-01 00:00:00.000',
            'SP6109' => '',
            'SP6110' => '',
        ],
        [
            'ROW_ID' => '21367',
            // "ID" => "GHJ001",
            'CODE' => 2,
            'DESCR' => 'ПОНАДА И.В.',
            'ISMARK' => '0',
            'VERSTAMP' => '0',
            'SP6089' => '2JY004',
            'SP6090' => '2917502000002',
            'SP6091' => '54G001',
            'SP6092' => '6602',
            'SP6093' => 'WS7211-5-8-1P',
            'SP6094' => 'Ботинки женские',
            'SP6095' => 'P009',
            'SP6096' => '1',
            'SP6097' => '2023-01-02 00:00:00.000',
            'SP6098' => '0090000002',
            'SP6099' => '1',
            'SP6100' => '37',
            'SP6101' => '372.30',
            'SP6102' => '375292052202',
            'SP6107' => '13:50:35',
            'SP6108' => '1753-01-01 00:00:00.000',
            'SP6109' => '',
            'SP6110' => '',
        ],
        [
            'ROW_ID' => '21368',
            // "ID" => "GHK001",
            'CODE' => 3,
            'DESCR' => 'ЮРКЕВИЧ Н.А.',
            'ISMARK' => '0',
            'VERSTAMP' => '0',
            'SP6089' => '4QV004',
            'SP6090' => '2966300000004',
            'SP6091' => '5CV001',
            'SP6092' => '6905',
            'SP6093' => '342-88 бежевый',
            'SP6094' => 'Ботинки женские',
            'SP6095' => 'D',
            'SP6096' => '3',
            'SP6097' => '2023-01-02 00:00:00.000',
            'SP6098' => '0040000001',
            'SP6099' => '1',
            'SP6100' => '37',
            'SP6101' => '224.40',
            'SP6102' => '375297989829',
            'SP6107' => '13:55:04',
            'SP6108' => '1753-01-01 00:00:00.000',
            'SP6109' => '',
            'SP6110' => '',
        ],
        [
            'ROW_ID' => '21369',
            // "ID" => "GHL001",
            'CODE' => 4,
            'DESCR' => 'ДУЛЬСКАЯ И.О.',
            'ISMARK' => '0',
            'VERSTAMP' => '0',
            'SP6089' => '4CX003',
            'SP6090' => '2921546000003',
            'SP6091' => '45N001',
            'SP6092' => '5350',
            'SP6093' => 'WN8035-52-1B',
            'SP6094' => 'Ботинки женские',
            'SP6095' => 'P009',
            'SP6096' => '1',
            'SP6097' => '2023-01-02 00:00:00.000',
            'SP6098' => '0090000003',
            'SP6099' => '1',
            'SP6100' => '38',
            'SP6101' => '238.20',
            'SP6102' => '375297237978',
            'SP6107' => '14:27:58',
            'SP6108' => '1753-01-01 00:00:00.000',
            'SP6109' => '',
            'SP6110' => '',
        ],
        [
            'ROW_ID' => '21369',
            // "ID" => "GHL001",
            'CODE' => 14,
            'DESCR' => 'ДУЛЬСКАЯ И.О.',
            'ISMARK' => '0',
            'VERSTAMP' => '0',
            'SP6089' => '4CX003',
            'SP6090' => '2921546000003',
            'SP6091' => '45N001',
            'SP6092' => '5350',
            'SP6093' => 'WN8035-52-1B',
            'SP6094' => 'Ботинки женские',
            'SP6095' => 'P009',
            'SP6096' => '1',
            'SP6097' => '2023-01-02 00:00:00.000',
            'SP6098' => '0090000003',
            'SP6099' => '-1',
            'SP6100' => '38',
            'SP6101' => '-238.20',
            'SP6102' => '375297237978',
            'SP6107' => '14:27:58',
            'SP6108' => '2023-01-11 00:00:00.000',
            'SP6109' => '09:15:48',
            'SP6110' => '0010000019',
        ],
        [
            'ROW_ID' => '21370',
            // "ID" => "GHM001",
            'CODE' => 5,
            'DESCR' => 'NONE',
            'ISMARK' => '0',
            'VERSTAMP' => '0',
            'SP6089' => '0',
            'SP6090' => 'NONE',
            'SP6091' => '4NG001',
            'SP6092' => '5991',
            'SP6093' => 'AH092-M65-A1003',
            'SP6094' => 'Ботинки женские',
            'SP6095' => 'P009',
            'SP6096' => '1',
            'SP6097' => '2023-01-02 00:00:00.000',
            'SP6098' => '0090000004',
            'SP6099' => '1',
            'SP6100' => '35',
            'SP6101' => '177.30',
            'SP6102' => 'NONE',
            'SP6107' => '14:39:47',
            'SP6108' => '1753-01-01 00:00:00.000',
            'SP6109' => '',
            'SP6110' => '',
        ],
        [
            'ROW_ID' => '21389',
            // "ID" => "GI5001",
            'CODE' => 15,
            'DESCR' => 'МЕЩЕРЯКОВА О.А.                                   ',
            'ISMARK' => '0',
            'VERSTAMP' => '0',
            'SP6089' => '1WJ002',
            'SP6090' => '2915412000006',
            'SP6091' => '5B3001',
            'SP6092' => '6841',
            'SP6093' => '1705-65-1R                    ',
            'SP6094' => 'женские                                                                                      ',
            'SP6095' => 'F   ',
            'SP6096' => '5',
            'SP6097' => '2023-01-03 00:00:00.000',
            'SP6098' => '0030000003',
            'SP6099' => '1',
            'SP6100' => '40',
            'SP6101' => '376,80',
            'SP6102' => '375292000360   ',
            'SP6107' => '12:06:58',
            'SP6108' => '1753-01-01 00:00:00.000',
            'SP6109' => '',
            'SP6110' => '',
        ],
        [
            'ROW_ID' => '21389',
            // "ID" => "GI5001",
            'CODE' => 16,
            'DESCR' => 'МЕЩЕРЯКОВА О.А.                                   ',
            'ISMARK' => '0',
            'VERSTAMP' => '0',
            'SP6089' => '1WJ002',
            'SP6090' => '2915412000006',
            'SP6091' => '5B3001',
            'SP6092' => '6841',
            'SP6093' => '1705-65-1R                    ',
            'SP6094' => 'женские                                                                                      ',
            'SP6095' => 'F   ',
            'SP6096' => '5',
            'SP6097' => '2023-01-03 00:00:00.000',
            'SP6098' => '0030000003',
            'SP6099' => '-1',
            'SP6100' => '40',
            'SP6101' => '-376,80',
            'SP6102' => '375292000360   ',
            'SP6107' => '12:06:58',
            'SP6108' => '2023-01-11 00:00:00.000',
            'SP6109' => '09:15:48',
            'SP6110' => '0010000011',
        ],
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'SC6104';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'CODE' => 'integer',
        'SP6092' => 'integer',
        'SP6096' => 'integer',
        'SP6098' => 'string',
        'SP6100' => 'integer',
        'SP6101' => 'float',
    ];

    /**
     * Get the latest code by receipt number from the offline orders.
     */
    public static function getLatestCodeByReceiptNumber(?string $receiptNumber): int
    {
        return (int)self::query()->where('SP6098', $receiptNumber)->value('CODE');
    }

    /**
     * Check if the order is a return.
     */
    public function isReturn(): bool
    {
        return !empty($this->SP6109);
    }

    /**
     * Get the stock associated with the offline order.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'SP6096', 'one_c_id');
    }

    /**
     * Get the available sizes associated with the offline order.
     */
    public function availableSizes(): HasMany
    {
        return $this->hasMany(AvailableSizesFull::class, 'one_c_product_id', 'SP6092');
    }

    /**
     * Get the product associated with the offline order.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'SP6092', 'one_c_id')->withTrashed();
    }

    /**
     * Get the size associated with the offline order.
     */
    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'SP6100', 'name');
    }

    /**
     * Get the date and time when the order was sold.
     */
    public function getSoldAtDateTime(): Carbon
    {
        $date = Carbon::parse($this->SP6097);

        return $date->setTimeFromTimeString($this->SP6107);
    }

    /**
     * Get the date and time when the order was returned.
     */
    public function getReturnedAtDateTime(): Carbon
    {
        $date = Carbon::parse($this->SP6108);

        return $date->setTimeFromTimeString($this->SP6109);
    }

    /**
     * Get the formatted offline order phone number.
     */
    public function getFormattedPhone(): ?string
    {
        $phone = $this->SP6102;
        if (!$phone || strlen($phone) < 7) {
            return null;
        }
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();

            $parsedPhone = $phoneUtil->parse($phone, 'BY');
            if ($phoneUtil->isValidNumber($parsedPhone)) {
                return $phoneUtil->format($parsedPhone, PhoneNumberFormat::E164);
            }
        } catch (\Throwable $th) {
        }

        return null;
    }
}
