
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_guides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('usage_context');
            $table->longText('instructions');
            $table->text('safety_precautions')->nullable();
            $table->string('difficulty_level')->default('beginner');
            $table->string('video_url')->nullable();
            $table->string('status')->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_guides');
    }
};
