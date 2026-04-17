<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Affectation;
use App\Models\Campagne;
use App\Models\Face;
use App\Models\Panneau;
use App\Models\Tache;

class StatistiquesService
{
    // ── Dashboard 

    public function dashboard(): array
    {
        $aujourd_hui = now()->toDateString();
        $dans7jours  = now()->addDays(7)->toDateString();

        return [
            'kpi'              => $this->kpiDashboard($aujourd_hui, $dans7jours),
            'occupation_mensuelle' => $this->occupationMensuelle(),
            'expirent_bientot' => $this->expirentBientot($aujourd_hui, $dans7jours),
            'par_ville'        => $this->repartitionParVille(),
            'campagnes_actives'=> $this->campagnesActives(),
            'dernieres_taches' => $this->dernieresTaches(),
        ];
    }

    // ── Statistiques ───────────────────────────────────────────────

    public function statistiques(string $periode = 'ce_mois'): array
    {
        [$debut, $fin] = $this->resoudrePeriode($periode);

        return [
            'kpi'               => $this->kpiStatistiques($debut, $fin),
            'evolution'         => $this->evolutionParVille(),
            'top_annonceurs'    => $this->topAnnonceurs(),
            'statut_faces'      => $this->statutFaces(),
            'repartition_taches'=> $this->repartitionTaches(),
            'tableau_villes'    => $this->tableauParVille($debut, $fin),
        ];
    }

    // ── Méthodes privées 

    private function resoudrePeriode(string $periode): array
    {
        return match($periode) {
            'trimestre' => [
                now()->startOfQuarter()->toDateString(),
                now()->endOfQuarter()->toDateString(),
            ],
            default => [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ],
        };
    }

    private function kpiDashboard(string $debut, string $fin): array
    {
        return [
            'total_panneaux' => Panneau::count(),
            'faces_libres'   => Face::where('statut', 'libre')->count(),
            'faces_occupees' => Face::where('statut', 'occupee')->count(),
            'expirent_7j'    => Affectation::whereBetween(
                'date_fin', [$debut, $fin]
            )->count(),
        ];
    }

    private function kpiStatistiques(string $debut, string $fin): array
    {
        $totalFaces    = Face::count();
        $facesOccupees = Face::where('statut', 'occupee')->count();

        return [
            'taux_occupation'   => $totalFaces > 0
                ? round($facesOccupees / $totalFaces * 100, 1)
                : 0,
            'campagnes_actives' => Campagne::whereIn('statut',
                ['preparation', 'active']
            )->count(),
            'taches_attente'    => Tache::where('statut', 'en_attente')->count(),
            'revenu_estime'     => $this->calculerRevenu($debut, $fin),
        ];
    }

    private function calculerRevenu(string $debut, string $fin): int
    {
        $prixMoyenFace = 150000;

        return Affectation::where('date_debut', '<=', $fin)
            ->where('date_fin', '>=', $debut)
            ->count() * $prixMoyenFace;
    }

    private function occupationMensuelle(): array
    {
        $mois = [];
        for ($i = 11; $i >= 0; $i--) {
            $debut = now()->subMonths($i)->startOfMonth()->toDateString();
            $fin   = now()->subMonths($i)->endOfMonth()->toDateString();

            $total    = Face::count();
            $occupees = Affectation::where('date_debut', '<=', $fin)
                ->where('date_fin', '>=', $debut)
                ->distinct('face_id')
                ->count('face_id');

            $mois[] = [
                'mois' => now()->subMonths($i)->translatedFormat('M'),
                'taux' => $total > 0
                    ? round($occupees / $total * 100, 1)
                    : 0,
            ];
        }
        return $mois;
    }

    private function expirentBientot(string $debut, string $fin): array
    {
        return Affectation::with([
            'campagne:id,nom,annonceur',
            'face.panneau:id,reference',
        ])
        ->whereBetween('date_fin', [$debut, $fin])
        ->orderBy('date_fin')
        ->limit(5)
        ->get()
        ->map(fn ($a) => [
            'campagne' => $a->campagne->nom,
            'panneau'  => $a->face->panneau->reference,
            'jours'    => now()->diffInDays($a->date_fin),
        ])
        ->toArray();
    }

