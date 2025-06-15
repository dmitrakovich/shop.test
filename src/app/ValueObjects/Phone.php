<?php

namespace App\ValueObjects;

use App\Helpers\PhoneHelper;

/**
 * Phone value object class for handling phone number operations
 */
class Phone
{
    /**
     * @param  int  $value  The phone number as an integer
     */
    public function __construct(private readonly int $value) {}

    /**
     * String representation of the phone number in E164 format
     */
    public function __toString(): string
    {
        return $this->toE164();
    }

    /**
     * Creates a new Phone instance from a raw string input
     *
     * @param  string  $value  Raw phone number string
     */
    public static function fromRawString(string $value): self
    {
        return new self(PhoneHelper::unify($value));
    }

    /**
     * Returns the phone number as an integer
     */
    public function toInt(): int
    {
        return $this->value;
    }

    /**
     * Returns the phone number in E164 format (with + prefix)
     */
    public function toE164(): string
    {
        return '+' . $this->value;
    }

    /**
     * Returns the phone number format for database storage
     */
    public function forSave(): int
    {
        return $this->toInt();
    }

    /**
     * Returns the phone number format for SMS sending
     */
    public function forSms(): int
    {
        return $this->toInt();
    }
}
