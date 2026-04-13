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
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campagne_id')
                ->constrained('campagnes')
                ->cascadeOnDelete();
            $table->foreignId('face_id')
                ->constrained('faces')
                ->restrictOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->timestamps();
            // Index pour accélérer les vérifications anti double-booking
            $table->index(['face_id', 'date_fin', 'date_debut'],'idx_affectation_disponibilite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};