<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('restaurant_ratings', function (Blueprint $table) {
            $table->bigInteger('order_id')->nullable()->after('restaurant_id');
        });
    }

    public function down()
    {
        Schema::table('restaurant_ratings', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }
};
