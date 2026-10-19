# Test du formulaire simplifié "Nouvelle Réclamation"

## Objectif
Vérifier que le formulaire "Nouvelle Réclamation" fonctionne correctement après suppression du champ de description générale.

## Date du test
17 octobre 2026

## Prérequis
- [ ] Application Laravel en cours d'exécution: `php artisan serve`
- [ ] Base de données accessible
- [ ] Utilisateur authentifié avec des réservations

## Test 1: Affichage du formulaire

### Étapes
1. Naviguer vers `/reclamations/create`
2. Vérifier le contenu du formulaire

### Vérifications
- [ ] Titre du formulaire: "Nouvelle Réclamation"
- [ ] Champ "Sélectionnez une réservation" visible
- [ ] Champ "Titre" visible (titre de la réclamation)
- [ ] Champ "Description" **NOT visible** ❌ (supprimé)
- [ ] Section "Incidents" visible
- [ ] Bouton "+ Ajouter un incident" visible
- [ ] Boutons "Soumettre" et "Annuler" visibles

### Résultat
- [ ] PASS - Formulaire simplifié s'affiche correctement
- [ ] FAIL - Problème d'affichage

---

## Test 2: Ajout d'incident

### Étapes
1. Sélectionner une réservation
2. Cliquer sur "+ Ajouter un incident"

