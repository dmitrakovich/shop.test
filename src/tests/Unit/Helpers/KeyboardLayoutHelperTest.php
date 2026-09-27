<?php

namespace Tests\Unit\Helpers;

use App\Helpers\KeyboardLayoutHelper;
use PHPUnit\Framework\TestCase;

class KeyboardLayoutHelperTest extends TestCase
{
    public function test_it_switches_english_layout_to_russian(): void
    {
        $this->assertSame('ботинки', KeyboardLayoutHelper::switch(',jnbyrb'));
        $this->assertSame('ботинки.', KeyboardLayoutHelper::switch(',jnbyrb/'));
    }

    public function test_it_switches_russian_layout_to_english(): void
    {
        $this->assertSame('barocco', KeyboardLayoutHelper::switch('ифкщссщ'));
    }

    public function test_latin_alphabet_roundtrips(): void
    {
        $lower = 'qwertyuiop[]asdfghjkl;\'zxcvbnm,./`';
        $upper = 'QWERTYUIOP{}ASDFGHJKL:"ZXCVBNM<>?~';

        $lowerSwitched = KeyboardLayoutHelper::switch($lower);
        $upperSwitched = KeyboardLayoutHelper::switch($upper);

        $this->assertNotNull($lowerSwitched);
        $this->assertNotNull($upperSwitched);
        $this->assertSame($lower, KeyboardLayoutHelper::switch($lowerSwitched));
        $this->assertSame($upper, KeyboardLayoutHelper::switch($upperSwitched));
    }

    public function test_it_returns_null_for_mixed_alphabets_digits_and_empty_text(): void
    {
        $this->assertNull(KeyboardLayoutHelper::switch('ботq'));
        $this->assertNull(KeyboardLayoutHelper::switch('38'));
        $this->assertNull(KeyboardLayoutHelper::switch(''));
        $this->assertNull(KeyboardLayoutHelper::switch('   '));
    }
}
