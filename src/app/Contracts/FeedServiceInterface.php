<?php

namespace App\Contracts;

interface FeedServiceInterface
{
    /**
     * Backup feed file
     */
    public function backup(): void;

    /**
     * Generate feed file
     */
    public function generate(): void;
}
