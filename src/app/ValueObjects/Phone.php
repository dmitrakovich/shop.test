<?php

namespace App\ValueObjects;

use App\Helpers\PhoneHelper;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

/**
 * Phone value object class for handling phone number operations
 */
class Phone implements Castable
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
     * @param  array<int, mixed>  $arguments
     */
    public static function dataCastUsing(...$arguments): Cast
    {
        return new class implements Cast
        {
            /**
             * @param  array<string, mixed>  $properties
             * @param  CreationContext<Data>  $context
             */
            public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): Phone
            {
                return Phone::fromRawString($value);
            }
        };
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
