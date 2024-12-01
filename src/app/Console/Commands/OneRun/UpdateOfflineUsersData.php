<?php

namespace App\Console\Commands\OneRun;

use App\Models\OneC\DiscountCard;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UpdateOfflineUsersData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-run:update-offline-users-data';

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
            ->where(function (Builder $query) {
                $query->orWhereNull('last_name')
                    ->orWhereNull('birth_date');
            })
            ->get(['id', 'discount_card_number', 'first_name', 'last_name', 'patronymic_name', 'birth_date']);

        $this->output->progressStart($users->count());

        foreach ($users as $user) {
            $this->updateFullName($user);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }

    private function updateFullName(User $user): void
    {
        /** @var DiscountCard|null */
        $userFromDiscountCard = DiscountCard::query()
            ->where('ID', $user->discount_card_number)
            ->first([
                'SP4353 as first_name',
                'SP4352 as last_name',
                'SP4354 as patronymic_name',
                'SP3970 as birth_date',
            ]);

        if (!$userFromDiscountCard) {
            return;
        }

        $user->update($this->filterExistingValues($user, $userFromDiscountCard));
    }

    /**
     * Filter out values that already exist in the user model
     *
     * @param  User  $user  The user model to check against
     * @param  DiscountCard  $discountCard  The discount card model containing new values
     * @return array Array of field/value pairs that don't exist in user model
     */
    private function filterExistingValues(User $user, DiscountCard $discountCard): array
    {
        $values = [];
        foreach ($discountCard->toArray() as $field => $value) {
            if (empty($user->{$field})) {
                $values[$field] = $value;
            }
        }

        return $values;
    }
}
