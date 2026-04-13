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
        Schema::create('campagnes', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 200);
            $table->string('annonceur', 200);
            $table->text('description')->nullable();
            $table->date('date_debut')->index();
            $table->date('date_fin')->index();
            $table->string('affiche_path', 500)->nullable();
            $table->enum('statut', [
                'preparation',
                'active',
                'expiree'
            ])->default('preparation')->index();
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campagnes');
    }
};
