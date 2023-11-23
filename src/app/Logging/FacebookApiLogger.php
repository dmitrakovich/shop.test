<?php

namespace App\Logging;

use FacebookAds\Http\ResponseInterface;
use FacebookAds\Logger\CurlLogger;

class FacebookApiLogger extends CurlLogger
{
    /**
     * @param  string  $level
     */
    public function logResponse($level, ResponseInterface $response, array $context = [])
    {
        $this->flush('response: ' . $response->getBody());
    }
}
