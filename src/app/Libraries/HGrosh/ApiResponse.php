<?php

namespace App\Libraries\HGrosh;

use Psr\Http\Message\ResponseInterface;

class ApiResponse
{
    private string $body; // Тело ответа @var string

    private array $location;

    private int $status; // HTTP-статус ответа @var int

    private array $errors = []; // Массив ошибок @var array

    /**
     * ApiResponse constructor.
     */
    public function __construct(?ResponseInterface $response = null)
    {
        if ($response === null) {
            $this->status = 500;
            $this->body = '';
            $this->errors[] = [
                'code' => 'internal_error',
                'message' => 'Internal Server Error',
            ];
        } else {
            $this->status = $response->getStatusCode();
            if (
                $response->hasHeader('Content-type') &&
                strpos(implode(',', $response->getHeader('Content-type')), 'json') !== false
            ) {
                $this->body = (string)$response->getBody()->getContents();
            } else {
                $this->body = (string)$response->getBody();
            }
            $this->location = $response->getHeader('location');
            if ($this->status > 299) {
                $decode_body = json_decode($this->body, true);
                if (isset($decode_body['error'])) {
                    $this->errors[] = [
                        'code' => $decode_body['error'],
                        'message' => $decode_body['error_description'] ?? $decode_body['message'] ?? 'unknown_error',
                    ];
                } elseif (isset($decode_body['errors'])) {
                    $this->errors = $decode_body['errors'];
                } elseif (isset($decode_body['requests'][0]['errors'])) {
                    $this->errors = $decode_body['requests'][0]['errors'];
                }
            }
        }
    }

    /**
     * Проверка корректности выполненного запроса
     */
    public function isOk(): bool
    {
        return $this->status >= 200 && $this->status <= 299;
    }

    /**
     * Проверка наличия ошибок в запросе
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getLocation(): array
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function getBodyFormat(): array
    {
        $result = json_decode($this->body, true);

        return is_array($result) ? $result : [];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
