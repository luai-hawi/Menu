<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_group_id')
                ->constrained('menu_item_option_groups')
                ->cascadeOnDelete();

            // Arabic-only label.
            $table->string('option_name_ar');

            // price_delta adjusts the base item price. May be negative
            // (e.g. "Small" -1.00) or zero. Uses decimal(8,2) to match menu_items.price.
            $table->decimal('price_delta', 8, 2)->default(0);

            // Optional short Arabic note. Capped to enforce brevity.
            $table->string('option_note_ar', 160)->nullable();

            // Display order inside its group
            $table->unsignedInteger('position')->default(0);

            // Whether the option is currently offered.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Hot path: loading a group's options in display order.
            $table->index(['option_group_id', 'position'], 'mi_opts_group_pos_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_options');
    }
};
