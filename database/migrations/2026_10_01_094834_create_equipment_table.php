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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            // Equipment information
            $table->string('name');
            $table->text('description');
            $table->string('brand')->nullable();

            // Energy specifications
            $table->decimal('power', 10, 2)->nullable();
            $table->decimal('capacity', 10, 2)->nullable();

            // Rental information
            $table->enum('condition', [
                'excellent',
                'good',
                'used'
            ]);

            $table->decimal('price_per_day', 10, 2);

            $table->string('location');

            $table->boolean('availability')->default(true);

            // Equipment image
            $table->string('image')->nullable();

            // Publication status
            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};