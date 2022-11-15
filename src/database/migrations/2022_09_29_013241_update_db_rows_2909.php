<?php

use App\Models\ProductAttributes\Status;
use App\Models\Url;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Url::where('slug', 'promotion')->update(['model_type' => 'App\Models\ProductAttributes\Status']);
        Status::firstOrCreate(
            ['slug' => 'promotion'],
            ['name' => 'акция'],
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