    private function repartitionParVille(): array
    {
        return Panneau::selectRaw('ville, COUNT(*) as total')
            ->groupBy('ville')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function campagnesActives(): array
    {
        return Campagne::with(['affectations.tache'])
            ->whereIn('statut', ['preparation', 'active'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($c) => [
                'id'         => $c->id,
                'nom'        => $c->nom,
                'annonceur'  => $c->annonceur,
                'avancement' => $this->calculerAvancement($c),
            ])
            ->toArray();
    }

    private function calculerAvancement(Campagne $campagne): int
    {
        $taches   = $campagne->affectations->pluck('tache')->filter();
        if ($taches->isEmpty()) return 0;
        return (int) round(
            $taches->where('statut', 'validee')->count()
            / $taches->count() * 100
        );
    }

    private function dernieresTaches(): array
    {
        return Tache::with([
            'agent:id,name',
            'affectation.face.panneau:id,reference',
        ])
        ->whereNotNull('agent_id')
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn ($t) => [
            'agent'   => $t->agent->name,
            'panneau' => $t->affectation->face->panneau->reference,
            'face'    => $t->affectation->face->numero,
            'statut'  => $t->statut,
            'date'    => $t->updated_at->diffForHumans(),
        ])
        ->toArray();
    }

    private function evolutionParVille(): array
    {
        $villes = Panneau::distinct()->pluck('ville')->take(3);
        $series = [];

        foreach ($villes as $ville) {
            $data = [];
            for ($i = 11; $i >= 0; $i--) {
                $debut = now()->subMonths($i)->startOfMonth()->toDateString();
                $fin   = now()->subMonths($i)->endOfMonth()->toDateString();

                $total = Face::whereHas('panneau',
                    fn ($q) => $q->where('ville', $ville)
                )->count();

                $occupees = Affectation::whereHas('face.panneau',
                    fn ($q) => $q->where('ville', $ville)
                )
                ->where('date_debut', '<=', $fin)
                ->where('date_fin',   '>=', $debut)
                ->distinct('face_id')
                ->count('face_id');

                $data[] = $total > 0
                    ? round($occupees / $total * 100, 1)
                    : 0;
            }
            $series[] = ['name' => $ville, 'data' => $data];
        }

        $categories = [];
        for ($i = 11; $i >= 0; $i--) {
            $categories[] = now()->subMonths($i)->translatedFormat('M');
        }

        return ['series' => $series, 'categories' => $categories];
    }

    private function topAnnonceurs(): array
    {
        return Campagne::selectRaw('annonceur as nom, COUNT(*) as total')
            ->groupBy('annonceur')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function statutFaces(): array
    {
        return [
            ['statut' => 'Libre',   'total' => Face::where('statut', 'libre')->count()],
            ['statut' => 'Occupee', 'total' => Face::where('statut', 'occupee')->count()],
        ];
    }

    private function repartitionTaches(): array
    {
        return [
            ['statut' => 'En attente', 'total' => Tache::where('statut', 'en_attente')->count()],
            ['statut' => 'En cours',   'total' => Tache::where('statut', 'en_cours')->count()],
            ['statut' => 'Realisee',   'total' => Tache::where('statut', 'realisee')->count()],
            ['statut' => 'Validee',    'total' => Tache::where('statut', 'validee')->count()],
        ];
    }

    private function tableauParVille(string $debut, string $fin): array
    {
        return Panneau::selectRaw('ville, COUNT(*) as total_panneaux')
            ->groupBy('ville')
            ->orderByDesc('total_panneaux')
            ->get()
            ->map(function ($row) use ($debut, $fin) {
                $faces = Face::whereHas('panneau',
                    fn ($q) => $q->where('ville', $row->ville)
                )->count();

                $occupees = Affectation::whereHas('face.panneau',
                    fn ($q) => $q->where('ville', $row->ville)
                )
                ->where('date_debut', '<=', $fin)
                ->where('date_fin',   '>=', $debut)
                ->distinct('face_id')
                ->count('face_id');

                return [
                    'nom'            => $row->ville,
                    'total_panneaux' => $row->total_panneaux,
                    'taux'           => $faces > 0
                        ? round($occupees / $faces * 100, 1)
                        : 0,
                    'ca'             => $occupees * 150000,
                ];
            })
            ->toArray();
    }
}