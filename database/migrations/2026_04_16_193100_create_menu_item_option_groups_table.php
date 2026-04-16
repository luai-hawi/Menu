<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_item_option_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')
                ->constrained('menu_items')
                ->cascadeOnDelete();

            // SINGLE or MULTIPLE
            $table->enum('group_type', ['SINGLE', 'MULTIPLE'])->default('SINGLE');

            // Arabic-only label (the dashboard is Arabic-first).
            $table->string('group_name_ar');

            // Selection constraints (only meaningful for MULTIPLE groups,
            // but a SINGLE required group is represented via min_choices=1, max_choices=1).
            $table->unsignedTinyInteger('min_choices')->default(0);
            $table->unsignedTinyInteger('max_choices')->default(1);

            // Controls whether the customer MUST choose at least one option.
            // Mirrors min_choices > 0 but kept explicit for clarity.
            $table->boolean('is_required')->default(false);

            // Display order within the item
            $table->unsignedInteger('position')->default(0);

            $table->timestamps();

            // Hot-path index: loading an item's groups ordered by position.
            $table->index(['menu_item_id', 'position'], 'mi_opt_groups_item_pos_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_option_groups');
    }
};
