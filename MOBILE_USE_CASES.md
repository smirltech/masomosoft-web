# Cas d'Usage pour l'Application Mobile (Saint-François-Xavier / MasomoSoft)

Ce document spécifie l'ensemble des cas d'usage fonctionnels et techniques pour l'application mobile de gestion d'établissement scolaire.

---

## 1. Acteurs du Système Mobile

- **Directeur / Manager (Admin)** : Accède aux tableaux de bord financiers, aux statistiques d'inscriptions et aux récapitulatifs globaux.
- **Agent Financier / Caissier** : Consulte les reçus, suit les perceptions des frais scolaires et vérifie les encaissements.
- **Parent / Tuteur (Optionnel / Futur)** : Consulte l'état financier des frais de ses enfants et les reçus de paiement.

---

## 2. Matrice des Cas d'Usage

| Code UC | Titre du Cas d'Usage | Acteur Principal | Fréquence | Priorité |
| :--- | :--- | :--- | :--- | :--- |
| **UC-01** | Authentification de l'utilisateur (Login) | Tous les utilisateurs | Quotidienne | Haute |
| **UC-02** | Déconnexion sécurisée (Logout) | Tous les utilisateurs | Occasionnelle | Haute |
| **UC-03** | Chargement du contexte de l'établissement | Application Mobile | Au lancement | Haute |
| **UC-04** | Consultation du tableau de bord financier | Manager / Admin | Quotidienne | Haute |
| **UC-05** | Consultation et filtrage des reçus de paiement | Manager / Caissier | Fréquente | Haute |
| **UC-06** | Gestion de session expirée / Mode Hors-ligne | Système Mobile | Automatique | Moyenne |

---

## 3. Description Détaillée des Cas d'Usage

### UC-01 : Authentification de l'utilisateur (Login)

- **Objectif** : Permettre à un utilisateur de se connecter avec son adresse email et mot de passe pour obtenir un jeton d'accès sécurisé (Sanctum).
- **Préconditions** : L'utilisateur dispose d'un compte actif.
- **Scénario Nominal** :
  1. L'utilisateur ouvre l'application mobile et accède à l'écran de connexion.
  2. L'utilisateur saisit son adresse email et son mot de passe.
  3. L'application transmet la requête `POST /api/v1/auth/login` avec le paramètre `device_name`.
  4. Le serveur valide les informations et renvoie un statut `200 OK` avec le token d'accès Bearer et les informations du profil.
  5. L'application enregistre le token dans le stockage sécurisé (Keychain / Keystore).
  6. L'application redirige l'utilisateur vers le Tableau de Bord (Dashboard).
- **Scénario Alternatif (Erreur de validation / Identifiants incorrects)** :
  - En cas d'identifiants erronés, le serveur retourne une erreur `422 Unprocessable Entity`.
  - L'application affiche un message d'erreur clair : *"Les identifiants sont incorrects"*.

---

### UC-02 : Déconnexion sécurisée (Logout)

- **Objectif** : Terminer la session de l'utilisateur et révoquer le token côté serveur.
- **Préconditions** : L'utilisateur est connecté.
- **Scénario Nominal** :
  1. L'utilisateur clique sur "Se déconnecter" depuis son profil ou les paramètres.
  2. L'application envoie une requête `POST /api/v1/auth/logout` avec l'en-tête `Authorization: Bearer <token>`.
  3. Le serveur supprime le jeton d'accès courant et retourne `200 OK`.
  4. L'application supprime le token et les données en cache local.
  5. L'application redirige l'utilisateur vers l'écran de Login.

---

### UC-03 : Chargement du Contexte de l'Établissement

- **Objectif** : Récupérer la configuration de l'école (nom, logo, devises supportées, année scolaire en cours) dès le démarrage ou après connexion.
- **Préconditions** : L'utilisateur est authentifié.
- **Scénario Nominal** :
  1. L'application envoie une requête `GET /api/v1/context`.
  2. Le serveur renvoie les données de l'école, l'année scolaire active et la liste des devises (`USD`, `CDF`).
  3. L'application met à jour l'en-tête de l'interface (nom de l'école, logo, badge de l'année scolaire) et configure les sélecteurs de devise.
- **Scénario d'Erreur Réseau** :
  - Si le réseau est indisponible, l'application utilise la dernière configuration enregistrée localement en cache.

---

### UC-04 : Consultation du Tableau de Bord Financier

- **Objectif** : Visualiser les indicateurs clés (revenus, dépenses, soldes par devise) et les 10 dernières transactions.
- **Préconditions** : L'utilisateur possède le rôle Manager ou Administrateur.
- **Scénario Nominal** :
  1. L'utilisateur accède à l'onglet "Dashboard".
  2. L'application envoie une requête `GET /api/v1/dashboard`.
  3. Le serveur calcule et retourne :
     - La synthèse financière en `USD` (Revenus, Dépenses, Solde net).
     - La synthèse financière en `CDF` (Revenus, Dépenses, Solde net).
     - La liste unifiée et triée chronologiquement des transactions récentes (perceptions, dépenses, revenus auxiliaires).
     - Le statut de l'année scolaire en cours.
  4. L'application affiche les cartes récapitulatives et la liste des transactions avec indicateur visuel (Vert pour Revenu/Perception, Rouge pour Dépense).
- **Actions Utilisateur** :
  - Tirer pour rafraîchir (*Pull-to-refresh*).
  - Basculer entre les devises (`USD` / `CDF`).

---

### UC-05 : Consultation et Suivi des Reçus Financiers

- **Objectif** : Consulter l'historique paginé des perceptions de frais scolaires avec les montants payés et dus.
- **Préconditions** : L'utilisateur est connecté.
- **Scénario Nominal** :
  1. L'utilisateur se rend dans la section "Finances" > "Reçus".
  2. L'application envoie une requête `GET /api/v1/finance/receipts?page=1`.
  3. Le serveur renvoie le résumé global par devise (`summary`), la liste paginée des reçus et les métadonnées de pagination.
  4. L'application affiche la liste avec la référence du reçu, la date, le payeur, le montant payé et le solde dû.
  5. L'utilisateur peut faire défiler la liste (chargement infini / pagination).
- **Cas Particuliers** :
  - Si un reçu a une date d'échéance dépassée avec un reliquat, l'application peut afficher un badge "En attente / Partiel".

---

### UC-06 : Gestion des Erreurs et de la Session Expirée

- **Objectif** : Offrir une expérience fluide et sécurisée en cas de perte de session ou de problème réseau.
- **Règles Fonctionnelles** :
  - **Erreur 401 (Non autorisé)** : Lorsqu'un appel API renvoie une 401, l'application purge automatiquement le token et affiche un message : *"Votre session a expiré. Veuillez vous reconnecter."*
  - **Absence de connexion Internet** : L'application affiche une bannière informative *"Mode hors-ligne - Données en cache"* sans bloquer la navigation sur les écrans déjà consultés.
  - **Retry automatique** : Sur les requêtes de lecture (GET), proposer un bouton "Réessayer" en cas d'échec réseau.
