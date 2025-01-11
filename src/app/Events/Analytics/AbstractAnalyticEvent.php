<?php

namespace App\Events\Analytics;

use App\Facades\Device;
use App\Models\Data\UserData;
use App\Models\Guest;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Util;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class AbstractAnalyticEvent
 *
 * This abstract class provides a foundation for analytic events with common functionality,
 * such as setting event ID, visit ID, user data, event time, and source URL.
 */
abstract class AbstractAnalyticEvent
{
    use SerializesModels;

    /**
     * Event ID for the analytic event.
     */
    public string $eventId;

    /**
     * Event time for the analytic event (timestamp).
     */
    public int $eventTime;

    /**
     * Source URL for the analytic event.
     */
    public ?string $sourceUrl;

    /**
     * Where the Conversion occurred
     */
    public string $actionSource;

    /**
     * Visit ID for the analytic event.
     */
    public ?string $visitId;

    /**
     * User data for the analytic event.
     */
    public UserData $userData;

    /**
     * Set analytic data, including event ID, visit ID, user data, event time, and source URL.
     */
    protected function setAnalyticData(): void
    {
        $this->setEventId();
        $this->setEventTime();
        $this->setSourceUrl();
        $this->setActionSource();
        $this->setVisitId();
        $this->setUserData();
    }

    /**
     * Set a unique event ID for the analytic event.
     */
    protected function setEventId(): void
    {
        if (!isset($this->eventId)) {
            $this->eventId = Str::ulid();
        }
    }

    /**
     * Set event time to the current timestamp.
     */
    protected function setEventTime(): void
    {
        $this->eventTime = time();
    }

    /**
     * Set the source URL for the analytic event.
     */
    protected function setSourceUrl(): void
    {
        $this->sourceUrl = request()->fullUrl();
    }

    /**
     * Set action source, this is where the Conversion occurred.
     */
    protected function setActionSource(): void
    {
        if (!isset($this->actionSource)) {
            $this->actionSource = ActionSource::WEBSITE;
        }
    }

    /**
     * Set a unique visit ID for the analytic event.
     */
    protected function setVisitId(): void
    {
        $this->visitId = Device::id();
    }

    /**
     * Set user data based on the authentication status
     */
    protected function setUserData(): void
    {
        $externalIds = [$this->visitId];
        if (Auth::check()) {
            $externalIds[] = Auth::id();
        }
        $userData = Auth::check() ? Auth::user()->toArray() : Guest::getData();
        $this->userData = (new UserData($userData))
            ->setExternalIds($externalIds)
            ->setClientIpAddress(request()->ip())
            ->setClientUserAgent(request()->userAgent())
            ->setFbc(Util::getFbp())
            ->setFbp(Util::getFbc());
    }
}