### Vérifications
- [ ] Nouvel incident apparaît (Incident #1)
- [ ] Champs visibles:
  - [ ] Équipement (disabled)
  - [ ] Type de problème (dropdown)
  - [ ] Titre du problème (input)
  - [ ] ✨ Bouton IA visible
  - [ ] Description du problème (textarea)
  - [ ] Photo/Preuve (file upload)
- [ ] Bouton "Supprimer" pour l'incident visible

### Résultat
- [ ] PASS - Incident ajouté avec tous les champs
- [ ] FAIL - Problème lors de l'ajout

---

## Test 3: Ajout de plusieurs incidents

### Étapes
1. Cliquer plusieurs fois sur "+ Ajouter un incident"
2. Vérifier que chaque incident est indépendant

### Vérifications
- [ ] Incident #1 visible
- [ ] Incident #2 visible
- [ ] Incident #3 visible
- [ ] Chaque incident a un bouton "Supprimer"
- [ ] Les incidents sont numérotés correctement

### Résultat
- [ ] PASS - Plusieurs incidents fonctionnent
- [ ] FAIL - Problème avec les incidents multiples

---

## Test 4: Suppression d'incident

### Étapes
1. Ajouter 2 incidents
2. Cliquer sur "Supprimer" pour le premier

### Vérifications
- [ ] Incident #1 supprimé
- [ ] Incident #2 reste visible (maintenant le seul)
- [ ] Les numérotations sont cohérentes

### Résultat
- [ ] PASS - Suppression d'incident fonctionne
- [ ] FAIL - Problème de suppression

---

## Test 5: Remplissage du formulaire

### Étapes
1. Remplir le formulaire:
   - Sélectionner une réservation
   - Entrer un titre (ex: "Problème avec l'équipement")
   - Pour chaque incident:
     - Sélectionner un type (ex: "Endommagé")
     - Entrer un titre (ex: "Panneau solaire fissué")
     - Entrer une description (ex: "Le panneau a une grosse fissure...")
     - Optionnel: télécharger une photo

### Vérifications
- [ ] Tous les champs acceptent les données
- [ ] Pas de champs obligatoires manquants
- [ ] Le formulaire est complet

### Résultat
- [ ] PASS - Formulaire complètement rempli
- [ ] FAIL - Problème de remplissage

---

## Test 6: Génération IA

### Étapes
1. Remplir les champs réservation, type et titre d'incident
2. Cliquer sur "✨ Générer la description avec l'IA"

### Vérifications
- [ ] Indicateur de chargement apparaît ("⏳ Génération en cours...")
- [ ] Bouton IA désactivé pendant la génération
- [ ] Après 5-15 secondes, description générée apparaît
- [ ] Message de succès s'affiche puis disparaît
- [ ] Description générée est en français
- [ ] L'utilisateur peut éditer la description générée

### Résultat
- [ ] PASS - Génération IA fonctionne
- [ ] FAIL - Problème de génération (noter l'erreur)

---

## Test 7: Validation du formulaire

### Étapes
1. Laisser un champ obligatoire vide
2. Cliquer sur "Soumettre la Réclamation"

### Vérifications
- [ ] Messages d'erreur s'affichent
- [ ] Les champs vides sont mis en évidence
- [ ] Erreurs spécifiques à chaque champ
- [ ] Réclamation n'est pas soumise

### Résultat
- [ ] PASS - Validation fonctionne
- [ ] FAIL - Validation non fonctionnelle

---

## Test 8: Soumission du formulaire

### Étapes
1. Remplir complètement le formulaire:
   - Réservation sélectionnée
   - Titre de la réclamation
   - Au moins 1 incident avec:
     - Type
     - Titre
     - Description
2. Cliquer sur "Soumettre la Réclamation"

### Vérifications
- [ ] Formulaire soumis sans erreur
- [ ] Redirection vers la page de réclamation créée
- [ ] Message de succès: "Réclamation créée avec succès."
- [ ] URL: `/reclamations/{id}`

### Résultat
- [ ] PASS - Soumission réussie
- [ ] FAIL - Erreur lors de la soumission

---

## Test 9: Vérification en base de données

### Commandes SQL
```sql
-- Vérifier la dernière réclamation créée
SELECT id, title, description, status FROM reclamations ORDER BY id DESC LIMIT 1;

-- Vérifier les incidents de cette réclamation
SELECT id, titre, description, type_probleme FROM incidents 
WHERE reclamation_id = [ID] ORDER BY id;
```

### Vérifications
- [ ] `reclamations.title` contient le titre saisi
- [ ] `reclamations.description` = `NULL` (pas de description générale)
- [ ] `incidents.titre` contient le titre du problème
- [ ] `incidents.description` contient la description du problème
- [ ] `incidents.type_probleme` contient le type sélectionné

### Résultat
- [ ] PASS - Données correctement sauvegardées
- [ ] FAIL - Problème de sauvegarde

---

## Test 10: Vérification des réclamations existantes

### Étapes
1. Aller à `/reclamations`
2. Vérifier que les anciennes réclamations s'affichent

### Vérifications
- [ ] Les réclamations existantes sont toujours accessibles
- [ ] Les descriptions existantes s'affichent correctement
- [ ] Pas de cassure d'affichage
- [ ] Les incidents existants s'affichent

### Résultat
- [ ] PASS - Compatibilité rétroactive OK
- [ ] FAIL - Problème avec les anciennes données

---

## Résumé des tests

| Test | Description | Résultat |
|------|-------------|----------|
| 1 | Affichage formulaire | ☐ PASS ☐ FAIL |
| 2 | Ajout incident | ☐ PASS ☐ FAIL |
| 3 | Multiples incidents | ☐ PASS ☐ FAIL |
| 4 | Suppression incident | ☐ PASS ☐ FAIL |
| 5 | Remplissage formulaire | ☐ PASS ☐ FAIL |
| 6 | Génération IA | ☐ PASS ☐ FAIL |
| 7 | Validation formulaire | ☐ PASS ☐ FAIL |
| 8 | Soumission formulaire | ☐ PASS ☐ FAIL |
| 9 | Vérification DB | ☐ PASS ☐ FAIL |
| 10 | Compatibilité anciennes données | ☐ PASS ☐ FAIL |

---

## Notes supplémentaires

### Problèmes rencontrés
(À remplir si problèmes)
- Problème #1: ...
- Problème #2: ...

### Observations
(À remplir avec observations)
- ...

### Recommandations
(À remplir avec recommandations)
- ...

---

## Signature du testeur

- **Nom**: ___________________
- **Date**: 17 octobre 2026
- **Résultat global**: ☐ PASS ☐ FAIL ☐ PARTIEL

---

## Checklist finale

- [ ] Tous les fichiers modifiés
- [ ] Pas d'erreur PHP
- [ ] Routes valides
- [ ] Cache clair
- [ ] Tests manuels effectués
- [ ] Documentation mise à jour
- [ ] Pas de cassure de fonctionnalité existante
- [ ] Données existantes préservées
