# CLAUDE.md — FASO PAN

## 1. Rôle de l'Agent

Tech Lead Fullstack, 20 ans d'expérience Laravel + Vue.js.
Méthode : anti-copie stricte. Tu fais réfléchir avant de donner
du code complet. Tu corriges comme en code review d'entreprise.

Mode de travail adapté :

- Nouveaux concepts → questions de compréhension d'abord
- Patterns connus (CRUD, store, composant) → code direct
- Bugs → diagnostic collaboratif (étudiant cherche d'abord)
- Code review → systématique avant validation de session
  Niveau : étudiant L3 Informatique, Burkina Faso.

## 2. Contexte Métier

Plateforme de gestion de panneaux publicitaires — Burkina Faso.
Nom du projet : FASO PAN.
Contrainte critique : zéro double-booking sur les affectations
de faces de panneaux. Toute réservation passe par une
transaction sécurisée avec vérification de chevauchement.

## 3. Stack Réelle du Projet

Backend : Laravel 12, PHP 8.4, MySQL 8
Auth : Sanctum tokens Bearer (stockés localStorage)
RBAC : Spatie Laravel-Permission
Frontend : Vue.js 3 Composition API, Pinia, Vue Router 4
Style : Tailwind CSS v4 (plugin Vite)
Icones : Font Awesome 6.5.1 (CDN cdnjs)
Tests : Pest (backend)

## 4. Architecture Imposée

Controller → Service → Model (toujours dans cet ordre)
FormRequest : validation + authorize() sur chaque endpoint
Policy : une Policy par ressource critique
API Resource : jamais de ->toArray() brut en réponse
Transactions : DB::transaction() sur toute opération multi-tables

## 5. Structure Backend

app/
├── Http/Controllers/Api/V1/
│ ├── AuthController.php
│ ├── UserController.php
│ ├── PanneauController.php
│ ├── CampagneController.php
│ └── TacheController.php 
├── Http/Requests/
│ ├── StoreUserRequest.php
│ ├── UpdateUserRequest.php
│ ├── StorePanneauRequest.php
│ ├── UpdatePanneauRequest.php
│ └── StoreCampagneRequest.php
├── Http/Resources/
│ ├── UserResource.php
│ ├── PanneauResource.php
│ ├── FaceResource.php
│ └── CampagneResource.php
├── Models/
│ ├── User.php
│ ├── Panneau.php
│ ├── Face.php
│ ├── Campagne.php
│ ├── Affectation.php
│ ├── Tache.php
│ └── Notification.php
├── Policies/
│ ├── UserPolicy.php
│ ├── PanneauPolicy.php
│ └── CampagnePolicy.php
  └── TachePolicy.php
└── Services/
├── UserService.php
├── PanneauService.php
├── CampagneService.php
└── DisponibiliteService.php
└── TacheService.php 

## 6. Structure Frontend

src/
├── api/
│ ├── http.js ← axios + intercepteurs + token Bearer
│ ├── auth.api.js
│ ├── users.api.js
│ ├── panneaux.api.js
│ └── campagnes.api.js
├── stores/
│ ├── auth.store.js
│ ├── users.store.js
│ ├── panneaux.store.js
│ └── campagnes.store.js
├── views/
│ ├── auth/LoginView.vue
│ ├── DashboardView.vue
│ ├── users/UsersView.vue
│ ├── panneaux/PanneauxView.vue
│ └── campagnes/CampagnesView.vue
├── components/
│ ├── users/
│ │ ├── UserTable.vue
│ │ ├── UserModal.vue
│ │ └── UserPagination.vue
│ ├── panneaux/
│ │ ├── PanneauTable.vue
│ │ ├── PanneauModal.vue
│ │ └── PanneauPagination.vue
│ └── campagnes/
│ ├── CampagneTable.vue
│ ├── CampagneModal.vue
│ ├── CampagnePagination.vue
│ └── FaceSelector.vue
└── layouts/
└── AppLayout.vue

## 7. Base de Données — Tables

users : id, name, email, password, actif, deleted_at
panneaux : id, reference, pays, ville, quartier, adresse,
latitude, longitude, eclaire, hauteur_mat,
statut(actif|maintenance|hors_service),
created_by, deleted_at
faces : id, panneau_id, numero, largeur, hauteur,
surface(GENERATED storedAs), statut(libre|occupee),
deleted_at
campagnes : id, nom, annonceur, description,
date_debut, date_fin, affiche_path,
statut(preparation|active|expiree),
created_by, deleted_at
affectations : id, campagne_id, face_id, date_debut, date_fin
INDEX(face_id, date_fin, date_debut)
taches : id, affectation_id, agent_id, statut,
note, photo_path, latitude_pose, longitude_pose,
realise_at, valide_at, valide_by
notifications : id, user_id, type, titre, message, lien, lu_at

## 8. Rôles & Permissions

super_admin : toutes les permissions
gestionnaire : voir/creer/modifier panneaux, faces, campagnes,
taches, stats, gerer utilisateurs, assigner campagne
agent_terrain : voir panneaux, voir faces, taches.own.validate
annonceur : voir campagnes (lecture seule)

## 9. Palette UI

Sidebar fond : #1B3B8A (bleu royal)
Nav actif : #F97316 (orange — bordure gauche 3px)
Primary orange : #F97316 (boutons, accents)
Primary hover : #EA6C0A
Page bg : #F0F4FF
Card bg : #FFFFFF
Text main : #1C2833
Text muted : #6B7280
Statuts :
Libre/Succès : #27AE60 bg-green-100
Occupé/Erreur: #EF4444 bg-red-100
Alerte J-7 : #F97316 bg-orange-100
Pending : #1B3B8A bg-blue-100

## 10. Règles Techniques Critiques

- Controller.php doit utiliser AuthorizesRequests (pas Authorizable)
- Route login : ->name('login') obligatoire
- Route /me : ->name('v1.me') obligatoire
- apiResource avec nom pluriel :
  ->parameters(['panneaux' => 'panneau'])
  ->parameters(['campagnes' => 'campagne'])
- created_by dans $fillable sur Panneau ET Campagne
- surface face : GENERATED storedAs en MySQL (pas calculé en PHP)
- whereDate() interdit sur colonnes indexées
- exists() au lieu de count() > 0
- with() systématique (jamais de lazy loading)
- Soft deletes sur toutes les entités critiques
- DB::transaction() sur toute opération multi-tables

## 11. Anti Double-Booking — Algorithme

Deux périodes se chevauchent si :
date_debut <= $fin ET date_fin >= $debut

Implémentation :

1. UI : FaceSelector affiche uniquement faces libres
2. Service : DisponibiliteService::conflits() — 1 requête SQL
3. DB : DB::transaction() atomique dans CampagneService

Scope sur Affectation :
->where('date_debut', '<=', $fin)
->where('date_fin', '>=', $debut)

## 12. État Actuel du Projet

Semaine 1 — TERMINÉE
Session 1 : Setup Laravel + Sanctum + Spatie
Session 2 : Auth (login / logout / me)
Session 3 : RBAC (rôles + permissions)
Session 4 : Frontend Auth (Vue + Pinia + Router)

Semaine 2 — TERMINÉE
Session 5 : CRUD Users backend
Session 6 : UserPolicy
Session 7 : Frontend Users

Semaine 3 — TERMINÉE
Session 8 : Modélisation panneaux
Session 9 : CRUD Panneaux backend
Session 10 : Frontend Panneaux

Semaine 4 — TERMINÉE
Session 11 : Modèle réservation
Session 12 : Anti double-booking (5/5 tests Postman)
Session 13 : Transactions DB
Session 14 : Frontend Campagnes — TERMINÉE
Session 14b : RBAC Tâches — TERMINÉE
- Filtrage par permission can() au lieu de hasRole()
- Support complet Admin/Gestionnaire/Agent
- Blocage Annonceur (403)
- 6/6 tests TacheRBACTest validés

## 13. Bugs Résolus — Session 14

- FaceSelector : defineProps() non assigné → const props = defineProps()
- CampagneModal : watch sur dates absent → watch sur getters ajouté
- Condition fin > debut → fin >= debut (campagne 1 jour valide)
- Watch déclenche fetchAvailableFaces() + reset face_ids + clearErrors()

## 14. Bugs Résolus — Session 14b: RBAC Tâches

### Problème Identifié
- Erreur 403 Forbidden systématique sur `GET /api/v1/taches` pour l'administrateur, malgré un token valide.
- Incohérences de droits pour les rôles `agent_terrain` et `gestionnaire` lors de l'accès ou de la modification des tâches.
- Les Policies n'étaient pas correctement appliquées ou bypassées pour le `super_admin`.
- Le package Spatie Laravel-Permission n'initialisait les rôles et permissions que pour le guard `web`, ignorant le guard `sanctum` utilisé par l'API.

### Correctifs Appliqués
1.  **Bypass Global pour le Super Admin** : Ajout d'une règle `Gate::before` dans [AppServiceProvider.php](file:///c:/laragon/www/fasopan/backend/app/Providers/AppServiceProvider.php) pour autoriser le `super_admin` à toutes les actions.
2.  **Alignement des Guards Spatie** : Modification du [RolesAndPermissionsSeeder.php](file:///c:/laragon/www/fasopan/backend/database/seeders/RolesAndPermissionsSeeder.php) pour créer les permissions/rôles pour les guards `web` ET `sanctum`.
3.  **Filtrage par Permission** : Refonte de `TacheService::lister()` pour utiliser `$user->can('taches.manage')` et `$user->can('taches.own.validate')` au lieu de `hasRole()`, assurant une gestion robuste des accès Gestionnaire vs Agent.
4.  **Amélioration des Resources API** : Mise à jour de [FaceResource.php](file:///c:/laragon/www/fasopan/backend/app/Http/Resources/FaceResource.php) pour inclure la relation `panneau`.

### Validation
- Création de [TacheRBACTest.php](file:///c:/laragon/www/fasopan/backend/tests/Feature/TacheRBACTest.php).
- 6/6 scénarios validés :
    - Admin : liste toutes les tâches.
    - Gestionnaire : liste toutes les tâches.
    - Agent : liste uniquement ses tâches.
    - Annonceur : 403 Forbidden.
    - Agent : peut avancer le statut de sa tâche.
    - Gestionnaire : peut assigner un agent.

## 15. Session 14c — Frontend Tâches — TERMINÉE

### Fonctionnalités livrées
- Vue Kanban 4 colonnes (en_attente / en_cours / realisee / validee)
- Bascule Tableau ↔ Liste (TacheTable.vue avec badges d'action)
- Création de tâche : TacheCreationModal.vue (affectation + agent + note)
- Assignation agent : TacheModal.vue (gestionnaire)
- Avancement statut : boutons contextuels par rôle
- Upload photo preuve : TacheRealiserModal.vue (en_cours → realisee)
- Remplacement photo sur carte realisee : clic/hover direct sur miniature
- Filtrage sidebar par rôle (agent ne voit pas Statistiques)

### Fichiers ajoutés / modifiés
Backend :
  app/Http/Requests/AvancerTacheRequest.php  (nouveau)
  app/Http/Requests/UpdateTachePhotoRequest.php (nouveau)
  app/Http/Controllers/Api/V1/TacheController.php (store, avancer, updatePhoto)
  app/Services/TacheService.php (avancerStatut avec photo, updatePhoto)
  app/Http/Resources/TacheResource.php (photo_url, latitude_pose, longitude_pose)
  app/Policies/TachePolicy.php (updatePhoto method)
  routes/api.php (POST /taches/{tache}/photo, match patch|post avancer)

Frontend :
  src/api/taches.api.js (avancer FormData, updatePhoto)
  src/stores/taches.store.js (avancerTache payload, updatePhoto, creerTache)
  src/views/taches/TachesView.vue (Kanban, modals, photo upload inline)
  src/components/taches/TacheTable.vue (badges action)
  src/components/taches/TacheCreationModal.vue (nouveau)
  src/components/taches/TacheRealiserModal.vue (nouveau — upload + GPS)
  src/layouts/AppLayout.vue (navItems filtrés par rôle)

### Règles techniques validées
- FormData multipart : Content-Type: undefined (pas 'multipart/form-data')
  → le navigateur génère le boundary automatiquement
- ref Vue 3 dans v-for → Proxy, pas HTMLElement
  → utiliser document.getElementById() pour les inputs natifs
- Policy::update() trop restrictive pour updatePhoto (statut realisee exclu)
  → méthode dédiée updatePhoto() dans TachePolicy
- Route POST /avancer : Route::match(['patch','post'], ...) car navigateur
  n'envoie pas PATCH en multipart

## 16. Session 16 — Filtres & Recherche — TERMINÉE

### Livraisons
- IndexPanneauRequest  — valide ville, statut, eclaire, search, per_page
- IndexCampagneRequest — valide search, statut, annonceur, per_page
- IndexTacheRequest    — valide statut, agent_id, campagne_id, per_page
- CampagneController::index — logique filtre extraite vers CampagneService::lister()
- TacheService::lister()   — ajout filtres agent_id + campagne_id
- panneaux.store : filtre eclaire ajouté
- taches.store   : filtre campagne_id ajouté

### Règle ajoutée
- Tout endpoint index() doit avoir un FormRequest dédié
  (valide les query strings + authorize via Policy)
- Injection SQL impossible : statut validé par in:... avant d'atteindre Eloquent

## 17. Session 17 — UX + Validation — TERMINÉE

### Livraisons
- useToast.js composable — success/error/warning/info, auto-dismiss, état partagé
- ToastContainer.vue — teleport body, animation slide, palette UI
- ConfirmModal.vue — v-model, 3 variants danger/warning/primary, action callback
- AppLayout.vue — ToastContainer monté une fois pour toute l'app
- 12 alert()/confirm() remplacés dans TachesView, PanneauxView, CampagnesView, UsersView

## 18. Session 18 — Sécurité Avancée — TERMINÉE

### Vulnérabilités corrigées

**1. Privilege Escalation — UpdateUserRequest**
- Avant : `role` et `actif` dans rules() sans condition → un agent pouvait
  tenter `PATCH /users/{id}` avec `role=super_admin`
- Après : `$canManage = $user->can('gerer utilisateurs')`
  → `['prohibited']` si non-gestionnaire (défense en profondeur)

**2. Route orpheline — AuthController::updateProfile**
- `PUT /me` enregistrée mais méthode absente → 500
- Ajout de `updateProfile()` avec validation name/email/password
  et protection unique() sur email

**3. AffectationResource — parent::toArray() brut**
- Violation de la règle "jamais de toArray() brut"
- Remplacé par champs explicites + whenLoaded('campagne', 'face')

**4. Rate Limiting global**
- `throttle:60,1` ajouté au middleware group `auth:sanctum`
- Login déjà protégé par `throttle:5,1` (inchangé)

### Règles de sécurité validées
- `prohibited` rule = refus avec message d'erreur 422 (meilleur que ignorer)
- Rate limiting granulaire : login strict (5/min), API large (60/min)
- Resource API : toujours lister les champs explicitement
- updateProfile ≠ updateUser : pas de role/actif sur /me

### Prochaine Étape
Session 19 : Refactor
Session 20 : Tests finaux
Session 21 : Préparation démo

## 19. Session 19 — Refactor — TERMINÉE

### Corrections appliquées

**1. Face.$fillable — bug silencieux critique**
- `statut` absent du $fillable → mises à jour ignorées silencieusement
- Ajout de `'statut'` dans Face::$fillable

**2. AuthController — logique métier extraite vers UserService**
- `updateProfile()` contenait hash password + update dans le controller
- Ajout de `UserService::updateProfile(array $data): User`
- AuthController délègue au service (pattern Controller→Service respecté)
- Import `Hash` orphelin supprimé

**3. CampagneController::facesDisponibles — FormRequest**
- Validation inline `$request->validate()` dans le controller
- Création de `IndexFacesDisponiblesRequest` (authorize + rules)
- Controller utilise `$request->validated()` uniquement

**4. AffectationResource — parent::toArray() supprimé**
- Remplacé par champs explicites + whenLoaded('campagne', 'face')

**5. Stubs vides supprimés (6 fichiers orphelins)**
- Controllers : FaceController.php, AffectationController.php
- Requests : UpdateCampagneRequest.php (authorize:false dangereux),
  StoreAffectationRequest.php, StoreFaceRequest.php, UpdateTacheRequest.php

### Règles de refactor validées
- Controller ne hash jamais un password — c'est le Service
- Tout endpoint → son FormRequest dédié (même GET avec query strings)
- Stubs non utilisés = dette technique + risque sécurité (authorize:false)

## 20. Session 19b — Export CSV — TERMINÉE

### Livraisons

**Backend**
- ExportService — streaming via response()->streamDownload() + chunk(100)
  → mémoire constante, même sur 10 000 panneaux
- Inventaire : référence, ville, face, surface, statut, campagne en cours
- Campagnes : nom, annonceur, dates, statut, nb faces, créateur
- UTF-8 BOM (\xEF\xBB\xBF) en tête → Excel détecte les accents
- fputcsv avec séparateur ';' (standard Excel francophone)

**FormRequests**
- ExportInventaireRequest : ville, statut (in:...), eclaire (boolean)
- ExportCampagnesRequest  : annonceur, statut (in:...+tous)
- authorize via permissions Spatie

**Frontend**
- exports.api.js : axios direct (pas l'instance http partagée)
  → car l'interceptor retourne response.data et on perd les headers
  → Bearer token ré-injecté manuellement
- Blob → URL.createObjectURL → <a download> → revoke
- Nom de fichier extrait du Content-Disposition header
- ExportModal : choix type + filtres conditionnels, state loading

### Bugs évités
- window.open : n'envoie PAS le Bearer token → 401
- (bool) "0" vaut true en PHP → filter_var(..., FILTER_VALIDATE_BOOLEAN)
- Content-Type: 'text/csv; charset=UTF-8' obligatoire pour Excel

### Prochaine Étape
Session 20 : Tests finaux
Session 21 : Préparation démo

## 15. Comment Reprendre dans une Nouvelle Conversation

Colle ce fichier CLAUDE.md en début de conversation avec :
"Tu es mon Tech Lead sur le projet FASO PAN.
Voici le contexte complet. On reprend à [session X]."

## 16. Skills Activés

| Situation                | Skill à lire                       |
| ------------------------ | ---------------------------------- |
| Générer du code Laravel  | .claude/skills/fasopan-engine.md   |
| Vérifier sécurité/accès  | .claude/skills/fasopan-security.md |
| Créer un composant Vue   | .claude/skills/fasopan-ui.md       |
| Code review d'un fichier | .claude/skills/code-reviewer.md    |

### Règle d'activation automatique

- Toute nouvelle route Laravel → lire fasopan-security.md
- Tout nouveau composant Vue → lire fasopan-ui.md
- Toute logique métier → lire fasopan-engine.md
- Toute correction de code → lire code-reviewer.md
