<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Arabic-only simplification.
 * --------------------------
 * The UI no longer offers English-language fields for option groups and
 * options — everything is Arabic. This migration drops the *_en columns.
 *
 * Rollback re-creates the columns as NOT-NULL empty strings. If you need
 * to restore English content after a rollback you'll need to repopulate
 * the columns manually.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_item_option_groups', function (Blueprint $table) {
            if (Schema::hasColumn('menu_item_option_groups', 'group_name_en')) {
                $table->dropColumn('group_name_en');
            }
        });

        Schema::table('menu_item_options', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('menu_item_options', 'option_name_en')) {
                $drops[] = 'option_name_en';
            }
            if (Schema::hasColumn('menu_item_options', 'option_note_en')) {
                $drops[] = 'option_note_en';
            }
            if (! empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }

    public function down(): void
    {
        Schema::table('menu_item_option_groups', function (Blueprint $table) {
            if (! Schema::hasColumn('menu_item_option_groups', 'group_name_en')) {
                $table->string('group_name_en')->default('');
            }
        });

        Schema::table('menu_item_options', function (Blueprint $table) {
            if (! Schema::hasColumn('menu_item_options', 'option_name_en')) {
                $table->string('option_name_en')->default('');
            }
            if (! Schema::hasColumn('menu_item_options', 'option_note_en')) {
                $table->string('option_note_en', 160)->nullable();
            }
        });
    }
};
