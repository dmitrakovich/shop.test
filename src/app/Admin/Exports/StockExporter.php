<?php

namespace App\Admin\Exports;

use Encore\Admin\Grid;
use Encore\Admin\Grid\Row;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class StockExporter extends ExcelExporterFromCollection implements WithDrawings, WithEvents
{
    /**
     * @var string
     */
    protected $fileName = 'Склад.xlsx';

    /**
     * @var string|null Temporary directory path to clean up
     */
    protected ?string $tempDir = null;

    /**
     * Create a new exporter instance.
     */
    public function __construct(?Grid $grid = null)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(60);

        parent::__construct($grid);
    }

    /**
     * Prepare rows for excel
     */
    public function prepareRows(): Collection
    {
        $this->grid->build();
        $columns = $this->grid->visibleColumnNames();

        return $this->grid->rows()->map(function (Row $row) use ($columns) {
            return array_map(fn($name) => $this->prepareRow($name, $row), $columns);
        });
    }

    /**
     * Prepare a data row for the specified column.
     */
    private function prepareRow(string $columnName, Row $row): mixed
    {
        if ($columnName === 'media') {
            return null;
        }
        $data = $row->column($columnName);
        if (str_starts_with($columnName, 'stock_')) {
            $data = strip_tags($data);
        }

        return $data;
    }

    /**
     * Setup height, wedth, color & other styles
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $columnCount = $this->grid->getColumns()->count();
                $rowCount = $this->grid->rows()->count() + 1;
                $maxColumnLetter = Coordinate::stringFromColumnIndex($columnCount);

                $sheet = $event->sheet->getDelegate();

                $sheet->freezePane('A2');

                $titleStyles = $sheet->getStyle(1);
                $titleStyles->getFont()->setBold(true)->setSize(12);

                $borderStyle = $sheet->getStyle("A1:{$maxColumnLetter}{$rowCount}")->getBorders();
                $borderStyle->getAllBorders()->setBorderStyle(Style\Border::BORDER_THIN);

                $alignmentStyle = $sheet->getStyle("A1:{$maxColumnLetter}{$rowCount}")->getAlignment();
                $alignmentStyle->setVertical(Style\Alignment::VERTICAL_CENTER);

                foreach ($sheet->getRowIterator(2) as $row) {
                    $sheet->getRowDimension($row->getRowIndex())->setRowHeight(82);
                }

                $sheet->getColumnDimension('A')->setWidth(16); // фото
                $sheet->getColumnDimension('B')->setWidth(22); // название
                $sheet->getColumnDimension('C')->setWidth(27); // артикул
                for ($i = 4; $i < $columnCount - 4; $i++) {
                    $sheet->getColumnDimensionByColumn($i)->setWidth(20); // склады
                }
                $sheet->getColumnDimensionByColumn($columnCount - 3)->setWidth(25); // размеры на сайте
                $sheet->getColumnDimensionByColumn($columnCount - 2)->setWidth(14); // цена в 1С
                $sheet->getColumnDimensionByColumn($columnCount - 1)->setWidth(14); // цена на сайте
                $sheet->getColumnDimensionByColumn($columnCount)->setWidth(10); // скидка

                $this->grid->rows()->map(function (Row $row) use ($maxColumnLetter, $sheet) {
                    $attr = $row->getRowAttributes();
                    if (($pos = strpos($attr, 'background-color: #')) !== false) {
                        $rowNumber = $row->number + 2;
                        $rgb = substr($attr, $pos + 19, 6);
                        $sheet->getStyle("A{$rowNumber}:{$maxColumnLetter}{$rowNumber}")
                            ->getFill()
                            ->setFillType(Style\Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB($rgb);
                    }
                });
            },
        ];
    }

    /**
     * Вставка изображений товаров
     *
     * @return BaseDrawing[]
     */
    public function drawings()
    {
        $images = [];
        $noImagePath = public_path('images/no-image-100.png');
        $this->tempDir = storage_path('app/temp/' . now()->format('Y_m_d_H_i_s'));

        // Убеждаемся, что временный каталог существует
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }

        $this->grid->rows()->map(function (Row $row) use (&$images, $noImagePath) {
            $imgHtml = $row->column('media');
            dd($imgHtml);
            if (str_contains($imgHtml, 'no-image-100')) {
                $imagePath = $noImagePath;
            } else {
                // Пытаемся извлечь URL из HTML (может быть в атрибуте src)
                $imageUrl = $this->extractImageUrl($imgHtml);

                if (!$imageUrl) {
                    $imagePath = $noImagePath;
                } elseif (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
                    // URL S3 - скачивание во временный файл
                    $imagePath = $this->downloadImageFromUrl($imageUrl, $this->tempDir, $row->number);
                    if (!$imagePath) {
                        $imagePath = $noImagePath;
                    }
                } else {
                    // Локальный путь
                    $imagePath = public_path($imageUrl);
                    if (!file_exists($imagePath)) {
                        $imagePath = $noImagePath;
                    }
                }
            }

            $drawing = new Drawing();
            $drawing->setPath($imagePath);
            $drawing->setOffsetX(6)->setOffsetY(5);
            $drawing->setCoordinates('A' . ($row->number + 2));

            $images[] = $drawing;
        });

        return $images;
    }

    /**
     * Извлечение URL изображения из HTML
     */
    private function extractImageUrl(string $html): ?string
    {
        // Пытаемся найти атрибут src
        if (preg_match('/src=["\']([^"\']+)["\']/', $html, $matches)) {
            return $matches[1];
        }

        // Резервное решение: пытаемся найти шаблон media/products/ (старый формат)
        $start = strpos($html, 'media/products/');
        if ($start !== false) {
            $end = strpos($html, "'", $start);
            if ($end === false) {
                $end = strpos($html, '"', $start);
            }
            if ($end !== false) {
                return substr($html, $start, $end - $start);
            }
        }

        return null;
    }

    /**
     * Скачивание изображения из URL в временный файл
     */
    private function downloadImageFromUrl(string $url, string $tempDir, int $rowNumber): ?string
    {
        try {
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $tempFile = $tempDir . '/stock_export_' . $rowNumber . '_' . uniqid() . '.' . $extension;

            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                file_put_contents($tempFile, $response->body());

                return $tempFile;
            }
        } catch (\Exception $e) {
            // Логирование ошибки, если нужно, но продолжаем с резервным изображением
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        try {
            $this->download($this->fileName)->prepare(request())->send();
        } finally {
            // Очистка временных файлов после генерации Excel и отправки
            $this->cleanupTempFiles();
        }

        exit;
    }

    /**
     * Очистка временного каталога
     */
    private function cleanupTempFiles(): void
    {
        if ($this->tempDir && is_dir($this->tempDir)) {
            $this->deleteDirectory($this->tempDir);
        }
        $this->tempDir = null;
    }

    /**
     * Рекурсивное удаление каталога
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }

        return @rmdir($dir);
    }
}
