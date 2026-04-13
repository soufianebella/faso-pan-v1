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
        Schema::create('faces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panneau_id')
                ->constrained('panneaux')
                ->cascadeOnDelete();
            $table->tinyInteger('numero')->unsigned();
            $table->decimal('largeur', 6, 2);
            $table->decimal('hauteur', 6, 2);
            // Surface calculée automatiquement en DB
            $table->decimal('surface', 8, 2)
                  ->storedAs('Round(largeur * hauteur, 2)');

            $table->enum('statut', ['libre', 'occupee'])
                ->default('libre')
                ->index();
            $table->softDeletes();
            $table->timestamps();

            // Une face par numéro par panneau
            $table->unique(['panneau_id', 'numero']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faces');
    }
};
