<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenancerequests', function (Blueprint $table) {
            $table->increments('request_id');

            $table->unsignedInteger('room_id');
            $table->unsignedInteger('property_id');

            $table->text('issue_reported');
            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Cancelled'])->default('Pending');

            $table->dateTime('date_reported')->useCurrent();
            $table->string('assigned_to', 100)->nullable();

            $table->timestamps();

            $table->index(['room_id', 'property_id']);
            $table->index('status');

            $table->foreign('room_id')
                ->references('room_id')->on('rooms')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('property_id')
                ->references('property_id')->on('properties')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenancerequests');
    }
};
