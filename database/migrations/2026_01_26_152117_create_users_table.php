<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');

            $table->unsignedInteger('property_id')->nullable();

            $table->string('full_name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);

            $table->enum('role', ['super_admin', 'owner', 'manager'])->default('owner');

            $table->string('profile_image_url', 255)->nullable();
            $table->string('otp_code', 6)->nullable();
            $table->dateTime('otp_expiry')->nullable();

            $table->timestamps();

            $table->index('property_id');

            $table->foreign('property_id')
                ->references('property_id')->on('properties')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
