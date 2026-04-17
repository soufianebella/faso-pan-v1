<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Campagne;
use App\Models\Face;
use App\Models\Panneau;
use App\Models\Tache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class StatistiqueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Panneau::class);

        // Toutes les requêtes SQL en une seule fois
        // MySQL fait les COUNT — pas PHP
        $stats = [

            'panneaux' => [
                'total'        => Panneau::count(),
                'actifs'       => Panneau::where('statut', 'actif')->count(),
                'maintenance'  => Panneau::where('statut', 'maintenance')->count(),
                'hors_service' => Panneau::where('statut', 'hors_service')->count(),
            ],

            'faces' => [
                'total'   => Face::count(),
                'libres'  => Face::where('statut', 'libre')->count(),
                'occupees' => Face::where('statut', 'occupee')->count(),
            ],

            'campagnes' => [
                'total'      => Campagne::count(),
                'preparation' => Campagne::where('statut', 'preparation')->count(),
                'actives'    => Campagne::where('statut', 'active')->count(),
                'expirees'   => Campagne::where('statut', 'expiree')->count(),
            ],

            'taches' => [
                'total'      => Tache::count(),
                'en_attente' => Tache::where('statut', 'en_attente')->count(),
                'en_cours'   => Tache::where('statut', 'en_cours')->count(),
                'realisees'  => Tache::where('statut', 'realisee')->count(),
                'validees'   => Tache::where('statut', 'validee')->count(),
            ],

            'taux_occupation' => Face::count() > 0
                ? round(
                    Face::where('statut', 'occupee')->count()
                        / Face::count() * 100,
                    1
                )
                : 0,
        ];

        return response()->json(['data' => $stats]);
    }
    public function dashboard(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Panneau::class);

        $aujourd_hui = now()->toDateString();
        $dans7jours  = now()->addDays(7)->toDateString();

        return response()->json(['data' => [

            // KPI cards
            'kpi' => [
                'total_panneaux'  => Panneau::count(),
                'faces_libres'    => Face::where('statut', 'libre')->count(),
                'faces_occupees'  => Face::where('statut', 'occupee')->count(),
                'expirent_7j'     => Affectation::whereBetween(
                    'date_fin',
                    [$aujourd_hui, $dans7jours]
                )->count(),
            ],

            // Taux d'occupation mensuel sur 12 mois
            'occupation_mensuelle' => $this->occupationMensuelle(),

            // Campagnes qui expirent bientôt
            'expirent_bientot' => Affectation::with([
                'campagne:id,nom,annonceur',
                'face.panneau:id,reference',
            ])
                ->whereBetween('date_fin', [$aujourd_hui, $dans7jours])
                ->orderBy('date_fin')
                ->limit(5)
                ->get()
                ->map(fn($a) => [
                    'campagne'  => $a->campagne->nom,
                    'panneau'   => $a->face->panneau->reference,
                    'jours'     => now()->diffInDays($a->date_fin),
                ]),

            // Répartition par ville
            'par_ville' => Panneau::selectRaw('ville, COUNT(*) as total')
                ->groupBy('ville')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),

            // Campagnes actives avec avancement tâches
            'campagnes_actives' => Campagne::with(['affectations.tache'])
                ->whereIn('statut', ['preparation', 'active'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn($c) => [
                    'id'         => $c->id,
                    'nom'        => $c->nom,
                    'annonceur'  => $c->annonceur,
                    'avancement' => $this->calculerAvancement($c),
                ]),

            // Dernières tâches
            'dernieres_taches' => Tache::with([
                'agent:id,name',
                'affectation.face.panneau:id,reference',
            ])
                ->whereNotNull('agent_id')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn($t) => [
                    'agent'    => $t->agent->name,
                    'panneau'  => $t->affectation->face->panneau->reference,
                    'face'     => $t->affectation->face->numero,
                    'statut'   => $t->statut,
                    'date'     => $t->updated_at->diffForHumans(),
                ]),
        ]]);
    }

    private function occupationMensuelle(): array
    {
        $mois = [];
        for ($i = 11; $i >= 0; $i--) {
            $debut = now()->subMonths($i)->startOfMonth()->toDateString();
            $fin   = now()->subMonths($i)->endOfMonth()->toDateString();

            $total   = Face::count();
            $occupees = Affectation::where('date_debut', '<=', $fin)
                ->where('date_fin', '>=', $debut)
                ->distinct('face_id')
                ->count('face_id');

            $mois[] = [
                'mois'  => now()->subMonths($i)->translatedFormat('M'),
                'taux'  => $total > 0 ? round($occupees / $total * 100, 1) : 0,
            ];
        }
        return $mois;
    }

    private function calculerAvancement(Campagne $campagne): int
    {
        $taches = $campagne->affectations
            ->pluck('tache')
            ->filter();

        if ($taches->isEmpty()) return 0;

        $validees = $taches->where('statut', 'validee')->count();
        return (int) round($validees / $taches->count() * 100);
    }
}
