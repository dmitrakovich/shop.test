<?php

namespace App\Console\Commands\OneRun;

use App\Models\OneC\DiscountCard;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UpdateOfflineUserFullNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-run:update-offline-user-full-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update full names of users who have offline orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var Collection|User[] */
        $users = User::query()
            ->has('offlineOrders')
            ->whereNull('last_name')
            ->get(['id', 'discount_card_number', 'first_name', 'last_name', 'patronymic_name']);

        $this->output->progressStart($users->count());

        foreach ($users as $user) {
            $this->updateFullName($user);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }

    private function updateFullName(User $user): void
    {
        $id = str_pad($user->discount_card_number, 9, ' ', STR_PAD_LEFT);
        /** @var DiscountCard|null */
        $userFullNameFromDiscountCard = DiscountCard::query()
            ->where('ID', $id)
            ->first([
                'SP4353 as first_name',
                'SP4352 as last_name',
                'SP4354 as patronymic_name',
            ]);

        if (!$userFullNameFromDiscountCard) {
            return;
        }

        $user->update($userFullNameFromDiscountCard->toArray());
    }
}
