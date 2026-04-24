<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campagne;
use App\Models\Panneau;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    // UTF-8 BOM pour qu'Excel detecte l'encodage correctement (accents)
    private const BOM = "\xEF\xBB\xBF";

    // Hint Excel : force la reconnaissance du separateur ';' quel que soit le locale
    // (evite le probleme "tout dans une seule colonne avec des ; visibles")
    private const SEP_HINT = "sep=;\n";

    public function inventaire(array $filtres): StreamedResponse
    {
        $filename = 'inventaire_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($filtres) {
            $out = fopen('php://output', 'w');
            echo self::BOM;
            echo self::SEP_HINT;

            // PHP 8.4 : $escape doit etre explicite — '' desactive l'echappement redondant
            fputcsv($out, [
                'Reference', 'Ville', 'Quartier', 'Adresse',
                'Statut panneau', 'Eclaire',
                'Face #', 'Largeur (m)', 'Hauteur (m)', 'Surface (m2)',
                'Statut face', 'Campagne en cours',
            ], ';', '"', '');

            $query = Panneau::query()
                ->with(['faces.affectationActive.campagne'])
                ->orderBy('reference');

            // LIKE plutot que egalite stricte : l'utilisateur tape "ouaga" et match "Ouagadougou"
            if (!empty($filtres['ville'])) {
                $query->where('ville', 'like', '%' . $filtres['ville'] . '%');
            }
            if (!empty($filtres['quartier'])) {
                $query->where('quartier', 'like', '%' . $filtres['quartier'] . '%');
            }
            if (!empty($filtres['statut'])) {
                $query->where('statut', $filtres['statut']);
            }
            if (isset($filtres['eclaire']) && $filtres['eclaire'] !== '' && $filtres['eclaire'] !== null) {
                // filter_var : "0"/"false" → false, "1"/"true" → true (vs (bool)"0" qui vaut true)
                $query->where('eclaire', filter_var($filtres['eclaire'], FILTER_VALIDATE_BOOLEAN));
            }

            // chunk() : charge 100 panneaux a la fois — memoire constante meme sur 10 000 lignes
            $query->chunk(100, function ($panneaux) use ($out) {
                foreach ($panneaux as $panneau) {
                    foreach ($panneau->faces as $face) {
                        fputcsv($out, [
                            $panneau->reference,
                            $panneau->ville,
                            $panneau->quartier,
                            $panneau->adresse,
                            // statut est un Backed Enum (PanneauStatus::class) → .value pour la chaîne
                            $panneau->statut->value,
                            $panneau->eclaire ? 'Oui' : 'Non',
                            $face->numero,
                            $face->largeur,
                            $face->hauteur,
                            $face->surface,
                            $face->statut,
                            $face->affectationActive?->campagne?->nom ?? '',
                        ], ';', '"', '');
                    }
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function campagnesActives(array $filtres): StreamedResponse
    {
        $filename = 'campagnes_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($filtres) {
            $out = fopen('php://output', 'w');
            echo self::BOM;
            echo self::SEP_HINT;

            fputcsv($out, [
                'Nom', 'Annonceur', 'Date debut', 'Date fin',
                'Statut', 'Nb faces reservees', 'Createur',
            ], ';', '"', '');

            $query = Campagne::query()
                ->with('createur')
                ->withCount('affectations')
                ->orderByDesc('date_debut');

            // Defaut : uniquement les campagnes actives (conformement au libelle bouton)
            $statut = $filtres['statut'] ?? 'active';
            if ($statut !== 'tous') {
                $query->where('statut', $statut);
            }

            if (!empty($filtres['annonceur'])) {
                $query->where('annonceur', 'like', '%' . $filtres['annonceur'] . '%');
            }

            $query->chunk(200, function ($campagnes) use ($out) {
                foreach ($campagnes as $campagne) {
                    fputcsv($out, [
                        $campagne->nom,
                        $campagne->annonceur,
                        $campagne->date_debut,
                        $campagne->date_fin,
                        $campagne->statut,
                        $campagne->affectations_count,
                        $campagne->createur?->name ?? '',
                    ], ';', '"', '');
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
