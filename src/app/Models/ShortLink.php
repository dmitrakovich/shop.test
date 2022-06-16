<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ShortLink provide full link by short link
 *
 * @property integer $id
 * @property string $short_link
 * @property string $full_link
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ShortLink extends Model
{
    use HasFactory;

    final const SHORT_LINK_LENGTH = 7;
    final const CHAR_LIST = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ12345678901234567890';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * Generate, save & return short link
     */
    public static function createShortLink(string $fullLink): self
    {
        return self::firstOrCreate(
            ['full_link' => $fullLink],
            ['short_link' => self::generateShortLink()],
        );
    }

    /**
     * Generate unique short link
     */
    protected static function generateShortLink(): string
    {
        $fuse = 15;
        do {
            if (!$fuse--) {
                throw new \Exception('Too many attempts to generate');
            }
            $random = substr(str_shuffle(self::CHAR_LIST), 0, self::SHORT_LINK_LENGTH);
        } while (self::where('short_link', $random)->exists());

        return $random;
    }
}
