<?php

use App\Enums\Ads\BannerMediaCollection;
use App\Enums\MorphMap;
use App\Models\Ads\Banner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Merges legacy desktop rows (catalog_top | feedback) with mobile counterparts
 * (catalog_mob | feedback_mob) using explicit survivor/donor IDs.
 *
 * Old banners often stored uploads under desktop_* collections; donor (mob) rows
 * get collections renamed to mobile_* before media is moved onto the survivor.
 */
return new class extends Migration
{
    /**
     * Pairs keyed by survivor (desktop/top) banner id → donor (mob) banner id.
     */
    private const array FEEDBACK_PAIRS = [
        152 => 153,
        32 => 33,
    ];

    private const array CATALOG_PAIRS = [
        199 => 200,
        196 => 197,
        193 => 194,
        190 => 191,
        187 => 188,
        184 => 185,
        181 => 182,
        178 => 179,
        174 => 175,
        172 => 171,
        168 => 169,
        166 => 165,
        161 => 162,
        158 => 159,
        155 => 156,
        149 => 150,
        146 => 147,
        142 => 143,
        139 => 140,
        135 => 136,
        132 => 133,
        130 => 129,
    ];

    /**
     * @return array<string, string>
     */
    private static function desktopToMobileRenameMap(): array
    {
        return [
            BannerMediaCollection::DESKTOP_IMAGE->value => BannerMediaCollection::MOBILE_IMAGE->value,
            BannerMediaCollection::DESKTOP_VIDEO->value => BannerMediaCollection::MOBILE_VIDEO->value,
            BannerMediaCollection::DESKTOP_VIDEO_PREVIEW->value => BannerMediaCollection::MOBILE_VIDEO_PREVIEW->value,
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function mobileToDesktopRenameMap(): array
    {
        // inverse: misplaced mob-side files on a desktop survivor row → desktop_*.
        /** @var array<string, string> */
        return array_flip(self::desktopToMobileRenameMap());
    }

    /**
     * @param  array<string, string>  $fromCollectionToCollection
     */
    private static function remapMediaCollections(int $bannerId, array $fromCollectionToCollection): void
    {
        foreach ($fromCollectionToCollection as $from => $to) {
            DB::table('media')
                ->where('model_type', MorphMap::Banner->value)
                ->where('model_id', $bannerId)
                ->where('collection_name', $from)
                ->update(['collection_name' => $to]);
        }
    }

    /**
     * @return list<string>
     */
    private static function mobileCollections(): array
    {
        return [
            BannerMediaCollection::MOBILE_IMAGE->value,
            BannerMediaCollection::MOBILE_VIDEO->value,
            BannerMediaCollection::MOBILE_VIDEO_PREVIEW->value,
        ];
    }

    private function mergeMobileFromDonorIntoSurvivor(Banner $survivor, Banner $donor): void
    {
        self::remapMediaCollections($survivor->id, self::mobileToDesktopRenameMap());
        self::remapMediaCollections($donor->id, self::desktopToMobileRenameMap());

        DB::table('media')
            ->where('model_type', MorphMap::Banner->value)
            ->where('model_id', $donor->id)
            ->whereIn('collection_name', self::mobileCollections())
            ->update(['model_id' => $survivor->id]);

        $survivor->mobile_type = $donor->mobile_type;
        $survivor->save();

        $donor->delete();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach ([self::FEEDBACK_PAIRS, self::CATALOG_PAIRS] as $pairs) {
                foreach ($pairs as $survivorId => $donorId) {
                    $survivor = Banner::query()->withoutGlobalScopes()->find($survivorId);
                    $donor = Banner::query()->withoutGlobalScopes()->find($donorId);

                    if (!$survivor instanceof Banner || !$donor instanceof Banner) {
                        continue;
                    }

                    $this->mergeMobileFromDonorIntoSurvivor($survivor, $donor);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
