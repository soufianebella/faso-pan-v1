<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Affectation;
use App\Models\Campagne;
use App\Models\Face;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoResetCommand extends Command
{
    protected $signature   = 'demo:reset {--force : Ignore la confirmation}';
    protected $description = 'Remet la base dans un état propre pour la démo (tâches en_attente supprimées, nouvelles affectations créées)';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Réinitialiser les données de démo ?', true)) {
            $this->info('Annulé.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            // ── 1. Supprimer les tâches en_attente (libère les affectations)
            $deleted = Tache::where('statut', 'en_attente')->delete();
            $this->line("  ✓ <comment>{$deleted}</comment> tâche(s) en_attente supprimée(s)");

            // ── 2. Créer une campagne demo fraîche si elle n'existe pas encore
            $admin    = User::first();
            $campagne = Campagne::firstOrCreate(
                ['nom' => 'DEMO — Nouvelle Campagne'],
                [
                    'annonceur'   => 'Annonceur Démo',
                    'description' => 'Campagne générée automatiquement pour la démo.',
                    'date_debut'  => now()->addDays(2)->toDateString(),
                    'date_fin'    => now()->addMonths(3)->toDateString(),
                    'statut'      => 'preparation',
                    'created_by'  => $admin?->id,
                ]
            );
            $this->line("  ✓ Campagne <comment>{$campagne->nom}</comment> (id={$campagne->id})");

            // ── 3. Créer des affectations sur des faces sans aucun conflit
            // Priorité : faces sans aucune affectation du tout (garanti zéro conflit)
            $faces   = Face::doesntHave('affectations')->take(4)->get();
            $created = 0;

            foreach ($faces as $face) {
                Affectation::firstOrCreate(
                    ['campagne_id' => $campagne->id, 'face_id' => $face->id],
                    ['date_debut'  => $campagne->date_debut, 'date_fin' => $campagne->date_fin]
                );
                $created++;
            }

            $this->line("  ✓ <comment>{$created}</comment> affectation(s) créée(s)");
        });

        $dispo = Affectation::whereDoesntHave('tache')->count();
        $this->newLine();
        $this->info("✅ Démo prête — <comment>{$dispo}</comment> affectation(s) disponible(s) pour la création de tâches.");

        return self::SUCCESS;
    }
}
