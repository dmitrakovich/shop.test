<?php

use App\Models\ProductAttributes\Promotion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Slug for all active sales
     */
    protected string $slug = 'promotion';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->uuid('slug');
            $table->timestamps();
        });

        DB::table('promotions')->insert([
            'name' => 'Акция',
            'slug' => $this->slug,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('urls')->insert([
            'slug' => $this->slug,
            'model_type' => Promotion::class,
            'model_id' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotions');

        DB::table('urls')->where('slug', $this->slug)->delete();
    }
};
