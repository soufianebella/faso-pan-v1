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
    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function dashboard(): array
    {
        $aujourd_hui = now()->toDateString();
        $dans7jours  = now()->addDays(7)->toDateString();

        return [
            'kpi'                  => $this->kpiDashboard($aujourd_hui, $dans7jours),
            'occupation_mensuelle' => $this->occupationMensuelle(),
            'expirent_bientot'     => $this->expirentBientot($aujourd_hui, $dans7jours),
            'par_ville'            => $this->repartitionParVille(),
            'campagnes_actives'    => $this->campagnesActives(),
            'dernieres_taches'     => $this->dernieresTaches(),
        ];
    }

    // ── Statistiques ──────────────────────────────────────────────────────────

    public function statistiques(string $periode = 'ce_mois'): array
    {
        [$debut, $fin] = $this->resoudrePeriode($periode);

        return [
            'kpi'                => $this->kpiStatistiques($debut, $fin),
            'evolution'          => $this->evolutionParVille(),
            'top_annonceurs'     => $this->topAnnonceurs(),
            'statut_faces'       => $this->statutFaces(),
            'repartition_taches' => $this->repartitionTaches(),
            'tableau_villes'     => $this->tableauParVille($debut, $fin),
        ];
    }

    // ── Privées ───────────────────────────────────────────────────────────────

    private function resoudrePeriode(string $periode): array
    {
        return match ($periode) {
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
        // 1 requête pour libre + occupee au lieu de 2 COUNT séparés
        $statutFaces = Face::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        return [
            'total_panneaux' => Panneau::count(),
            'faces_libres'   => $statutFaces->get('libre', 0),
            'faces_occupees' => $statutFaces->get('occupee', 0),
            'expirent_7j'    => Affectation::whereBetween('date_fin', [$debut, $fin])->count(),
        ];
    }

    private function kpiStatistiques(string $debut, string $fin): array
    {
        // 1 requête pour tous les statuts de faces
        $statutFaces   = Face::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $totalFaces    = $statutFaces->sum();
        $facesOccupees = $statutFaces->get('occupee', 0);

        return [
            'taux_occupation'   => $totalFaces > 0
                ? round($facesOccupees / $totalFaces * 100, 1)
                : 0,
            'campagnes_actives' => Campagne::whereIn('statut', ['preparation', 'active'])->count(),
            'taches_attente'    => Tache::where('statut', 'en_attente')->count(),
            'revenu_estime'     => $this->calculerRevenu($debut, $fin),
        ];
    }

    private function calculerRevenu(string $debut, string $fin): int
    {
        // Prix moyen par face — valeur métier Burkina Faso
        $prixMoyenFace = 150_000;

        return Affectation::where('date_debut', '<=', $fin)
            ->where('date_fin', '>=', $debut)
            ->count() * $prixMoyenFace;
    }

    private function occupationMensuelle(): array
    {
        $totalFaces  = Face::count();
        $annee       = now()->year;
        $moisActuel  = now()->month;

        // Fenêtre : Jan → Déc de l'année courante
        $debutAnnee = "{$annee}-01-01";
        $finAnnee   = "{$annee}-12-31";

        // 1 seule requête pour toute l'année
        $affectations = Affectation::select(['face_id', 'date_debut', 'date_fin'])
            ->where('date_debut', '<=', $finAnnee)
            ->where('date_fin',   '>=', $debutAnnee)
            ->get();

        $mois = [];
        // Toujours Jan (1) → Déc (12) — ordre croissant fixe
        for ($m = 1; $m <= 12; $m++) {
            $date  = \Carbon\Carbon::create($annee, $m, 1);
            $debut = $date->startOfMonth()->toDateString();
            $fin   = $date->copy()->endOfMonth()->toDateString();

            // Mois futurs → taux 0 (pas encore de données)
            $occupees = $m <= $moisActuel
                ? $affectations
                    ->filter(fn ($a) => $a->date_debut <= $fin && $a->date_fin >= $debut)
                    ->pluck('face_id')
                    ->unique()
                    ->count()
                : 0;

            $mois[] = [
                'mois' => $date->locale('en')->isoFormat('MMM'), // ex: Jan, Feb...
                'taux' => $totalFaces > 0
                    ? round($occupees / $totalFaces * 100, 1)
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
            'campagne' => $a->campagne?->nom ?? 'N/A',
            'panneau'  => $a->face?->panneau?->reference ?? 'N/A',
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
        // withCount évite de charger toutes les affectations + tâches en mémoire
        return Campagne::withCount([
            'affectations as total_taches'   => fn ($q) => $q->whereHas('tache'),
            'affectations as taches_valides' => fn ($q) => $q->whereHas(
                'tache', fn ($t) => $t->where('statut', 'validee')
            ),
        ])
        ->whereIn('statut', ['preparation', 'active'])
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn ($c) => [
            'id'         => $c->id,
            'nom'        => $c->nom,
            'annonceur'  => $c->annonceur,
            'avancement' => $c->total_taches > 0
                ? (int) round($c->taches_valides / $c->total_taches * 100)
                : 0,
        ])
        ->toArray();
    }

    private function dernieresTaches(): array
    {
        return Tache::with([
            'agent:id,name',
            'affectation.face.panneau:id,reference',
        ])
        ->whereNotNull('agent_id')
        ->whereHas('affectation.face.panneau') // garde uniquement les tâches avec relations intactes
        ->latest()
        ->limit(5)
        ->get()
        ->map(fn ($t) => [
            'agent'   => $t->agent->name,
            'panneau' => $t->affectation?->face?->panneau?->reference ?? 'N/A',
            'face'    => $t->affectation?->face?->numero ?? '?',
            'statut'  => $t->statut,
            'date'    => $t->updated_at->diffForHumans(),
        ])
        ->toArray();
    }

    private function evolutionParVille(): array
    {
        $villes      = Panneau::distinct()->pluck('ville')->take(3);
        $annee       = now()->year;
        $moisActuel  = now()->month;
        $debutAnnee  = "{$annee}-01-01";
        $finAnnee    = "{$annee}-12-31";

        // 1 requête : faces indexées par ville
        $facesParVille = Face::select(['id', 'panneau_id'])
            ->with('panneau:id,ville')
            ->get()
            ->groupBy('panneau.ville')
            ->map(fn ($faces) => $faces->pluck('id'));

        // 1 requête : toutes les affectations de l'année courante
        $affectations = Affectation::select(['face_id', 'date_debut', 'date_fin'])
            ->where('date_debut', '<=', $finAnnee)
            ->where('date_fin',   '>=', $debutAnnee)
            ->get();

        // Categories Jan → Déc fixes
        $categories = [];
        for ($m = 1; $m <= 12; $m++) {
            $categories[] = \Carbon\Carbon::create($annee, $m, 1)
                ->locale('en')
                ->isoFormat('MMM'); // Jan, Feb, Mar...
        }

        $series = [];
        foreach ($villes as $ville) {
            $faceIds = $facesParVille->get($ville, collect());
            $total   = $faceIds->count();
            $data    = [];

            for ($m = 1; $m <= 12; $m++) {
                if ($m > $moisActuel) {
                    $data[] = 0;
                    continue;
                }

                $date  = \Carbon\Carbon::create($annee, $m, 1);
                $debut = $date->startOfMonth()->toDateString();
                $fin   = $date->copy()->endOfMonth()->toDateString();

                $occupees = $affectations
                    ->filter(fn ($a) =>
                        $faceIds->contains($a->face_id)
                        && $a->date_debut <= $fin
                        && $a->date_fin   >= $debut
                    )
                    ->pluck('face_id')
                    ->unique()
                    ->count();

                $data[] = $total > 0 ? round($occupees / $total * 100, 1) : 0;
            }

            $series[] = ['name' => $ville, 'data' => $data];
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
        // 1 requête GROUP BY au lieu de 2 COUNT séparés
        $counts = Face::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        return [
            ['statut' => 'Libre',   'total' => $counts->get('libre', 0)],
            ['statut' => 'Occupee', 'total' => $counts->get('occupee', 0)],
        ];
    }

    private function repartitionTaches(): array
    {
        // 1 requête GROUP BY au lieu de 4 COUNT séparés
        $counts = Tache::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        return [
            ['statut' => 'En attente', 'total' => $counts->get('en_attente', 0)],
            ['statut' => 'En cours',   'total' => $counts->get('en_cours', 0)],
            ['statut' => 'Realisee',   'total' => $counts->get('realisee', 0)],
            ['statut' => 'Validee',    'total' => $counts->get('validee', 0)],
        ];
    }

    private function tableauParVille(string $debut, string $fin): array
    {
        // Requête 1 : nombre de faces par ville
        $facesParVille = Face::selectRaw('panneaux.ville, COUNT(faces.id) as total')
            ->join('panneaux', 'panneaux.id', '=', 'faces.panneau_id')
            ->groupBy('panneaux.ville')
            ->pluck('total', 'ville');

        // Requête 2 : faces occupées par ville sur la période
        $occupeesParVille = Affectation::selectRaw(
            'panneaux.ville, COUNT(DISTINCT affectations.face_id) as occupees'
        )
        ->join('faces',    'faces.id',    '=', 'affectations.face_id')
        ->join('panneaux', 'panneaux.id', '=', 'faces.panneau_id')
        ->where('affectations.date_debut', '<=', $fin)
        ->where('affectations.date_fin',   '>=', $debut)
        ->groupBy('panneaux.ville')
        ->pluck('occupees', 'ville');

        return Panneau::selectRaw('ville, COUNT(*) as total_panneaux')
            ->groupBy('ville')
            ->orderByDesc('total_panneaux')
            ->get()
            ->map(function ($row) use ($facesParVille, $occupeesParVille) {
                $faces    = $facesParVille->get($row->ville, 0);
                $occupees = $occupeesParVille->get($row->ville, 0);

                return [
                    'nom'            => $row->ville,
                    'total_panneaux' => $row->total_panneaux,
                    'taux'           => $faces > 0
                        ? round($occupees / $faces * 100, 1)
                        : 0,
                    'ca'             => $occupees * 150_000,
                ];
            })
            ->toArray();
    }
}
