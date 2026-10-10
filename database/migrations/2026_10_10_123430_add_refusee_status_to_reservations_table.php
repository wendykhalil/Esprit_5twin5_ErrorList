<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajoute le statut 'refusee' à la colonne enum de la table reservations.
     * Cette migration modifie l'enum pour inclure 'refusee' pour les réservations refusées par l'admin.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // MySQL: Modifier l'enum
            DB::statement("ALTER TABLE reservations MODIFY COLUMN statut ENUM('en_attente', 'confirmee', 'en_cours', 'terminee', 'litige', 'annulee', 'refusee') NOT NULL DEFAULT 'en_attente'");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Ajouter la valeur à l'enum existant
            DB::statement("ALTER TYPE reservation_status ADD VALUE 'refusee'");
        } elseif ($driver === 'sqlite') {
            // SQLite: Créer une nouvelle table avec la structure modifiée
            Schema::create('reservations_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('equipment_id')
                    ->constrained((new \App\Models\Equipment)->getTable())
                    ->cascadeOnDelete();
                $table->date('date_debut');
                $table->date('date_fin');
                $table->enum('statut', ['en_attente', 'confirmee', 'en_cours', 'terminee', 'litige', 'annulee', 'refusee'])
                    ->default('en_attente');
                $table->decimal('prix_total', 8, 2)->default(0);
                $table->timestamps();
            });

            // Copier les données
            DB::statement('INSERT INTO reservations_new SELECT * FROM reservations');

            // Supprimer l'ancienne table et renommer
            Schema::drop('reservations');
            DB::statement('ALTER TABLE reservations_new RENAME TO reservations');
        }
    }

    public function down(): void
    {
        // Migration non-destructive car elle ajoute seulement une nouvelle valeur
        // Le down serait complexe et non-nécessaire en production
    }
};
