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
        Schema::create('panneaux', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            //Localisation
            $table->string('pays', 100)->default('Burkina Faso');
            $table->string('ville', 100);
            $table->string('quartier', 150)->nullable();
            $table->text('adresse')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->boolean('eclaire')->default(false);
            $table->decimal('hauteur_mat', 5, 2)->nullable();
            $table->enum('statut', [
                'actif',
                'maintenance',
                'hors_service'
            ])->default('actif')->index();
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();
            // Indexes pour les recherches fréquentes
            $table->index(['ville', 'statut']);
            $table->index(['statut','deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panneaux');
    }
};