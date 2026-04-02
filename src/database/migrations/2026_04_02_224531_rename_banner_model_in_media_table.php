<?php

use App\Enums\MorphMap;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('media')
            ->where('model_type', 'App\Models\Banner')
            ->update(['model_type' => MorphMap::Banner]);
    }
};
