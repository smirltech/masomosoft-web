# Documentation Technique de l'API Mobile (Saint-François-Xavier / MasomoSoft)

Cette documentation technique décrit l'ensemble des points d'accès (endpoints) de l'API REST v1 conçue pour l'intégration avec l'application mobile.

---

## 1. Informations Générales

- **Base URL** : `http://<domain_or_ip>/api/v1`
- **Format d'échange** : `JSON`
- **En-têtes recommandés pour chaque requête** :
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
- **Gestion de l'authentification** : Laravel Sanctum via jeton d'authentification Bearer.
  ```http
  Authorization: Bearer <votre_token_sanctum>
  ```

---

## 2. Codes de Statut HTTP

| Code | Signification | Description |
| :--- | :--- | :--- |
| `200 OK` | Succès | Requête traitée avec succès. |
| `201 Created` | Ressource créée | Création réussie d'une nouvelle ressource. |
| `401 Unauthorized` | Non authentifié | Token Sanctum absent, expiré ou invalide. |
| `403 Forbidden` | Accès refusé | Droits insuffisants pour effectuer l'action. |
| `404 Not Found` | Non trouvé | La ressource demandée n'existe pas. |
| `422 Unprocessable Entity` | Erreur de validation | Données de requête invalides (format, champs manquants). |
| `500 Server Error` | Erreur serveur | Problème interne côté serveur. |

---

## 3. Endpoints d'Authentification

### 3.1 Connexion Utilisateur (Login)
Authentifie l'utilisateur (Manager/Administrateur) et génère un jeton Sanctum pour l'application mobile.

- **Méthode** : `POST`
- **URL** : `/api/v1/auth/login`
- **Authentification** : Aucune (Public)

#### Paramètres du corps (Body JSON) :
| Champ | Type | Requis | Description |
| :--- | :--- | :--- | :--- |
| `email` | `string` | Oui | Adresse email du compte utilisateur. |
| `password` | `string` | Oui | Mot de passe de l'utilisateur. |
| `device_name` | `string` | Non | Nom de l'appareil mobile (ex: `iPhone 15 Pro`, `Samsung S23`). |

#### Exemple de Requête :
```json
{
  "email": "manager@saintfx.cd",
  "password": "motdepasseSecurise123",
  "device_name": "Mobile Manager App"
}
```

#### Exemple de Réponse (200 OK) :
```json
{
  "data": {
    "token": "1|abcdef1234567890sanctumTokenExample...",
    "user": {
      "id": 1,
      "name": "Jean Dupont",
      "email": "manager@saintfx.cd",
      "role": "admin"
    }
  }
}
```

#### Exemple d'Erreur (422 Unprocessable Entity) :
```json
{
  "message": "Les identifiants sont incorrects.",
  "errors": {
    "email": [
      "Les identifiants sont incorrects."
    ]
  }
}
```

---

### 3.2 Déconnexion (Logout)
Invalide et supprime le jeton d'accès actuel de l'appareil mobile.

- **Méthode** : `POST`
- **URL** : `/api/v1/auth/logout`
- **Authentification** : `auth:sanctum` (Requise)

#### Exemple de Réponse (200 OK) :
```json
{
  "message": "Successfully logged out."
}
```

---

### 3.3 Profil Utilisateur Connecté (Me)
Récupère les informations du compte utilisateur actuellement authentifié.

- **Méthode** : `GET`
- **URL** : `/api/v1/auth/me`
- **Authentification** : `auth:sanctum` (Requise)

#### Exemple de Réponse (200 OK) :
```json
{
  "data": {
    "id": 1,
    "name": "Jean Dupont",
    "email": "manager@saintfx.cd",
    "role": "admin"
  }
}
```

---

## 4. Endpoints Système & Contexte

### 4.1 Récupération du Contexte Global
Fournit la configuration générale de l'établissement scolaire, l'année scolaire en cours et les devises gérées.

- **Méthode** : `GET`
- **URL** : `/api/v1/context`
- **Authentification** : `auth:sanctum` (Requise)

#### Exemple de Réponse (200 OK) :
```json
{
  "data": {
    "school": {
      "id": 1,
      "name": "Complexe Scolaire Saint-François-Xavier",
      "code": "SFX-01",
      "logo": "https://domain.cd/storage/logos/school.png",
      "address": "Kinshasa / Lubumbashi",
      "phone": "+243 000 000 000",
      "email": "info@saintfx.cd",
      "website": "https://saintfx.cd",
      "settings": {}
    },
    "academic_year": {
      "id": 1,
      "name": "2024-2025",
      "statut": true
    },
    "currencies": [
      "USD",
      "CDF"
    ]
  }
}
```

---

## 5. Endpoints Tableau de Bord & Finances

### 5.1 Synthèse du Dashboard Manager
Fournit les indicateurs clés de performance financière et les transactions récentes pour l'année en cours.

- **Méthode** : `GET`
- **URL** : `/api/v1/dashboard`
- **Authentification** : `auth:sanctum` (Requise)

#### Exemple de Réponse (200 OK) :
```json
{
  "data": {
    "financial_summary": {
      "USD": {
        "income": 12500.0,
        "expenses": 4200.0,
        "balance": 8300.0
      },
      "CDF": {
        "income": 25000000.0,
        "expenses": 14000000.0,
        "balance": 11000000.0
      }
    },
    "recent_transactions": [
      {
        "id": 105,
        "type": "income",
        "description": "Frais scolaires",
        "amount": 150.0,
        "currency": "USD",
        "date": "2026-09-01T10:15:00.000000Z"
      },
      {
        "id": 42,
        "type": "expense",
        "description": "Achat fournitures de bureau",
        "amount": 85.0,
        "currency": "USD",
        "date": "2026-08-30"
      }
    ],
    "academic_year": {
      "id": 1,
      "name": "2024-2025",
      "is_current": true
    },
    "notifications_count": 0
  }
}
```

---

### 5.2 Liste des Reçus / Paiements (Finance Receipts)
Permet de lister l'ensemble des perceptions et paiements effectués avec totaux et pagination.

- **Méthode** : `GET`
- **URL** : `/api/v1/finance/receipts`
- **Authentification** : Public / Optionnellement protégée selon configuration
- **Paramètres Query** :
  - `page` *(int, optionnel)* : Numéro de la page (ex: `?page=2`).

#### Exemple de Réponse (200 OK) :
```json
{
  "summary": {
    "USD": 12500.0,
    "CDF": 25000000.0
  },
  "data": [
    {
      "id": 12,
      "reference": "REC-20260901-0012",
      "amount_due": 150.0,
      "amount_paid": 150.0,
      "currency": "USD",
      "date": "2026-09-01T08:30:00+00:00",
      "due_date": "2026-09-15",
      "paid_by": "M. Kabasele",
      "custom_property": null
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 45,
    "last_page": 3
  }
}
```

---

## 6. Bonnes Pratiques d'Intégration Mobile

1. **Stockage Sécurisé du Token** :
   - Stocker le token Sanctum dans le trousseau sécurisé de l'OS (**Keychain** sur iOS / **EncryptedSharedPreferences / Keystore** sur Android).
2. **Gestion de l'expiration / Erreurs 401** :
   - Mettre en place un intercepteur HTTP (ex: Axios / Dio / Retrofit) pour intercepter le statut `401 Unauthorized`, purger les données locales et rediriger automatiquement l'utilisateur vers l'écran de Login.
3. **Mise en cache locale & Mode Hors-ligne** :
   - Mettre en cache le `/context` et les listes consultées pour permettre un affichage rapide (offline-first ou cache-then-network).
