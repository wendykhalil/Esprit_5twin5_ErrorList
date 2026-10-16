# Guide de Test - Codes de Compensation

## Résumé Exécutif

Le système de compensation des codes a été amélioré avec :
- **Validation AJAX en temps réel** sans rechargement de page
- **Calcul instantané des réductions** (pourcentage ou montant fixe)
- **Interface responsive** adaptée mobile et desktop
- **Prévention double-clic** pendant validation
- **Messages d'erreur explicites** en français

## Données de Test Créées

Commande : `php artisan test:create-compensation-codes`

| Code | Type | Valeur | Max Uses | État | Cas d'Usage |
|------|------|--------|----------|------|-------------|
| CC-SQRYFNKT1BVN | Pourcentage | 50% | 1 | ✅ Valide | Cas 1 : 50% réduction |
| CC-HPEMLN83DKX9 | Pourcentage | 25% | 3 | ✅ Valide | Multi-usage |
| CC-0FGADIIZVQ2T | Montant fixe | 50 TND | 1 | ✅ Valide | Cas 2 : Montant fixe |
| CC-PN440709BHW0 | Montant fixe | 100 TND | 2 | ✅ Valide | Réduction plus haute |
| CC-4AYWBA2SW7I2 | Pourcentage | 50% | 1 | ⏰ Expiré | Cas 4 : Code expiré |
| CC-M0HMHY41TTG3 | Pourcentage | 30% | 1 | 🔮 Futur | Cas 5 : Pas encore valide |

## Scénarios de Test

### Cas 1 : Code valide à 50% de réduction

**Étapes :**
1. Aller à `/reservations/create` ou page détail équipement
2. Sélectionner équipement (100 TND/jour)
3. Date début : aujourd'hui, Date fin : demain (1 jour)
4. Entrer code : `CC-SQRYFNKT1BVN`
5. Cliquer « Valider »

**Résultats attendus :**
- ✅ Message : "✓ Code de compensation valide ! 50% de réduction (Valide jusqu'au 15/11/2026)"
- ✅ Affichage du prix initial : 100 TND
- ✅ Affichage de la réduction : 50 TND
- ✅ Prix final : 50 TND (100 - 50)
- ✅ Bouton « Retirer le code » apparaît

**Validation serveur :**
- Soumettre la réservation
- Vérifier en base : `SELECT * FROM reservations WHERE compensation_code_id IS NOT NULL LIMIT 1;`
- Colonnes : `base_price=100`, `discount_amount=50`, `prix_total=50`

---

### Cas 2 : Code valide à montant fixe (50 TND)

**Étapes :**
1. Aller à `/reservations/create`
2. Sélectionner équipement (150 TND/jour)
3. Date début : aujourd'hui, Date fin : dans 3 jours (3 jours = 450 TND)
4. Entrer code : `CC-0FGADIIZVQ2T`
5. Cliquer « Valider »

**Résultats attendus :**
- ✅ Message : "✓ Code de compensation valide ! 50.00 TND de réduction (Valide jusqu'au 15/11/2026)"
- ✅ Prix initial : 450 TND
- ✅ Réduction : 50 TND
- ✅ Prix final : 400 TND (450 - 50)

**Cas limite :** Montant fixe supérieur au prix
- Sélectionner équipement 30 TND/jour, 1 jour = 30 TND
- Appliquer code 50 TND fixe
- ✅ Réduction plafonnée à 30 TND (ne dépasse pas la base)
- ✅ Prix final : 0 TND (30 - 30)

---

### Cas 3 : Code inexistant

**Étapes :**
1. Aller à `/reservations/create`
2. Entrer code invalide : `INVALID-CODE`
3. Cliquer « Valider »

**Résultats attendus :**
- ✅ Message d'erreur (rouge) : "✗ Le code de compensation n'existe pas."
- ✅ Prix reste inchangé
- ✅ Pas d'application de réduction

**Requête API :**
```bash
curl -X POST http://localhost:8000/codes-compensation/validate \
  -H "X-CSRF-TOKEN: <token>" \
  -H "Content-Type: application/json" \
  -d '{"code":"INVALID"}'
# Résponse : 404 { "valid": false, "message": "Le code de compensation n'existe pas." }
```

---

### Cas 4 : Code expiré

**Étapes :**
1. Aller à `/reservations/create`
2. Entrer code expiré : `CC-4AYWBA2SW7I2`
3. Cliquer « Valider »

**Résultats attendus :**
- ✅ Message d'erreur (rouge) : "✗ Ce code de compensation a expiré (le 15/10/2026)."
- ✅ Pas d'application de réduction
- ✅ Prix reste initial

**Réponse API :** HTTP 410 Gone

---

### Cas 5 : Code pas encore valide

**Étapes :**
1. Aller à `/reservations/create`
2. Entrer code futur : `CC-M0HMHY41TTG3`
3. Cliquer « Valider »

**Résultats attendus :**
- ✅ Message d'erreur (rouge) : "✗ Ce code de compensation n'est pas encore valide (débute le 21/10/2026)."
- ✅ Pas d'application de réduction

**Réponse API :** HTTP 400 Bad Request

---

### Cas 6 : Code retiré (état initial)

**Étapes :**
1. Aller à `/reservations/create`
2. Appliquer code valide `CC-SQRYFNKT1BVN`
3. ✅ Message de succès + réduction affichée
4. Cliquer sur bouton "✕ Retirer le code"

**Résultats attendus :**
- ✅ Champ code vidé
- ✅ Message disparaît
- ✅ Réduction disparaît
- ✅ Prix revient au prix initial
- ✅ Bouton « Retirer le code » disparaît

---

### Cas 7 : Double-clic sur « Valider »

