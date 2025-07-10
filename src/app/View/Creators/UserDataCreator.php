<?php

namespace App\View\Creators;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\GoogleTagManager\DataLayer;
use Spatie\GoogleTagManager\ScriptViewCreator;

/**
 * Class UserDataCreator
 *
 * View creator for providing user data to the view, integrating with Google Tag Manager.
 */
class UserDataCreator extends ScriptViewCreator
{
    /**
     * Bind user data to the view.
     */
    public function create(View $view): void
    {
        $view->with('userData', $this->getUserData());
    }

    /**
     * Get user data from the Google Tag Manager data layer or push data.
     *
     * @return Collection<string, mixed>
     */
    private function getUserData(): Collection
    {
        $dataLayer = $this->googleTagManager->getDataLayer()->toArray();

        if (isset($dataLayer['user_data'])) {
            /** @var array{user_data: array<string, mixed>} $dataLayer */
            return collect($dataLayer['user_data']);
        }

        /** @var DataLayer $item */
        foreach ($this->googleTagManager->getPushData() as $item) {
            /** @var array{user_data?: array<string, mixed>} $dataLayer */
            $dataLayer = $item->toArray();
            if (isset($dataLayer['user_data'])) {
                return collect($dataLayer['user_data']);
            }
        }

        return collect();
    }
}
