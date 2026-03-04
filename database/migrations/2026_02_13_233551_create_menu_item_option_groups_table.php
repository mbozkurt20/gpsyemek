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
        Schema::create('menu_item_option_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_item_id');
            $table->unsignedBigInteger('restaurant_id');
            $table->string('name'); // Örn: Sos Seçimi, Boyut Seçimi
            $table->string('type')->default('radio'); // radio veya checkbox
            $table->boolean('is_required')->default(true);
            $table->tinyInteger('min_count')->nullable();
            $table->tinyInteger('max_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_option_groups');
    }
};
