# Skill: Fasopan Engine

## 1. Anti Double-Booking
Vérification de chevauchement — syntaxe réelle du projet :

->where('date_debut', '<=', $fin)
->where('date_fin',   '>=', $debut)

Couches de protection (dans cet ordre) :
1. UI      : affiche uniquement les faces libres sur la période
2. Service : DisponibiliteService::conflits() — une requête SQL
3. DB      : DB::transaction() autour de toute affectation

Colonnes réelles : date_debut, date_fin (type DATE, pas DATETIME)
Index composite  : (face_id, date_fin, date_debut)

## 2. Règles Eloquent
- Lazy loading interdit — toujours with() explicite
- exists() plutôt que count() > 0
- select() ciblé sur les relations (jamais SELECT *)
- whereDate() interdit sur colonnes indexées — brise l'index

## 3. Transactions
DB::transaction() obligatoire sur toute opération qui :
- Crée une affectation + met à jour le statut de la face
- Crée une tâche automatiquement après une affectation
- Modifie plusieurs tables en une seule action métier

## 4. Patterns Service établis
CampagneService::create() = modèle de référence
  1. DB::transaction()
  2. Campagne::create() sans face_ids
  3. foreach face_ids → affectation + Face::update + tache
  4. return $campagne->load(relations)

DisponibiliteService::conflits() = pattern anti double-booking
  → whereIn + chevauche scope + pluck = 1 seule requête SQL

## 5. Noms de colonnes réels
Face       : panneau_id, numero, largeur, hauteur, surface, statut
Affectation: campagne_id, face_id, date_debut, date_fin
Tache      : affectation_id, agent_id, statut, realise_at, valide_at