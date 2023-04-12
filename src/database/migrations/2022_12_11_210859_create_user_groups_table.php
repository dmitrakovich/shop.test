<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('discount')->default(0);
        });

        DB::table('user_groups')->insert(['id' => 1, 'name' => 'Зарегистрированные', 'discount' => 5]);

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('usergroup_id', 'group_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('group_id')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_groups');

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('group_id', 'usergroup_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('usergroup_id')->default(0)->change();
        });
    }
};
