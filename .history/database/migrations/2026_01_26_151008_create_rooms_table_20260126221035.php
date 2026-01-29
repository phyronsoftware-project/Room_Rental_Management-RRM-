<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->increments('room_id');

            $table->unsignedInteger('property_id');
            $table->string('room_number', 50);
            $table->string('floor', 50)->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('status', ['Available', 'Occupied', 'Maintenance'])->default('Available');

            $table->timestamps();

            $table->index('property_id');
            $table->unique(['property_id', 'room_number']);

            $table->foreign('property_id')
                ->references('property_id')->on('properties')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
