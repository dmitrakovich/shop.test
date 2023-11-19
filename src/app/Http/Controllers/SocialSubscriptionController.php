<?php

namespace App\Http\Controllers;

use App\Events\Analytics\SocialSubscription;
use Illuminate\Http\RedirectResponse;

class SocialSubscriptionController extends Controller
{
    public function subscribe(string $channel, string $eventId): RedirectResponse
    {
        $subscribeLink = match ($channel) {
            'telegram' => config('contacts.telegram-channel.link'),
            'viber' => config('contacts.viber-channel.link'),
            default => '/',
        };

        event(new SocialSubscription($eventId));

        return redirect($subscribeLink);
    }
}
