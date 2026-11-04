# SolarShare — Module Rapport AI

## 1. Présentation du module

Le module **Rapport AI** de SolarShare est un outil destiné à l'administrateur afin de suivre et d'analyser les performances de la plateforme à travers des rapports détaillés générés à partir des données réelles de la base de données.

Il permet d'obtenir une vision globale de l'activité de location des équipements d'énergie renouvelable, notamment les réservations, les paiements, les revenus, les réclamations et les incidents.

L'objectif principal est de fournir à l'administrateur des indicateurs fiables et actualisés, accompagnés de graphiques, d'une analyse intelligente et d'un export PDF.

**Principe fondamental :** chaque nouvelle génération de rapport doit récupérer les dernières données disponibles dans la base de données. Le rapport ne doit jamais dépendre uniquement des résultats d'une génération précédente.

---

## 2. Sélection de la période

L'administrateur peut choisir l'une des quatre périodes suivantes :

| Période | Description |
|---|---|
| Cette semaine | Analyse des 7 derniers jours |
| Ce mois | Du premier au dernier jour du mois |
| Cette année | Du 1er janvier jusqu'à la date actuelle |
| Personnalisé | Sélection d'une date de début et d'une date de fin |

Tous les indicateurs, tableaux, graphiques et analyses doivent respecter la période sélectionnée.

Les limites temporelles doivent être cohérentes et inclure les données jusqu'à la fin de la date de fin sélectionnée.

---

## 3. Sources des données

Le module doit utiliser directement les données réelles enregistrées dans la base de données de SolarShare.

Les principales sources sont les mêmes que celles utilisées par les interfaces d'administration.

### 3.1. Réservations

Interface d'administration :

`/admin/reservations`

Les données à exploiter comprennent notamment :

- Identifiant de la réservation.
- Client associé.
- Équipement réservé.
- Date de début.
- Date de fin.
- Prix total.
- Statut de la réservation.

Ces informations permettent d'analyser le volume des réservations, leur évolution, leur répartition par statut et la demande pour chaque équipement.

### 3.2. Paiements

Interface d'administration :

`/admin/payments`

Les données à exploiter comprennent notamment :

- Identifiant du paiement.
- Montant.
- Méthode de paiement.
- Statut.
- Date du paiement.
- Transactions associées.
- Réservation liée au paiement, selon les relations existantes.

Les paiements constituent la source principale pour calculer les montants réellement encaissés.

Un paiement en attente ou échoué ne doit pas être considéré comme un revenu encaissé.

Les jointures entre paiements et réservations doivent éviter tout double comptage.

### 3.3. Réclamations et incidents

Interface d'administration :

`/admin/reclamations`

Les données à exploiter comprennent notamment :

- Identifiant de la réclamation.
- Client associé.
- Titre et description.
- Équipement concerné.
- Statut de la réclamation.
- Incidents associés.
- Type de problème.
- Dates disponibles dans les relations existantes.

Une réclamation peut contenir plusieurs incidents. Il faut donc distinguer le nombre total de réclamations du nombre total d'incidents.

Les statuts et les types doivent être récupérés depuis les données et les règles réellement utilisées dans le projet.

---

## 4. Analyse des réservations

Cette section présente l'activité de location pendant la période sélectionnée.

### 4.1. Indicateurs principaux

- Nombre total de réservations.
- Nombre de réservations terminées.
- Nombre de réservations en cours.
- Nombre de réservations confirmées.
- Nombre de réservations en attente.
- Nombre de réservations refusées.
- Nombre de réservations à venir, si cette distinction existe dans la logique métier.

Les statuts doivent être distingués correctement.

Une réservation confirmée ne doit pas automatiquement être considérée comme terminée.

La classification des réservations en cours, terminées et à venir doit respecter les statuts et les dates de réservation réels.

### 4.2. Graphiques

- Évolution des réservations pendant la période.
- Répartition des réservations par statut.
- Nombre de réservations par équipement.
- Classement des équipements les plus demandés.

### 4.3. Analyse IA

L'IA peut notamment expliquer :

