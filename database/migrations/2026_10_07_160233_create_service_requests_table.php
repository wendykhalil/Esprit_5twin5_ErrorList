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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
                
            $table->foreignId('service_provider_id')
                ->constrained('service_providers')
                ->restrictOnDelete();
                
            $table->foreignId('equipment_id')
                ->nullable()
                ->constrained('equipment')
                ->nullOnDelete();
                
            $table->string('title');
            $table->text('description');
            $table->dateTime('requested_date');
            $table->string('address');
            $table->string('status')->default('pending');
            $table->decimal('estimated_price', 8, 2)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
