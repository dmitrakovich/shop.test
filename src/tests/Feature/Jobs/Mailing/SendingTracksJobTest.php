<?php

namespace Tests\Feature\Jobs\Mailing;

use App\Enums\Config\ConfigKey;
use App\Enums\DeliveryTypeEnum;
use App\Enums\Order\OrderItemStatus;
use App\Enums\Order\OrderStatus;
use App\Jobs\Mailing\SendingTracksJob;
use App\Models\Config;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Product;
use App\Models\Size;
use App\Models\Stock;
use App\Notifications\SendingTracksSms;
use App\ValueObjects\Phone;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendingTracksJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::query()->updateOrCreate(
            ['key' => ConfigKey::SendingTracks],
            ['config' => ['active' => true, 'ignore_cities' => []]],
        );
    }

    public function test_sends_sms_for_order_sent_for_fitting_today(): void
    {
        $this->travelTo(now()->setTime(18, 20));

        $order = $this->createShippedOrder(now()->subHours(4));

        Notification::fake();

        (new SendingTracksJob())->handle();

        Notification::assertSentTo($order, SendingTracksSms::class);
    }

    public function test_does_not_send_sms_for_order_sent_yesterday(): void
    {
        $this->travelTo(now()->setTime(18, 20));

        $order = $this->createShippedOrder(now()->subDay()->setTime(14, 0));

        Notification::fake();

        (new SendingTracksJob())->handle();

        Notification::assertNotSentTo($order, SendingTracksSms::class);
    }

    public function test_job_is_scheduled_at_18_20(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($scheduledEvent): bool => str_contains($scheduledEvent->description, SendingTracksJob::class));

        $this->assertNotNull($event);

        $this->travelTo(now()->setTime(18, 20));
        $this->assertTrue($event->isDue($this->app));

        $this->travelTo(now()->setTime(10, 15));
        $this->assertFalse($event->isDue($this->app));
    }

    private function createShippedOrder(Carbon $sendedAt): Order
    {
        $order = Order::withoutEvents(fn (): Order => Order::query()->create([
            'first_name' => 'Анна',
            'phone' => Phone::fromRawString('+375291112244'),
            'total_price' => 100,
            'currency' => 'BYN',
            'rate' => 1,
            'city' => 'Минск',
            'status' => OrderStatus::FITTING,
            'status_updated_at' => $sendedAt,
            'created_at' => $sendedAt,
        ]));

        $item = OrderItem::withoutEvents(fn (): OrderItem => $order->items()->create([
            'product_id' => Product::factory()->create()->id,
            'size_id' => Size::query()->value('id') ?? Size::factory()->create()->id,
            'count' => 1,
            'buy_price' => 50,
            'price' => 100,
            'old_price' => 100,
            'current_price' => 100,
            'discount' => 0,
            'status' => OrderItemStatus::FITTING,
            'status_updated_at' => $sendedAt,
        ]));

        $item->statusLog()->create([
            'stock_id' => Stock::query()->value('id') ?? Stock::factory()->create()->id,
            'sended_at' => $sendedAt,
            'created_at' => $sendedAt,
        ]);

        $order->track()->create([
            'track_number' => 'BY' . $order->id . 'TEST',
            'delivery_type_enum' => DeliveryTypeEnum::BELPOST,
        ]);

        return $order->fresh(['track']) ?? $order;
    }
}
