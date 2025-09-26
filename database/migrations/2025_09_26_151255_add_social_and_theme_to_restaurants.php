<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('facebook_url')->nullable()->after('whatsapp_number');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('snapchat_url')->nullable()->after('instagram_url');
            $table->string('whatsapp_url')->nullable()->after('snapchat_url');
            $table->string('twitter_url')->nullable()->after('whatsapp_url');
            $table->string('tiktok_url')->nullable()->after('twitter_url');
            $table->json('theme_colors')->nullable()->after('tiktok_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_url',
                'instagram_url',
                'snapchat_url',
                'whatsapp_url',
                'twitter_url',
                'tiktok_url',
                'theme_colors'
            ]);
        });
    }
};