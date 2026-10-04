<?php

namespace Tests\Feature\Metrics;

use App\Enums\Order\OrderStatus;
use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Jobs\AvailableSizes\UpdateAvailabilityJob;
use App\Jobs\AvailableSizes\UpdateAvailableSizesFullTableJob;
use App\Jobs\AvailableSizes\UpdateAvailableSizesTableJob;
use App\Jobs\Payment\SendInstallmentNoticeJob;
use App\Metrics\ApplicationMetrics;
use App\Models\Currency;
use App\Models\Feeds\AbstractFeed;
use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;
use App\Services\Elasticsearch\CatalogIndexer;
use App\Services\Feeds\CsvService;
use App\Services\LogService;
use App\Services\Payment\InstallmentService;
use App\Services\Payment\Methods\AbstractPaymentService;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ApplicationMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_duration_is_recorded_when_a_job_runs(): void
    {
        $this->mock(InstallmentService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sendNotifications')->once()->andReturn(1);
        });
        $metrics = $this->mock(ApplicationMetrics::class);
        $metrics->shouldReceive('jobDuration')
            ->once()
            ->with('SendInstallmentNoticeJob', Mockery::on(fn (float $milliseconds): bool => $milliseconds >= 0));

        SendInstallmentNoticeJob::dispatchSync();
    }

    public function test_catalog_sync_records_rows_received_from_1c(): void
    {
        $metrics = $this->mock(ApplicationMetrics::class);
        $metrics->shouldReceive('oneCRows')->once()->with(0);

        $job = new class extends UpdateAvailableSizesTableJob
        {
            /**
             * @return list<array<string, mixed>>
             */
            protected function getAvailableSizesFrom1C(): array
            {
                return [];
            }

            protected function writeAvailableSizes(array $availableSizes): void {}
        };
        $job->handle();
    }

    public function test_full_table_sync_does_not_record_catalog_rows(): void
    {
        $metrics = $this->mock(ApplicationMetrics::class);
        $metrics->shouldReceive('oneCRows')->never();

        $job = new class extends UpdateAvailableSizesFullTableJob
        {
            /**
             * @return list<array<string, mixed>>
             */
            protected function getAvailableSizesFrom1C(): array
            {
                return [];
            }

            protected function writeAvailableSizes(array $availableSizes): void {}
        };
        $job->handle();
    }

    public function test_availability_update_records_publish_and_size_counts(): void
    {
        Bus::fake([UpdateAvailableSizesTableJob::class]);
        $this->mock(CatalogIndexer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('syncProductIds')->once();
        });
        $metrics = $this->mock(ApplicationMetrics::class);
        $metrics->shouldReceive('productsUnpublished')->once()->with(2);
        $metrics->shouldReceive('sizesAdded')->once()->with(3);
        $metrics->shouldReceive('sizesRemoved')->once()->with(4);
        $metrics->shouldReceive('productsPublished')->once()->with(5);

        $job = new class(app(LogService::class)) extends UpdateAvailabilityJob
        {
            public function __construct(LogService $logService)
            {
                parent::__construct($logService);
            }

            public function updateProductsOneCIdFromAvailableSizes(): void {}

            protected function deleteUnavailableProducts(): int
            {
                return 2;
            }

            /**
             * @return array{int, int}
             */
            protected function updateSizes(): array
            {
                return [3, 4];
            }

            protected function restoreProducts(): int
            {
                return 5;
            }

            protected function writeLog(): void {}
        };
        $job->handle();
    }

    public function test_feed_generation_records_duration_size_and_memory(): void
    {
        $currency = Currency::query()->firstOrFail();
        $feed = new class extends AbstractFeed
        {
            const FILE_TYPE = 'csv';

            public function getKey(): string
            {
                return 'sentry_metric_test';
            }

            public function getPreparedData(): object
            {
                return (object)['headers' => ['id'], 'rows' => [[1]]];
            }
        };
        $path = storage_path('app/xml/sentry_metric_test_' . strtolower($currency->code) . '.csv');
        File::ensureDirectoryExists(dirname($path));
        $this->beforeApplicationDestroyed(fn () => File::delete($path));

        $metrics = $this->mock(ApplicationMetrics::class);
        $metrics->shouldReceive('feedGenerated')
            ->once()
            ->with(
                'sentry_metric_test',
                'csv',
                $currency->code,
                Mockery::type('int'),
                Mockery::type('int'),
                Mockery::type('float'),
            );

        (new CsvService($feed, $currency))->generate();
    }

    public function test_payment_status_change_is_counted_once(): void
    {
        $payment = $this->createPayment(OnlinePaymentMethodEnum::YANDEX, OnlinePaymentStatusEnum::PENDING);
        $metrics = $this->mock(ApplicationMetrics::class);
        $metrics->shouldReceive('paymentStatus')
            ->once()
            ->with(OnlinePaymentStatusEnum::SUCCEEDED, OnlinePaymentMethodEnum::YANDEX);

        $service = new class extends AbstractPaymentService
        {
            /**
             * @param  array<string, mixed>  $data
             */
            public function create(Order $order, float $amount, ?string $paymentNum = null, array $data = []): ?OnlinePayment
            {
                return null;
            }
        };
        $service->setPaymentStatus($payment, OnlinePaymentStatusEnum::SUCCEEDED);
        $service->setPaymentStatus($payment, OnlinePaymentStatusEnum::SUCCEEDED);
    }

    public function test_cod_payment_counts_succeeded_when_it_is_created(): void
    {
        $metrics = $this->mock(ApplicationMetrics::class);
        $metrics->shouldReceive('paymentStatus')
            ->once()
            ->with(OnlinePaymentStatusEnum::SUCCEEDED, OnlinePaymentMethodEnum::COD);

        $this->createPayment(OnlinePaymentMethodEnum::COD);
    }

    public function test_pending_payment_status_is_not_sent(): void
    {
        $metrics = Mockery::mock(ApplicationMetrics::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $metrics->shouldReceive('count')->never();

        $metrics->paymentStatus(OnlinePaymentStatusEnum::PENDING, OnlinePaymentMethodEnum::YANDEX);
        $metrics->paymentStatus(null, OnlinePaymentMethodEnum::YANDEX);
    }

    private function createPayment(
        OnlinePaymentMethodEnum $method,
        ?OnlinePaymentStatusEnum $status = null,
    ): OnlinePayment {
        $order = Order::query()->create([
            'first_name' => 'Тест',
            'phone' => Phone::fromRawString('+375291112233'),
            'total_price' => 100,
            'currency' => 'BYN',
            'rate' => 1,
            'status' => OrderStatus::NEW,
            'status_updated_at' => now(),
        ]);

        $attributes = [
            'order_id' => $order->id,
            'method_enum_id' => $method,
            'currency_value' => 1,
            'amount' => 100,
        ];

        if ($status === null) {
            return OnlinePayment::query()->create($attributes);
        }

        return OnlinePayment::withoutEvents(fn (): OnlinePayment => OnlinePayment::query()->create([
            ...$attributes,
            'last_status_enum_id' => $status,
        ]));
    }
}
