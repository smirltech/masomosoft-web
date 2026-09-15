# API endpoints ajoutés

Tous les endpoints utilisent le préfixe `/api/v1` et Sanctum.

## Élèves inscrits

`GET /students?page=1&per_page=20`

Retourne les élèves avec une inscription approuvée pour l'année courante. La réponse contient `data` et `pagination`. `limit` est accepté comme alias de `per_page`.

## Recettes

`GET /revenues?period=day|month|year`

Les recettes sont les perceptions dont `paid_at` est renseigné. Les totaux sont séparés dans `data.USD` et `data.CDF`. Une période explicite est possible avec `startDate=YYYY-MM-DD&endDate=YYYY-MM-DD`. Les éléments détaillés sont dans `revenues`.

## Paiement

`POST /payments`

Le paiement s'appuie sur une perception existante. Utiliser `{ "perception_id": "01..." }`, ou `{ "student_id": "01...", "amount": 100, "currency": "USD" }` pour sélectionner la perception impayée correspondante. Une réussite retourne `201` avec `data.transactionId` et `data.status=success`; une perception déjà payée retourne `409`.
