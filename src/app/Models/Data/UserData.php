<?php

namespace App\Models\Data;

use FacebookAds\Object\ServerSide\UserData as FacebookUserData;
use Illuminate\Support\Facades\Auth;

class UserData extends FacebookUserData
{
    /**
     * Normalize and prepare user data for Google Tag Manager (GTM).
     */
    public function normalizeForGtm()
    {
        $normalizedData = $this->normalize();

        $gtmUserData = [];
        $gtmUserData['fn'] = $normalizedData['fn'][0] ?? null;
        $gtmUserData['ln'] = $normalizedData['ln'][0] ?? null;
        $gtmUserData['em'] = $normalizedData['em'][0] ?? null;
        $gtmUserData['ph'] = $normalizedData['ph'][0] ?? null;
        $gtmUserData['vid'] = $normalizedData['external_id'][0];
        $gtmUserData['uid'] = $normalizedData['external_id'][1] ?? null;
        $gtmUserData['user_id'] = $gtmUserData['uid'];
        $gtmUserData['user_type'] = Auth::check() ? Auth::user()->usergroup_id : 'guest';

        return array_filter($gtmUserData);
    }
}
