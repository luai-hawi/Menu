<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('name_en')->nullable();
            $table->text('description_en')->nullable();
            $table->string('welcome_title')->nullable();
            $table->string('welcome_title_en')->nullable();
            $table->text('welcome_message')->nullable();
            $table->text('welcome_message_en')->nullable();
            $table->string('welcome_video')->nullable();
        });
        Schema::table('menu_categories', fn (Blueprint $table) => $table->string('name_en')->nullable());
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('name_en')->nullable();
            $table->text('description_en')->nullable();
        });
        Schema::table('menu_item_option_groups', fn (Blueprint $table) => $table->string('group_name_en')->nullable());
        Schema::table('menu_item_options', function (Blueprint $table) {
            $table->string('option_name_en')->nullable();
            $table->string('option_note_en', 160)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', fn (Blueprint $table) => $table->dropColumn([
            'name_en', 'description_en', 'welcome_title', 'welcome_title_en',
            'welcome_message', 'welcome_message_en', 'welcome_video',
        ]));
        Schema::table('menu_categories', fn (Blueprint $table) => $table->dropColumn('name_en'));
        Schema::table('menu_items', fn (Blueprint $table) => $table->dropColumn(['name_en', 'description_en']));
        Schema::table('menu_item_option_groups', fn (Blueprint $table) => $table->dropColumn('group_name_en'));
        Schema::table('menu_item_options', fn (Blueprint $table) => $table->dropColumn(['option_name_en', 'option_note_en']));
    }
};
