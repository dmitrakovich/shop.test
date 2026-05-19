<?php

namespace App\Libraries\Belpost;

use App\Libraries\Belpost\Exceptions\ActionNotFoundException;
use ReflectionClass;

/**
 * Entry point for Belpost API actions (HGrosh-style).
 *
 * Magic methods resolve to classes under {@see Actions} — use {@see Facades\ApiBelpostFacade} in app code.
 */
class Api
{
    private readonly HttpClient $httpClient;

    /**
     * @var array<class-string, Actions\Action>
     */
    private array $actionContainer = [];

    public function __construct(?HttpClient $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new HttpClient();
    }

    public function httpClient(): HttpClient
    {
        return $this->httpClient;
    }

    public function isConfigured(): bool
    {
        return $this->httpClient->isConfigured();
    }

    /**
     * @param  array<int, mixed>  $arguments  Passed to the action constructor (e.g. list id, item id).
     */
    public function __call(string $name, array $arguments): Actions\Action
    {
        $reflection = new ReflectionClass($this->httpClient);
        $actionNamespace = $reflection->getNamespaceName() . '\Actions\\';
        // batchMailingCreateList → BatchMailingCreateList
        $className = $actionNamespace . ucfirst($name);

        if (!class_exists($className)) {
            throw new ActionNotFoundException("Action doesn't exist: {$className}.");
        }

        if (!isset($this->actionContainer[$className])) {
            $this->actionContainer[$className] = new $className($this->httpClient, $arguments);
        }

        return $this->actionContainer[$className];
    }
}
