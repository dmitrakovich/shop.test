<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait TruncatesTables
{
    protected function truncateTable(string $table): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            DB::table($table)->truncate();
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
