<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('payment_id');

            $table->unsignedInteger('tenant_id');
            $table->unsignedInteger('room_id');
            $table->unsignedInteger('property_id');

            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50)->nullable();
            $table->string('notes', 255)->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'room_id', 'property_id']);
            $table->index('payment_date');

            $table->foreign('tenant_id')
                ->references('tenant_id')->on('tenants')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

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
        Schema::dropIfExists('payments');
    }
};