**Étapes :**
1. Aller à `/reservations/create`
2. Entrer code valide `CC-SQRYFNKT1BVN`
3. Cliquer rapidement 2 fois sur « Valider »

**Résultats attendus :**
- ✅ Bouton devient désactivé pendant validation
- ✅ Texte change à "Validation en cours..."
- ✅ Seule UNE requête AJAX envoyée (pas de double envoi)
- ✅ Message d'erreur ou de succès affiché une fois
- ✅ Bouton réactivé après réponse

**Vérification DevTools (Network) :**
```
1 seule requête POST /codes-compensation/validate
```

---

## Calculs Attendus - Tableau Récapitulatif

| Scénario | Prix/Jour | Jours | Base | Code | Réduction | Final | Notes |
|----------|-----------|-------|------|------|-----------|-------|-------|
| 1 jour, 50% | 100 TND | 1 | 100 | 50% | 50 | 50 | Cas d'usage principal |
| 3 jours, 50 TND fixe | 150 TND | 3 | 450 | 50 TND | 50 | 400 | Montant fixe |
| 1 jour, 50 TND > prix | 30 TND | 1 | 30 | 50 TND | 30 | 0 | Discount capped |
| 5 jours, 25% | 200 TND | 5 | 1000 | 25% | 250 | 750 | Pourcentage plus bas |
| 10 jours, 100 TND | 100 TND | 10 | 1000 | 100 TND | 100 | 900 | Fixe petit montant |

---

## Tests Unitaires - Résultats

Fichier : `tests/Unit/CompensationCodeCalculationTest.php`

```
✓ percentage discount calculation (50% de 100 = 50)
✓ fixed discount calculation (50 TND fixe)
✓ discount cap at base price (jamais > base)
✓ has uses remaining (compteur uses_count)
✓ zero discount (0% = 0 TND)
✓ percentage rounding (précision 2 décimales)
✓ realistic scenario 50 percent (100 → 50 → 50)
✓ realistic scenario 50 fixed (100 → 50 → 50)
✓ multiple durations with discount (scaling correct)

Tests: 9 passed (25 assertions)
Duration: 0.61s
```

Lancer les tests : `php artisan test tests/Unit/CompensationCodeCalculationTest.php`

---

## Architecture Validée

### Endpoint API
- **URL :** `POST /codes-compensation/validate`
- **Auth :** Middleware `auth` requis
- **Request :** `{ "code": "CC-XXXXX" }`
- **Response (200 OK) :**
  ```json
  {
    "valid": true,
    "message": "Code de compensation valide !",
    "discount_type": "percentage|fixed",
    "discount_value": 50.0,
    "max_uses": 1,
    "uses_remaining": 0,
    "valid_until": "15/11/2026"
  }
  ```
- **Response (4xx/5xx) :**
  - 400 : Code vide ou non encore valide
  - 403 : N'appartient pas à l'utilisateur
  - 404 : Code inexistant
  - 410 : Désactivé, expiré, ou épuisé

### Modèle CompensationCode
- **Méthode :** `calculateDiscount($basePrice)` → montant de réduction
- **Logique :** Pourcentage ou montant fixe, plafonné à la base
- **Validation :** `isValid()`, `hasUsesRemaining()`, `isExpired()`, `hasStarted()`

### Contrôleur Réservation
- **Persiste le code :** Via `ApplyReservationCompensationService`
- **Atomicité :** Transaction DB avec lock pessimiste
- **Incrémente uses_count :** Après application réussie
- **Calcul serveur :** Jamais pas confiance au montant client

### JavaScript Client
- **Prévention double-submit :** Flag `isValidating`
- **Calcul côté client :** Affichage en temps réel
- **Validation serveur :** Au moment de la soumission réservation
- **Messages :** En français, color-coded (vert = succès, rouge = erreur)

---

## Checklist de Déploiement

- [x] Commande Artisan créée : `test:create-compensation-codes`
- [x] Codes de test générés dans DB (6 codes)
- [x] UX formulaire améliorée (responsive, double-clic prevention)
- [x] Tests unitaires passent (9/9)
- [x] Endpoint API fonctionne (validation + réponses correctes)
- [x] Service transactionnel opérationnel (lock + increment)
- [x] Messages français affichés correctement
- [x] Calculs de réduction valides (pourcentage, montant fixe, cap)
- [ ] Tests en production (voir section Cas d'Usage ci-dessus)

---

## Prochaines Étapes

1. **Test manuel** : Suivre les 7 cas d'usage ci-dessus
2. **Test navigateur** : Vérifier DevTools Network, Console, Local Storage
3. **Test multi-équipements** : Essayer avec différents prix/jour
4. **Test multi-durées** : 1 jour, 5 jours, 10 jours
5. **Test edge cases** : Montant fixe > prix, réduction 100%, etc.
6. **Performance** : Valider avec 100+ codes en DB
7. **Sécurité** : Vérifier ownership du code, ownership de la réservation

---

## Débogage

### Voir les requêtes AJAX
1. Ouvrir DevTools (F12)
2. Onglet « Network »
3. Filtrer par « compensation-codes »
4. Vérifier request/response JSON

### Voir les données en DB
```sql
SELECT * FROM compensation_codes ORDER BY created_at DESC LIMIT 10;
SELECT * FROM reservations WHERE compensation_code_id IS NOT NULL;
SELECT base_price, discount_amount, prix_total FROM reservations WHERE compensation_code_id IS NOT NULL;
```

### Logs applicatifs
```bash
tail -f storage/logs/laravel.log
```

### Tester l'endpoint directement
```bash
php artisan tinker
>>> $code = \App\Models\CompensationCode::first();
>>> $code->calculateDiscount(100);
=> 50.0
>>> $code->isValid();
=> true
```
