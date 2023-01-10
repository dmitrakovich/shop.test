<?php

namespace App\Helpers;

use Encore\Admin\Facades\Admin;
use Illuminate\Notifications\Facades\SmsTraffic;
use Illuminate\Notifications\Client\Response\SmsTrafficResponse;

use App\Enums\SmsTraffic\RouteOptionsEnum;
use App\Models\Logs\SmsLog;
use App\Services\LogService;

class SmsTrafficHelper
{
  protected static $logService;

  /**
   * Send sms to recipient
   */
  public static function send(
    string $to,
    string $message,
    array $options = [],
  ): SmsTrafficResponse {
    $logService = new LogService;
    $options['route'] = $options['route'] ?? RouteOptionsEnum::SMS_VIBER->value;
    $response = SmsTraffic::send($to, $message, $options);
    $logService->logSms(
      phone: $to,
      text: $message,
      route: $options['route'],
      adminId: Admin::user()->id ?? null,
      status: $response->getDescription()
    );
    return $response;
  }
}
