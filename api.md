# API Documentation — Module Finance

**Base URL (local) :** `http://127.0.0.1:8001/api/v1`
**Base URL (production) :** `https://masomosoft-ufecvqkb.apps.smirltech-sarl.com/api/v1`

## Authentification

Toutes les routes nécessitent un token Bearer (Sanctum) :

```
Authorization: Bearer {token}
Accept: application/json
```

## Conventions générales

| Convention | Détail |
|---|---|
| Devises | `USD`, `CDF` uniquement |
| Dates | Format `YYYY-MM-DD` |
| Période par défaut | Si `date_debut`/`date_fin` non fournis, l'API utilise l'année scolaire en cours (`Annee::encours()`) |
| Montants | `float`, jamais formatés (pas de séparateur de milliers) |
| Pagination | Style Laravel standard (`current_page`, `per_page`, `total`, `last_page`, `from`, `to`) |

---

## 1. Liste des dépenses

```
GET /finance/expenses
```

### Query parameters

| Paramètre | Type | Requis | Description |
|---|---|---|---|
| `date_debut` | date | non | Début de période. Défaut : début de l'année en cours |
| `date_fin` | date | non | Fin de période. Défaut : fin de l'année en cours |
| `type_id` | int | non | Filtre par `depense_type_id` |
| `status` | string | non | Filtre sur le dernier statut (`pending`, `approved_promoteur`, `approved_coordonnateur`, `rejected_promoteur`, `rejected_coordonnateur`, `issued`, `done`) |
| `search` | string | non | Recherche sur `reference`, `motif`, `beneficiaire` |
| `per_page` | int | non | Taille de page. Défaut : 15 |

### Exemple de requête

```bash
curl "http://127.0.0.1:8001/api/v1/finance/expenses?date_debut=2026-01-01&date_fin=2026-06-30&per_page=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

### Réponse `200`

```json
{
  "summary": {
    "USD": 5420,
    "CDF": 12500000
  },
  "data": [
    {
      "id": "01hz3k9m2nabcxyz",
      "reference": "260800",
      "description": "Achat fournitures",
      "category": "fonctionnement",
      "type": "Fournitures",
      "beneficiary": "Librairie Centrale",
      "amount": 320000,
      "currency": "CDF",
      "date": "2026-08-08",
      "status": "approved_promoteur",
      "note": null,
      "validated_at": "2026-08-09 10:15:00",
      "created_by": "Jean Kawel",
      "created_at": "2026-08-08 09:30:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 42,
    "last_page": 3,
    "from": 1,
    "to": 15
  },
  "periode": {
    "date_debut": "2026-01-01",
    "date_fin": "2026-06-30"
  }
}
```

> `summary` est calculé sur **l'ensemble des dépenses filtrées**, pas seulement la page courante.

---

## 2. Détail d'une dépense

```
GET /finance/expenses/{id}
```

`{id}` est un **ULID** (string), pas un entier auto-incrémenté.

### Exemple de requête

```bash
curl "http://127.0.0.1:8001/api/v1/finance/expenses/01hz3k9m2nabcxyz" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

### Réponse `200`

```json
{
  "data": {
    "id": "01hz3k9m2nabcxyz",
    "reference": "260800",
    "description": "Achat fournitures",
    "category": "fonctionnement",
    "type": "Fournitures",
    "beneficiary": "Librairie Centrale",
    "amount": 320000,
    "currency": "CDF",
    "date": "2026-08-08",
    "status": "approved_promoteur",
    "note": null,
    "validated_at": "2026-08-09 10:15:00",
    "created_by": "Jean Kawel",
    "created_at": "2026-08-08 09:30:00"
  }
}
```

### Réponse `404`

Renvoyée si l'`id` n'existe pas (`findOrFail`).

---

## 3. Rapport financier

```
GET /reports/financial
```

Retourne des données **déjà calculées** : le front n'a aucun agrégat, comparaison ou conversion de devise à faire.

### Query parameters

| Paramètre | Type | Requis | Description |
|---|---|---|---|
| `currency` | string | non | `USD` ou `CDF`. Défaut : `USD` |
| `date_debut` | date | non | Début de période. Défaut : début de l'année en cours |
| `date_fin` | date | non | Fin de période. Défaut : fin de l'année en cours |

### Exemple de requête

```bash
curl "http://127.0.0.1:8001/api/v1/reports/financial?currency=USD&date_debut=2026-01-01&date_fin=2026-06-30" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

### Réponse `200`

```json
{
  "currency": "USD",
  "period": {
    "start": "2026-01-01",
    "end": "2026-06-30"
  },
  "summary": {
    "income": 14250,
    "expenses": 5400,
    "balance": 8850
  },
  "chart": [
    { "period": "2026-01", "income": 4000, "expenses": 2000 },
    { "period": "2026-02", "income": 2300, "expenses": 1100 },
    { "period": "2026-03", "income": 1800, "expenses": 900 },
    { "period": "2026-04", "income": 2100, "expenses": 500 },
    { "period": "2026-05", "income": 2050, "expenses": 400 },
    { "period": "2026-06", "income": 2000, "expenses": 500 }
  ],
  "insights": [
    "Solde positif représentant 62% des revenus sur la période.",
    "Le mois avec le plus de dépenses est 2026-01."
  ]
}
```

> `chart` contient **toujours exactement 6 points**, un par mois, se terminant sur le mois de `date_fin` (ou le mois courant si non fourni).

---

## 4. Dashboard Manager

```
GET /dashboard
```

### Query parameters

Aucun.

### Exemple de requête

```bash
curl "http://127.0.0.1:8001/api/v1/dashboard" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

### Réponse `200`

```json
{
  "data": {
    "balances": {
      "USD": 12450,
      "CDF": 34500000
    },
    "recent_transactions": [
      {
        "id": "01hz...",
        "type": "expense",
        "description": "Achat fournitures",
        "amount": 320000,
        "currency": "CDF",
        "date": "2026-08-08"
      },
      {
        "id": "01hz...",
        "type": "income",
        "description": "Frais scolaires",
        "amount": 150,
        "currency": "USD",
        "date": "2026-08-07"
      }
    ],
    "statistics": {
      "USD": { "income": 20000, "expenses": 7550 },
      "CDF": { "income": 45000000, "expenses": 10500000 },
      "academic_year": {
        "id": 1,
        "name": "2025-2026",
        "is_current": true
      }
    },
    "notifications_count": 3
  }
}
```

> `balances` = revenus (frais scolaires + autres revenus) − dépenses validées, pour l'année scolaire en cours.
> `recent_transactions` mélange dépenses validées, revenus et perceptions (frais scolaires), toutes devises confondues, triés par date décroissante, limité à 10.

---

## Codes d'erreur communs

| Code | Cas | Exemple |
|---|---|---|
| `401` | Token absent ou invalide | Middleware `auth:sanctum` |
| `404` | Ressource introuvable | `GET /finance/expenses/{id_inexistant}` |
| `422` | Paramètres de requête invalides | Format de date incorrect |
| `500` | Erreur serveur | Voir `storage/logs/laravel.log` |

---

