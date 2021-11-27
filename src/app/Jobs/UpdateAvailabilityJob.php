<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Size;
use App\Services\Api\YandexApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdateAvailabilityJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Ручной способ обновления
     *
     * @var boolean
     */
    protected $isManual = false;
    protected $thtime = null;

    /**
     * Products on site
     *
     * @var array
     */
    protected $allProducts = [];

    // варианты букв в артикулах
    protected static $engSymbols = ['a', 'b', 'c', 'e', 'h', 'k', 'm', 'o', 'p', 't', 'x'];
    protected static $rusSymbols = ['а', 'в', 'с', 'е', 'н', 'к', 'м', 'о', 'р', 'т', 'х'];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(bool $manual = false)
    {
        $this->isManual = $manual;
        $this->thtime = date("Y-m-d-H:i:s");
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(YandexApiService $yandexApiService)
    {
        $this->debug('Старт');

        $availabilityConfig = $this->getConfig();

        if (!$this->isManual && $availabilityConfig['auto_del'] == 'off') {
            return $this->errorWithReturn('Автоматическое обновление выключено!');
        }

        $currentProducts = Product::leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->withTrashed()
            ->with(['sizes:id,name'])
            ->get([
                'products.id',
                'brand_id',
                'brands.name as brand',
                'category_id',
                'title as name',
                'deleted_at',
                'label_id as label',
            ]);
        $sizesList = Size::pluck('id', 'name')->toArray();

        foreach ($currentProducts as $product) {
            $brandName = trim($product->brand);
            if (!empty($brandName)) {
                $this->allProducts[strtolower($brandName)][$this->smallArt($product->name)] = [
                    'id' => $product->id,
                    'cat_id' => $product->category_id,
                    'status' => (int)!$product->trashed(),
                    'articul' => $product->name,
                    'brand' => $brandName,
                    'size' => $product->sizes->pluck('id', 'name')->toArray(),
                    'label' => $product->label
                ];
            }
        }
        unset($currentProducts);

        $fileInfo = $yandexApiService->getLeftoversFileInfo();
        if (empty($fileInfo)) {
            return $this->errorWithReturn('Ошибка! Яндекс Диск не отдал данные о файле.');
        } elseif (isset($fileInfo['error'])) {
            return $this->errorWithReturn('Ошибка получения данных. ' . ($fileInfo['message'] ?? $fileInfo['error']));
        }
        $filedate = explode(',', $availabilityConfig['file']);
        $actionsCount = count($availabilityConfig['publish']);
            + count($availabilityConfig['add_size'])
            + count($availabilityConfig['del'])
            + count($availabilityConfig['del_size'])
            + count($availabilityConfig['new']);
        if ($fileInfo['md5'] == $filedate[1] && $actionsCount > 0 && !isset($_POST['act'])) {
            return $this->errorWithReturn('Файл не обновлялся.');
        }
        $availabilityConfig['file'] = date('Y-m-d-H:i:s', strtotime($fileInfo['modified'])) . ',' . $fileInfo['md5'];

        $downloadLink = $yandexApiService->getLeftoversDownloadLink();
        if (empty($downloadLink)) {
            return $this->errorWithReturn('Ошибка! Яндекс Диск не получил ссылку на скачивание.');
        }

        $resI = file_get_contents($downloadLink['href']);
        $resI = mb_convert_encoding($resI, "UTF-8", "windows-1251");
        $resI = explode("\n", $resI);
        $resD = [];
        for ($i = 5; $i < count($resI); $i++) {
            if (mb_strpos($resI[$i], ' | ') !== false) {
                $itemA = explode("\t", $resI[$i]);
                $itemC = explode(' | ', $itemA[2]);
                $brandName = trim($itemA[3]);
                $brandKey = strtolower($brandName);
                $smallArt = $this->smallArt($itemC[0]);
                $sizeNotNull = $itemA[5] =! 0;

                if (!empty($brandName) && !empty($smallArt) && $sizeNotNull) {

                    $smallArt = $this->searchVendorCode($smallArt, $brandKey);

                    if (!isset($resD[$brandKey][$smallArt])) {
                        $resD[$brandKey][$smallArt] = [
                            'articul' => $itemC[0],
                            'brand' => $brandName,
                            'size' => $itemA[4] == 'б/р' ? ['без размера' => 'без размера'] : [$itemA[4] => $itemA[4]],
                            'cat' => $itemC[1],
                            'price' => str_replace("'00", "", $itemA[6])
                        ];
                    } elseif (is_array($resD[$brandKey][$smallArt]['size'])) {
                        $resD[$brandKey][$smallArt]['size'][$itemA[4]] = $itemA[4];
                    }
                    // общий список
                    if (!isset($this->allProducts[$brandKey][$smallArt])) {
                        $this->allProducts[$brandKey][$smallArt] = 'new';
                    }
                }
            }
        }
        unset($resI);
        // Сброс
        $deadline = date("Y-m-d-H:i:s", mktime(date("H"), date("i"), date("s"), date("n"), date("j") - $availabilityConfig['period'], date("Y")));
        $sbros = array('publish', 'new', 'add_size', 'del', 'del_size');
        foreach ($sbros as $sbrosv) {
            if ($availabilityConfig['auto_del'] == 'on' && $sbrosv == 'publish') {
                foreach ($availabilityConfig[$sbrosv] as $sbrK => $sbrV) if ($sbrV['status'] == 0 || $sbrV['time'] < $deadline) unset($availabilityConfig[$sbrosv][$sbrK]);
            } elseif ($availabilityConfig['auto_del'] == 'off' || $sbrosv == 'new') {
                $availabilityConfig[$sbrosv] = array();
            } else foreach ($availabilityConfig[$sbrosv] as $sbrK => $sbrV) if (!isset($sbrV['time']) || $sbrV['time'] < $deadline) unset($availabilityConfig[$sbrosv][$sbrK]);
        }
        // Сравнение
        foreach ($this->allProducts as $brandKey => $brandProducts) {
            foreach ($brandProducts as $smallArt => $product) {
                if ($product === 'new') {
                    continue;
                }
                $checkIgn = $this->allProducts[$brandKey][$smallArt]['label'] != 3;
                $checkNoM = (!isset($resD[$brandKey][$smallArt]) || (stripos($resD[$brandKey][$smallArt]['cat'], "мужск")) === false);
                if ($checkIgn && $checkNoM) {
                    if (
                        isset($this->allProducts[$brandKey][$smallArt])
                        && $this->allProducts[$brandKey][$smallArt]['status'] != 1
                        && isset($resD[$brandKey][$smallArt])
                    ) {
                        $it = $this->allProducts[$brandKey][$smallArt];
                        $availabilityConfig['publish'][] = [
                            'id' => $it['id'],
                            'name' => "$it[brand] $it[articul]",
                            'status' => 0,
                            'time' => $this->thtime
                        ];
                    }
                    if (
                        isset($this->allProducts[$brandKey][$smallArt])
                        && $this->allProducts[$brandKey][$smallArt]['status'] == 1
                        && !isset($resD[$brandKey][$smallArt])
                    ) {
                        $it = $this->allProducts[$brandKey][$smallArt];
                        $availabilityConfig['del'][] = [
                            'id' => $it['id'],
                            'name' => "$it[brand] $it[articul]",
                            'status' => 0,
                            'time' => $this->thtime
                        ];
                    }
                    if (
                        isset($this->allProducts[$brandKey][$smallArt])
                        && $this->allProducts[$brandKey][$smallArt] == 'new'
                    ) {
                        $it = $resD[$brandKey][$smallArt];
                        $it_err = '';
                        if (!isset($this->allProducts[$brandKey])) $it_err = 'нет бренда';
                        if (is_array($it['size'])) {
                            $it_err_s = '';
                            foreach ($it['size'] as $err_s) if (!isset($sizesList[$err_s])) $it_err_s = 'нет размера';
                            $it_err .= ((!empty($it_err) && !empty($it_err_s)) ? ', ' : '') . ((!empty($it_err_s)) ? $it_err_s : '');
                        }
                        $availabilityConfig['new'][$brandKey . '-' . $smallArt] = array(
                            'id' => $brandKey . '-' . $smallArt,
                            'brand' => $it['brand'],
                            'articul' => $it['articul'],
                            'cat' => $it['cat'],
                            'size' => $it['size'],
                            'err' => $it_err,
                            'time' => $this->thtime
                        );
                    }
                    // размеры
                    if (
                        isset($this->allProducts[$brandKey][$smallArt])
                        && isset($resD[$brandKey][$smallArt])
                        && array_keys($this->allProducts[$brandKey][$smallArt]['size']) != array_keys($resD[$brandKey][$smallArt]['size'])
                    ) {
                        $it = $this->allProducts[$brandKey][$smallArt];
                        $ds = $resD[$brandKey][$smallArt]['size'];
                        $ss = $this->allProducts[$brandKey][$smallArt]['size'];
                        $fs = $ss + $ds;
                        foreach ($fs as $fsk => $fsv) {
                            if (!isset($ss[$fsk]) && isset($ds[$fsk])) {
                                if (!isset($sizesList[$fsk])) $sizesList[$fsk] = 'new';
                                $availabilityConfig['add_size'][] = [
                                    'id' => $it['id'],
                                    'vid' => $sizesList[$fsk],
                                    'name' => "$it[brand] $it[articul]",
                                    'size' => $fsk,
                                    'status' => 0,
                                    'time' => $this->thtime
                                ];
                            } elseif (isset($ss[$fsk]) && !isset($ds[$fsk])) {
                                $availabilityConfig['del_size'][] = [
                                    'id' => $it['id'],
                                    'vid' => $ss[$fsk],
                                    'name' => "$it[brand]  $it[articul]",
                                    'size' => $fsk,
                                    'status' => 0,
                                    'time' => $this->thtime
                                ];
                            }
                        }
                    }
                }
            };
        };
        $checkLog = 0;
        $availabilityConfig['last_update'] = $this->thtime;
        $filedate = explode(",", $availabilityConfig['file']);
        $service_message = "Файл $filedate[0] . Наличие сверено в $availabilityConfig[last_update]";
        if ($availabilityConfig['auto_del'] == 'on') {
            $act_count = 0;
            // публикации
            if (count($availabilityConfig['publish']) > 0) {
                $act_count = 0;
                $img_list = array();
                $q_list = array();
                $imgL = array();
                foreach ($availabilityConfig['publish'] as $actV) {
                    if ($actV['status'] != 1) {
                        $img_list[] = $actV['id'];
                    } else {
                        $imgL[] = $actV['id'];
                    }
                }

                if (count($img_list) > 0) {
                    $imgFL = Product::whereIn('id', $img_list)
                        ->whereHas('media')
                        ->get('id as pid');

                    foreach ($imgFL as $imgV) {
                        if (!in_array($imgV->pid, $imgL)) $imgL[] = $imgV->pid;
                    }
                    foreach ($availabilityConfig['publish'] as $errK => $errV) {
                        if (!in_array($errV['id'], $imgL)) {
                            $availabilityConfig['publish'][$errK]['err'] = 'нет фото';
                        } elseif ($availabilityConfig['publish'][$errK]['status'] != 1) {
                            $q_list[] = $errV['id'];
                            $availabilityConfig['publish'][$errK]['status'] = 1;
                            $act_count++;
                        }
                    }
                }

                if (count($q_list) > 0) {
                    Product::withTrashed()->whereIn('id', $imgL)->restore();
                    $service_message .= ". Опубликовано $act_count";
                }
                $checkLog += $act_count;
            }
            // снятие с публикации
            if (count($availabilityConfig['del']) > 0) {
                $act_count = 0;
                $q_list = array();
                foreach ($availabilityConfig['del'] as $actK => $actV) {
                    if ($availabilityConfig['del'][$actK]['status'] != 1) {
                        $q_list[] = $actV['id'];
                        $availabilityConfig['del'][$actK]['status'] = 1;
                        $act_count++;
                    }
                }
                if ($act_count > 50) {
                    $service_message .= ". !!! Ошибка. Больше 50 снять с публикации";
                    $act_count = 0;
                } elseif ($act_count > 0) {
                    Product::whereIn('id', $q_list)->delete();
                    $service_message .= ". Снято с публикации $act_count";
                }
                $checkLog += $act_count;
            }
            // удаление размеров
            if (count($availabilityConfig['del_size']) > 0) {
                $act_count = 0;
                $q_list = array();
                foreach ($availabilityConfig['del_size'] as &$value) {
                    if ($value['status'] != 1) {
                        $q_list[$value['id']][] = $value['size'];
                        $value['status'] = 1;
                        $act_count++;
                    }
                }
                unset($value);
                if ($act_count > 1000) {
                    $service_message .= ". !!! Ошибка. Больше 100 удалить размеров";
                    $act_count = 0;
                } elseif ($act_count > 0) {
                    foreach ($q_list as $productId => $product) {
                        foreach ($product as $sizeName) {
                            if (!isset($sizesList[$sizeName])) {
                                continue;
                            }
                            DB::table('product_attributes')
                                ->where('attribute_type', 'App\Models\Size')
                                ->where('product_id', $productId)
                                ->where('attribute_id', $sizesList[$sizeName])
                                ->delete();
                        }
                    }
                    $service_message .= ". Удалено размеров $act_count";
                }
                $checkLog += $act_count;
            }
            // добавление размеров
            if (count($availabilityConfig['add_size']) > 0) {
                $act_count = 0;
                $insertData = [];
                foreach ($availabilityConfig['add_size'] as $actK => $actV) {
                    if ($availabilityConfig['add_size'][$actK]['status'] != 1 && $actV['vid'] != 'new') {
                        $insertData[] = [
                            'product_id' => $actV['id'],
                            'attribute_type' => 'App\Models\Size',
                            'attribute_id' => $actV['vid'],
                        ];
                        $availabilityConfig['add_size'][$actK]['status'] = 1;
                        $act_count++;
                    }
                }
                if ($act_count > 0) {
                    DB::table('product_attributes')->insert($insertData);
                    $service_message .= ". Добавлено размеров $act_count";
                }
                $checkLog += $act_count;
            }

            // Запись в log
            if ($checkLog > 0) {
                $this->writeLog($service_message);
            }
        } else {
            $this->writeLog($service_message);
        }

        if (!isset($_POST['act'])) {
            $this->saveConfig($availabilityConfig);
        }

        $this->complete('Успешно выполнено');
        return "<p class='adminka_message_success'>$service_message.</p>";
    }

    /**
     * Get availability config file name
     *
     * @return string
     */
    protected function getConfigFileName(): string
    {
        return database_path('files/availability.conf.php');
    }

    /**
     * Get availability config
     *
     * @return array
     */
    protected function getConfig(): array
    {
        $availabilityConfigFile = $this->getConfigFileName();
        if (!file_exists($availabilityConfigFile)) {
            $this->fail(new \Exception('Не найден файл конфигурации'));
        }
        return require $availabilityConfigFile;
    }

    /**
     * Save availability config
     *
     * @param array $config
     * @return void
     */
    protected function saveConfig(array $config): void
    {
        file_put_contents($this->getConfigFileName(), "<?php\nreturn " . var_export($config, true) . ';');
    }

    public function writeLog($msg)
    {
        $log_type = $this->isManual ? 'РУЧНОЕ' : 'АВТО';
        file_put_contents(database_path('files/availability.log.txt'), "{$this->thtime} [$log_type]: $msg\n", FILE_APPEND);
    }

    protected function smallArt($txt)
    {
        $r = array(' ', '-', '.', '_', '*');
        return mb_strtolower(str_replace($r, '', $txt));
    }

    /**
     * Поиск кривых артикулов
     *
     * @param string $vendorCode полученный артикул
     * @param string $brandKey код бренда
     * @return string|null найденный артикул
     */
    protected function searchVendorCode(string $vendorCode, string $brandKey): ?string
    {
        if (isset($this->allProducts[$brandKey][$vendorCode])) {
            return $vendorCode;
        }
        $vendorCodeRus = str_replace(self::$engSymbols, self::$rusSymbols, $vendorCode);
        if (isset($this->allProducts[$brandKey][$vendorCodeRus])) {
            return $vendorCodeRus;
        }
        $vendorCodeEng = str_replace(self::$rusSymbols, self::$engSymbols, $vendorCode);
        if (isset($this->allProducts[$brandKey][$vendorCodeEng])) {
            return $vendorCodeEng;
        }
        return $vendorCode;
    }

    protected function errorWithReturn(string $msg)
    {
        $this->complete($msg, 'jobs', 'error');
        return $msg;
    }
}
