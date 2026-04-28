<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panneau_etats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panneau_id')
                  ->constrained('panneaux')
                  ->cascadeOnDelete();
            $table->string('statut_avant', 20);
            $table->string('statut_apres', 20);
            $table->text('motif');
            $table->foreignId('changed_by')
                  ->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->index('panneau_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panneau_etats');
    }
};
