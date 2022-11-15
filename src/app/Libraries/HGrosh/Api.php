<?php

namespace App\Libraries\HGrosh;

use App\Libraries\HGrosh\Exceptions\ActionNotFoundException;
use ReflectionClass;

class Api
{
    private HttpClient $http_client; // Объект для взаимодействия с API api-epos.hgrosh.by

    private array $action_container = [];

    public function __construct()
    {
        $this->http_client = new HttpClient();
    }

    public function __call($name, $arguments)
    {
        $reflection = new ReflectionClass($this->http_client);
        $action_namespace = $reflection->getNamespaceName() . '\Actions\\';
        $class_name = $action_namespace . ucfirst($name);
        if (class_exists($class_name)) {
            if (!isset($this->action_container[$class_name])) {
                $this->action_container[$class_name] = new $class_name($this->http_client, $arguments);
            }

            return $this->action_container[$class_name];
        } else {
            throw new ActionNotFoundException("Action doesn't exist.");
        }
    }
}
