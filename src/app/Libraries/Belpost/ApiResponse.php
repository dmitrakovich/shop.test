<?php

namespace App\Libraries\Belpost;

class ApiResponse
{
    /**
     * @param  array<string, mixed>|string  $body
     */
    public function __construct(
        private readonly int $status,
        private readonly array|string $body,
    ) {}

    public function isOk(): bool
    {
        return $this->status >= 200 && $this->status <= 299;
    }

    /**
     * @return array<string, mixed>
     */
    public function getBodyFormat(): array
    {
        return is_array($this->body) ? $this->body : [];
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
