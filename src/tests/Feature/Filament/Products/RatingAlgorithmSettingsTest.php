<?php

namespace Tests\Feature\Filament\Products;

use App\Enums\Config\ConfigKey;
use App\Filament\Resources\Products\RatingAlgorithms\Pages\ListRatingAlgorithms;
use App\Jobs\UpdateProductsRatingJob;
use App\Models\Admin\AdminUser;
use App\Models\Config;
use App\Models\RatingAlgorithm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RatingAlgorithmSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_rating_settings_recalculates_ratings_when_algorithms_change(): void
    {
        Bus::fake([UpdateProductsRatingJob::class]);

        $popularity = $this->createAlgorithm('Popularity');
        $newness = $this->createAlgorithm('Newness');
        $this->saveRatingConfig($popularity, $newness);

        $this->actingAs($this->createSuperAdmin(), 'admin');

        Livewire::test(ListRatingAlgorithms::class)
            ->callAction('settings', data: [
                'popularity_algorithm_id' => $newness->id,
                'newness_algorithm_id' => $newness->id,
                'season_algorithm_id' => $newness->id,
                'sale_algorithm_id' => $newness->id,
            ])
            ->assertHasNoFormErrors()
            ->assertNotified('Настройки рейтинга сохранены');

        $config = Config::findByKeyOrFail(ConfigKey::Rating)->config;

        $this->assertSame($newness->id, (int)$config['popularity_algorithm_id']);
        $this->assertSame($newness->id, (int)$config['newness_algorithm_id']);
        Bus::assertDispatchedSync(UpdateProductsRatingJob::class);
    }

    public function test_saving_unchanged_rating_settings_does_not_recalculate(): void
    {
        Bus::fake([UpdateProductsRatingJob::class]);

        $algorithm = $this->createAlgorithm('Unchanged');
        $this->saveRatingConfig($algorithm, $algorithm);

        $this->actingAs($this->createSuperAdmin(), 'admin');

        Livewire::test(ListRatingAlgorithms::class)
            ->callAction('settings', data: [
                'popularity_algorithm_id' => $algorithm->id,
                'newness_algorithm_id' => $algorithm->id,
                'season_algorithm_id' => $algorithm->id,
                'sale_algorithm_id' => $algorithm->id,
            ])
            ->assertHasNoFormErrors()
            ->assertNotified('Настройки рейтинга сохранены');

        Bus::assertNotDispatched(UpdateProductsRatingJob::class);
    }

    public function test_recalculate_action_runs_rating_job(): void
    {
        Bus::fake([UpdateProductsRatingJob::class]);

        $this->actingAs($this->createSuperAdmin(), 'admin');

        Livewire::test(ListRatingAlgorithms::class)
            ->callAction('recalculate')
            ->assertNotified('Рейтинг пересчитан');

        Bus::assertDispatchedSync(UpdateProductsRatingJob::class);
    }

    private function createAlgorithm(string $name): RatingAlgorithm
    {
        return RatingAlgorithm::query()->create(['name' => $name]);
    }

    private function saveRatingConfig(RatingAlgorithm $popularity, RatingAlgorithm $newness): void
    {
        Config::query()->updateOrCreate(
            ['key' => ConfigKey::Rating],
            [
                'config' => [
                    'popularity_algorithm_id' => $popularity->id,
                    'newness_algorithm_id' => $newness->id,
                    'season_algorithm_id' => $popularity->id,
                    'sale_algorithm_id' => $popularity->id,
                    'last_update' => null,
                ],
            ],
        );
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'rating_settings_admin',
            'password' => bcrypt('secret'),
            'name' => 'Rating Settings Admin',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
