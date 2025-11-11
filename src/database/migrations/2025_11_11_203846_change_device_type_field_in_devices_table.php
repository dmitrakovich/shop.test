<?php

use App\Enums\Device\DeviceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->tinyInteger('type_int')->after('type');
        });

        DB::table('devices')->where('type', 'mobile')->update(['type_int' => DeviceType::MOBILE]);
        DB::table('devices')->where('type', 'desktop')->update(['type_int' => DeviceType::DESKTOP]);

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->renameColumn('type_int', 'type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->enum('type_enum', ['mobile', 'desktop'])->after('type');
        });

        DB::table('devices')->where('type', DeviceType::MOBILE)->update(['type_enum' => 'mobile']);
        DB::table('devices')->where('type', DeviceType::DESKTOP)->update(['type_enum' => 'desktop']);

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->renameColumn('type_enum', 'type');
        });
    }
};