- L'évolution de la demande.
- Les équipements les plus réservés.
- Les périodes de forte activité.
- Les changements importants dans les réservations.
- Les éventuelles observations commerciales.

Les conclusions doivent être basées exclusivement sur les indicateurs calculés.

---

## 5. Analyse des paiements et des revenus

Cette section présente la situation financière de la plateforme pendant la période sélectionnée.

### 5.1. Indicateurs principaux

- Nombre total de paiements.
- Nombre de paiements réussis.
- Nombre de paiements en attente.
- Nombre de paiements échoués.
- Montant total réellement encaissé.
- Montant des remboursements réellement exécutés, si disponible.
- Montant net encaissé selon les règles comptables du projet.

Les montants doivent être calculés à partir des paiements réels et de leurs statuts.

Une réclamation approuvée ne signifie pas automatiquement qu'un remboursement a été exécuté.

### 5.2. Revenus par équipement

Le système doit relier correctement :

`Équipement → Réservation → Paiement`

Pour chaque équipement, afficher :

- Nom de l'équipement.
- Identifiant de l'équipement.
- Nombre de réservations.
- Nombre de paiements réussis associés.
- Revenu réellement encaissé.
- Prix journalier, si disponible.

Les équipements ayant le même nom mais des identifiants différents doivent rester distincts.

### 5.3. Graphiques

- Évolution des revenus pendant la période.
- Répartition des paiements par statut.
- Revenus générés par équipement.
- Classement des équipements selon leurs revenus.

---

## 6. Analyse des incidents et réclamations

Cette section permet à l'administrateur de suivre les problèmes signalés par les clients.

### 6.1. Indicateurs principaux

- Nombre total de réclamations.
- Nombre total d'incidents associés.
- Réclamations en attente.
- Réclamations approuvées.
- Réclamations refusées.
- Types d'incidents.
- Équipements les plus concernés par les incidents.

Les nombres de réclamations et d'incidents doivent être calculés séparément.

Si le statut est enregistré au niveau de la réclamation, les incidents associés ne doivent pas être artificiellement comptés comme des décisions indépendantes.

### 6.2. Analyse par équipement

Pour chaque équipement, afficher :

- Nom et identifiant.
- Nombre de réclamations associées.
- Nombre d'incidents associés.
- Types de problèmes rencontrés.
- Répartition des statuts, lorsque les données le permettent.

### 6.3. Graphiques

- Répartition des réclamations par statut.
- Répartition des incidents par type.
- Nombre d'incidents par équipement.
- Équipements les plus concernés par les problèmes.

### 6.4. Analyse IA

L'IA peut identifier :

- Les équipements qui présentent le plus de problèmes.
- Les types d'incidents les plus fréquents.
- Les tendances observées pendant la période.
- Les équipements nécessitant une attention particulière.

Les recommandations doivent être justifiées par les données disponibles.

---

## 7. Analyse du profit

Le module doit permettre d'afficher :

- Le profit total de la plateforme.
- Le profit généré par chaque équipement.
- Le classement des équipements selon leur rentabilité.

Avant de calculer le profit, le système doit vérifier les données de coûts réellement disponibles dans la base de données.

Si les coûts d'achat, d'entretien, de réparation ou les autres dépenses nécessaires existent, ils peuvent être utilisés selon les règles métier du projet.

Exemple de principe :

`Profit = Revenus encaissés - Coûts et dépenses applicables`

Le calcul doit utiliser uniquement les coûts réellement enregistrés et justifiables.

Si les informations nécessaires ne sont pas disponibles, le système doit afficher les revenus par équipement et préciser que le profit exact ne peut pas être calculé.

Il est interdit de présenter le revenu comme un profit.

Les incidents ne doivent pas être transformés automatiquement en pertes financières sans montant réel associé.

---

## 8. Analyse intelligente avec l'IA

L'IA intervient après le calcul des statistiques.

Le backend Laravel doit effectuer les requêtes et les calculs à partir de la base de données.

L'IA reçoit ensuite les résultats structurés afin de produire :

- Un résumé général de la période.
- Une analyse des réservations.
- Une analyse des paiements et des revenus.
- Une analyse des incidents et réclamations.
- Une analyse des équipements les plus demandés.
- Une analyse des équipements générant le plus de revenus.
- Une analyse du profit lorsque les données le permettent.
- Des observations et recommandations commerciales.

