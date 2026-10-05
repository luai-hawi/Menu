<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            // Nullable so existing restaurants keep the current app-wide locale
            // until an owner explicitly picks one, instead of being switched
            // to a language nobody chose for them.
            $table->string('default_language', 5)->nullable()->after('currency_position');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('default_language');
        });
    }
};
