<?php

namespace App\Contracts;

interface FeedServiceInterface
{
    /**
     * Backup feed file
     *
     * @return void
     */
    public function backup(): void;

    /**
     * Generate feed file
     *
     * @return void
     */
    public function generate(): void;
}