L'IA ne doit jamais inventer des chiffres, modifier les résultats SQL ou déduire des informations financières absentes.

Les valeurs numériques présentées dans le rapport doivent provenir des calculs du backend.

---

## 9. Actualisation des données

L'actualisation des données est une exigence essentielle.

Chaque clic sur « Générer un rapport » doit déclencher une nouvelle récupération des données depuis la base de données.

Exemple :

1. L'administrateur génère un rapport le premier jour.
2. De nouvelles réservations sont ajoutées.
3. De nouveaux paiements sont enregistrés.
4. De nouvelles réclamations et de nouveaux incidents sont créés.
5. Trois jours plus tard, l'administrateur génère un nouveau rapport pour la même période.

Le nouveau rapport doit inclure toutes les nouvelles données correspondant à la période sélectionnée.

Le système ne doit pas réutiliser les anciens résultats statistiques comme source de vérité.

Les éventuels anciens rapports peuvent être conservés comme historique, mais une nouvelle génération doit toujours recalculer les indicateurs.

---

## 10. Export PDF

L'administrateur doit pouvoir télécharger le rapport au format PDF.

Le document doit contenir :

1. Titre du rapport et période analysée.
2. Résumé exécutif.
3. Indicateurs principaux.
4. Analyse détaillée des réservations.
5. Analyse des paiements et des revenus.
6. Analyse des incidents et réclamations.
7. Analyse des équipements.
8. Analyse du profit, si calculable.
9. Graphiques et tableaux.
10. Analyse IA et recommandations.

Le PDF doit utiliser exactement les mêmes résultats que le rapport affiché à l'écran.

Les valeurs, filtres, dates et graphiques doivent rester cohérents entre les deux formats.

---

## 11. Exigences techniques et règles de qualité

- Utiliser les tables et relations réelles du projet Laravel.
- Réutiliser les modèles et relations existants lorsque cela est pertinent.
- Ne pas extraire les données en analysant le HTML des pages Admin.
- Respecter la période sélectionnée pour chaque indicateur.
- Utiliser la date métier appropriée pour les réservations, paiements et incidents.
- Éviter les doublons dans les jointures SQL.
- Conserver les identifiants des équipements distincts.
- Distinguer revenu encaissé, valeur des réservations et profit.
- Distinguer réclamations et incidents.
- Gérer les valeurs nulles et les périodes sans données.
- Afficher des résultats explicites lorsqu'une métrique n'est pas calculable.
- Ne jamais inventer de données ou de coûts.
- Garantir la cohérence entre HTML, PDF et les éventuels exports CSV.

### Tests attendus

Les tests doivent notamment vérifier :

- Le filtrage par période.
- L'ajout de nouvelles réservations entre deux générations.
- L'ajout de nouveaux paiements entre deux générations.
- L'ajout de nouvelles réclamations et de nouveaux incidents.
- Le calcul des réservations par statut.
- Le calcul des paiements réellement encaissés.
- Les revenus par équipement.
- L'absence de double comptage.
- La distinction entre réclamations et incidents.
- Le calcul du profit uniquement lorsque les coûts sont disponibles.
- La cohérence des résultats entre le rapport HTML et le PDF.

---

## 12. Résultat attendu

Le module Rapport AI doit devenir un outil d'analyse et de pilotage pour l'administrateur de SolarShare.

Il doit permettre de comprendre rapidement :

- Combien de réservations ont été effectuées pendant une période.
- Combien sont terminées, en cours, en attente ou refusées.
- Combien d'argent a réellement été encaissé.
- Quels équipements sont les plus demandés.
- Quels équipements génèrent le plus de revenus.
- Combien d'incidents ont été signalés et combien de réclamations ont été approuvées ou refusées.
- Quels équipements sont les plus concernés par les incidents.
- Quel est le profit total et le profit par équipement lorsque les données le permettent.

**La fiabilité des données, leur actualisation à chaque génération et la cohérence des calculs constituent les priorités absolues du module.**
