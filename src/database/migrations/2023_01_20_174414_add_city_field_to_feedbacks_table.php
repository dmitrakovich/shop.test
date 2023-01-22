<?php

use App\Models\Feedback;
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
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->string('user_city')->nullable()->after('user_phone');
        });

        DB::table('media')
            ->where('model_type', Feedback::class)
            ->where('collection_name', 'default')
            ->update(['collection_name' => 'photos']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropColumn('user_city');
        });
    }
};
