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
        Schema::create('taches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affectation_id')
                ->constrained('affectations')
                ->cascadeOnDelete();
            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->enum('statut', [
                'en_attente',
                'en_cours',
                'realisee',
                'validee'
            ])->default('en_attente')->index();
            $table->text('note')->nullable();
            $table->string('photo_path', 500)->nullable();
            $table->decimal('latitude_pose', 10, 7)->nullable();
            $table->decimal('longitude_pose', 10, 7)->nullable();
            $table->timestamp('realise_at')->nullable();
            $table->timestamp('valide_at')->nullable();
            $table->foreignId('valide_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taches');
    }
};
