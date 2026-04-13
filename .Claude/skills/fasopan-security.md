# Skill: Fasopan Security

## 1. Authentification — État Réel
Tokens Bearer stockés dans localStorage.
Injectés via intercepteur Axios dans http.js.
NE PAS réécrire en cookies stateful — décision d'architecture
validée pour ce projet académique.

## 2. RBAC — Rôles Actifs
super_admin  : toutes les permissions
gestionnaire : panneaux, campagnes, taches, gerer utilisateurs
agent_terrain: voir panneaux, voir faces, taches.own.validate
annonceur     : voir campagnes (lecture seule — ses campagnes uniquement)

Vérification : $this->authorize() dans chaque Controller
               authorize() dans chaque FormRequest
               Jamais uniquement côté Vue.js

## 3. Règles Mass Assignment
created_by   : jamais dans $fillable — assigné manuellement
actif        : dans $fillable — modifiable par admin uniquement
               via UserPolicy::update()

## 4. Codes HTTP à respecter
401 : token absent ou invalide
403 : token valide mais droit insuffisant
422 : données invalides (FormRequest)
Ne jamais retourner 403 quand c'est un 401 et inversement.

## 5. Audit — Prévu en V2 (non implémenté)
Spatie ActivityLog à installer.
Cible : toutes les mutations sur affectations, campagnes, users.