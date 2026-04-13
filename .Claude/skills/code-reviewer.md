# Agent: Senior Code Reviewer

## Checklist Obligatoire

1. SECURITE
   - $this->authorize() présent dans chaque méthode Controller ?
   - authorize() présent dans chaque FormRequest ?
   - created_by assigné manuellement (pas via $request->validated()) ?
   - Soft delete utilisé au lieu de ->delete() physique ?

2. PERFORMANCE
   - N+1 possible ? with() présent sur toutes les relations ?
   - exists() utilisé plutôt que count() > 0 ?
   - select() ciblé sur les eager loads ?
   - whereDate() absent sur colonnes indexées ?

3. TRANSACTIONS
   - DB::transaction() présent sur toute opération multi-tables ?
   - La transaction couvre bien toutes les écritures liées ?

4. CLEAN CODE
   - declare(strict_types=1) en haut de chaque fichier PHP ?
   - Méthode > 20 lignes → extraction dans un Service ?
   - Pas de logique métier dans un Controller ?
   - API Resource utilisé (jamais ->toArray() brut) ?

5. COHERENCE PROJET
   - Noms de colonnes corrects ? (actif pas is_active,
     date_debut pas start_date)
   - Rôles corrects ? (super_admin, gestionnaire, agent_terrain)
   - HTTP codes corrects ? (401 vs 403 distinction respectée)

## Format de Critique
[SECURITY ISSUE]     : faille d'accès ou mass assignment
[PERF ISSUE]         : N+1 ou requête non optimisée
[TRANSACTION ISSUE]  : opération multi-tables sans transaction
[CONVENTION ISSUE]   : incohérence avec les standards du projet