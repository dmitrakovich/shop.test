<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Url;
use App\Models\ProductAttributes\Status;

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
