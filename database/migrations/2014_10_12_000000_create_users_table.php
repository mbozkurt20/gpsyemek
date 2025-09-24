<?php

use App\Enums\UserApplied;
use App\Enums\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->longText('address')->nullable();
            $table->integer('balance_id');
            $table->longtext('device_token')->nullable();
            $table->boolean('is_membership_conditions')->default(true);
            $table->boolean('is_illumination_text')->default(true);
            $table->boolean('is_electronic_message')->default(false);
            $table->unsignedTinyInteger('status')->default(UserStatus::ACTIVE);
            $table->unsignedTinyInteger('applied')->default(UserApplied::ADMIN);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
