<?php

namespace App\Libraries\Belpost\Exceptions;

use Illuminate\Http\Client\Response;

class BelpostApiException extends BelpostException
{
    /**
     * @param  array<string, mixed>|null  $responseBody
     */
    public function __construct(
        string $message,
        public readonly ?int $statusCode = null,
        public readonly ?array $responseBody = null,
    ) {
        parent::__construct($message);
    }

    public static function fromResponse(Response $response, string $context = ''): self
    {
        $body = $response->json();
        $message = is_array($body)
            ? self::messageFromBody($body)
            : (string)$response->body();

        $prefix = $context !== '' ? "{$context}: " : '';

        return new self(
            $prefix . $message,
            $response->status(),
            is_array($body) ? $body : null,
        );
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private static function messageFromBody(array $body): string
    {
        if (isset($body['errors']) && is_array($body['errors'])) {
            $parts = [];
            foreach ($body['errors'] as $field => $messages) {
                if (is_array($messages)) {
                    foreach ($messages as $item) {
                        $parts[] = is_string($field) && $field !== ''
                            ? "{$field}: {$item}"
                            : (string)$item;
                    }
                } else {
                    $parts[] = (string)$messages;
                }
            }

            if ($parts !== []) {
                return implode('; ', $parts);
            }
        }

        return (string)($body['message'] ?? $body['error'] ?? json_encode($body, JSON_UNESCAPED_UNICODE));
    }
}
