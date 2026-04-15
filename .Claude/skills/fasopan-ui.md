## 1. Palette Officielle — Thème Bleu/Orange

SIDEBAR & NAVIGATION
  Sidebar fond     : #1B3B8A  (bleu royal )
  Sidebar texte    : #FFFFFF
  Item actif fond  : rgba(249,115,22,0.15)  (orange transparent)
  Item actif texte : #F97316
  Item actif border: #F97316  (bordure gauche 3px)
  Item hover       : rgba(255,255,255,0.08)
  Logo texte       : #FFFFFF
  Logo accent      : #F97316

CONTENU PRINCIPAL
  Page bg          : #F0F4FF  (blanc bleuté — pas de gris pur)
  Card bg          : #FFFFFF
  Header bg        : #FFFFFF
  Header border-b  : #E5E7EB

ACTIONS & ACCENTS
  Bouton primaire  : #F97316
  Bouton hover     : #EA6C0A
  Bouton texte     : #FFFFFF
  Liens            : #F97316
  Focus ring       : #F97316
  Titre H1         : #1B3B8A
  Texte body       : #374151
  Texte secondaire : #6B7280

INPUTS FORMULAIRE
  Bordure normale  : #E5E7EB
  Bordure focus    : #F97316
  Fond input       : #FFFFFF
  Placeholder      : #9CA3AF

STATUTS SÉMANTIQUES
  Libre            : bg-green-100   text-green-700
  Occupé           : bg-red-100     text-red-700
  Alerte J-7       : bg-orange-100  text-orange-700
  Pending          : bg-blue-100    text-blue-700

RÈGLES STRICTES
  Texte sur #F97316  → toujours #FFFFFF
  Texte sur #1B3B8A  → toujours #FFFFFF
  #F97316 sur blanc  → ratio 3.1:1 → boutons/accents uniquement
  #1B3B8A sur blanc  → ratio 7.8:1 → titres et texte 

## 2. Layout Global
100vh strict : <div class="h-screen overflow-hidden flex">
Sidebar fixe : w-60 bg-[#0D2137] (240px, jamais collapsible en MVP)
Main content : flex-1 overflow-y-auto bg-[#F4F6F9]

## 3. Règles Vue.js
- storeToRefs(store) obligatoire pour la réactivité en template
- Skeleton loader pendant tout appel Axios (jamais de spinner global)
- Toast systématique après toute action (succès vert, erreur rouge)
- Composition API uniquement — pas d'Options API
- defineAsyncComponent sur les vues lourdes

## 4. Conventions Fichiers
Views    : src/views/{domaine}/{Nom}View.vue
Stores   : src/stores/{domaine}.store.js
API      : src/api/{domaine}.api.js
Layouts  : src/layouts/AppLayout.vue

## 5. Iconographie
CDN : Font Awesome 6.5.1 (via cdnjs.cloudflare.com)
Chargement : index.html — balise link dans <head>
Syntaxe    : <i class="fa-solid fa-{nom}"></i>
Style      : toujours fa-solid (pas fa-regular en MVP)
Taille     : heritee du parent via font-size ou classe Tailwind
Couleur    : heritee du parent via color ou classe Tailwind text-*

Regles :
- Jamais d'emoji dans le code — Font Awesome uniquement
- Icone + label dans la navigation (jamais icone seule)
- Icone seule uniquement dans les boutons d'action de tableau
  avec tooltip via title=""

  ## 6. Patterns composants établis
Tous les modules suivent ce pattern :
  XxxView.vue        → container (store + modal + table + pagination)
  XxxTable.vue       → tableau avec empty state + badges statut
  XxxModal.vue       → formulaire avec watch + erreurs 422
  XxxPagination.vue  → même code partout (currentPage/lastPage/total)

Store pattern :
  - storeToRefs() obligatoire pour la réactivité
  - errors = ref(null) stocke les erreurs 422 Laravel
  - isLoading séparé par opération si nécessaire
  - throw err dans catch pour que le composant gère l'affichage