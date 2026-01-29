<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->increments('property_id');

            $table->string('name', 255);        // NOT NULL
            $table->text('address')->nullable(); // text DEFAULT NULL
            $table->string('image_url', 255)->nullable();

            $table->timestamp('created_at')->useCurrent(); // default current_timestamp()
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
