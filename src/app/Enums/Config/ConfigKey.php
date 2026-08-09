<?php

namespace App\Enums\Config;

enum ConfigKey: string
{
    case AutoOrderStatuses = 'auto_order_statuses';
    case Availability = 'availability';
    case DistribOrderSetup = 'distrib_order_setup';
    case Feedback = 'feedback';
    case Installment = 'installment';
    case InventoryBlacklist = 'inventory_blacklist';
    case NewsletterRegister = 'newsletter_register';
    case Rating = 'rating';
    case SendingTracks = 'sending_tracks';
    case Sms = 'sms';
}
