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
        Schema::create('compensation_codes', function (Blueprint $table) {
            // Primary & Foreign Keys
            $table->id();

            // Code Information
            $table->string('code', 16)
                ->unique()
                ->comment('Code unique et difficile à deviner (ex: CC-A1B2C3D4E5F6)');

            // Reduction Details
            $table->enum('type', ['percentage', 'fixed'])
                ->comment('Type de réduction: pourcentage (0-100) ou montant fixe (TND)');

            $table->decimal('value', 8, 2)
                ->comment('Valeur de la réduction (pourcentage ou montant en TND)');

            // Validity Period
            $table->dateTime('valid_from')
                ->comment('Date de début de validité');

            $table->dateTime('valid_until')
                ->comment('Date d\'expiration');

            // Usage Limits
            $table->integer('max_uses')
                ->default(1)
                ->comment('Nombre maximal d\'utilisations');

            $table->integer('uses_count')
                ->default(0)
                ->comment('Nombre d\'utilisations actuelles');

            // Status & Assignment
            $table->boolean('is_active')
                ->default(true)
                ->comment('Code actif ou désactivé (soft-delete via flag)');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Client bénéficiaire (NULL = non assigné/code admin)');

            // Origin & Tracking
            $table->foreignId('reclamation_id')
                ->nullable()
                ->constrained('reclamations')
                ->nullOnDelete()
                ->comment('Réclamation d\'origine (NULL = créé manuellement)');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Admin qui a créé le code');

            // Description for Admin
            $table->text('description')
                ->nullable()
                ->comment('Description du code pour l\'administrateur');

            // Usage Tracking
            $table->dateTime('last_used_at')
                ->nullable()
                ->comment('Dernière utilisation du code');

            // Timestamps
            $table->timestamps();

            // Indexes for common queries
            $table->index('code');
            $table->index('user_id');
            $table->index('reclamation_id');
            $table->index('is_active');
            $table->index('valid_until');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compensation_codes');
    }
};
