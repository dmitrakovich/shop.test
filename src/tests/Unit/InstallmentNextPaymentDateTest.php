<?php

namespace Tests\Unit;

use App\Models\Payments\Installment;
use App\Notifications\InstallmentPaymentSms;
use Carbon\Carbon;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Tests\TestCase;

class InstallmentNextPaymentDateTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        IlluminateCarbon::setTestNow();

        parent::tearDown();
    }

    public function test_next_payment_date_uses_contract_date_day_of_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 12:00:00'));

        $installment = new Installment;
        $installment->contract_date = Carbon::parse('2025-03-20');
        $installment->created_at = Carbon::parse('2025-01-05');

        $this->assertSame('20.07.2026', $installment->getNextPaymentDate()->format('d.m.Y'));
    }

    public function test_next_payment_date_moves_to_next_month_when_day_already_passed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-25 12:00:00'));

        $installment = new Installment;
        $installment->contract_date = Carbon::parse('2025-03-20');
        $installment->created_at = Carbon::parse('2025-01-05');

        $this->assertSame('20.08.2026', $installment->getNextPaymentDate()->format('d.m.Y'));
    }

    public function test_next_payment_date_falls_back_to_created_at_when_contract_date_is_missing(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-03 12:00:00'));

        $installment = new Installment;
        $installment->contract_date = null;
        $installment->created_at = Carbon::parse('2025-01-05');

        $this->assertSame('05.07.2026', $installment->getNextPaymentDate()->format('d.m.Y'));
    }

    public function test_sms_content_includes_payment_deadline_from_contract_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 12:00:00'));

        $installment = new Installment;
        $installment->contract_number = '100/1';
        $installment->monthly_fee = 50.0;
        $installment->contract_date = Carbon::parse('2025-03-20');
        $installment->created_at = Carbon::parse('2025-01-05');
        $installment->setRelation('order', (object)['first_name' => 'Анна']);

        $content = (new InstallmentPaymentSms($installment))->getContent();

        $this->assertStringContainsString('Оплату необходимо произвести в срок до 20.07.2026', $content);
        $this->assertStringContainsString('договору №100/1', $content);
        $this->assertStringContainsString('50 BYN', $content);
    }
}
