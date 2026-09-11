# MasomoSoft Manager Mobile — API Integration Quick Guide

## 1. Server Configuration

The application is **multi-customer / self-hosted**. Each school has its own MasomoSoft server.

On the **first launch**, the user enters/selects the server URL.

**Example — Collège ENK**

Server:
https://masomosoft-ufecvqkb.apps.smirltech-sarl.com

API Base URL:
`https://{server}/api/v1`

The server URL must be stored locally so the user does not have to enter it every time.

---

## 2. Authentication

### Login

```http
POST /auth/login
```

The mobile sends the user's credentials to the configured server.

After successful authentication, store:

- `access_token`
- `user`

Use the token on subsequent requests:

```http
Authorization: Bearer {access_token}
Accept: application/json
```

---

## 3. Context

After login:

```http
GET /context
```

This provides the application context required by the Manager App, such as the current school/configuration and academic-year context.

The mobile should use the returned data rather than trying to determine these values locally.

---

## 4. Dashboard

```http
GET /dashboard
```

Expected data:

```text
financial_summary
    USD
        income
        expenses
        balance

    CDF
        income
        expenses
        balance

recent_transactions

academic_year

notifications_count
```

The mobile **only displays the values**.

Do not calculate locally:

- Balance
- Totals
- Currency totals

---

## 5. Receipts

### List

```http
GET /receipts
```

Optional parameters:

- `date_from`
- `date_to`
- `currency`
- `page`
- `per_page`

Example:

```http
GET /receipts?date_from=2026-08-01&date_to=2026-08-31&currency=USD&page=1&per_page=20
```

Response contains:

```text
summary
items
pagination
```

### Summary

```text
USD
CDF
```

These totals correspond to the **currently applied filters**.

### Receipt Item

The mobile receives the already prepared data:

```text
student
class
fee
amount
currency
date
academic_year_id
```

### Pagination

Use:

```text
current_page
per_page
total
last_page
has_more_pages
```

When the user requests more records, request the next page.

---

## 6. Important Implementation Rules

### Backend is responsible for

- Financial calculations
- Totals
- Balances
- Academic-year filtering
- Currency filtering
- Date filtering
- Pagination
- Data relationships
- Business rules

### Flutter is responsible for

- Display
- Formatting amounts
- Formatting dates
- Currency presentation
- Loading/error states
- Pagination UI
- Sending selected filters
- Local caching where useful

**Do not duplicate Laravel financial calculations in Flutter.**

---

## 7. Recommended Flutter API Structure

Keep the mobile implementation simple:

```text
lib/
├── core/
│   └── network/
│       ├── api_client.dart
│       └── api_config.dart
│
├── features/
│   ├── auth/
│   ├── dashboard/
│   ├── finance/
│   │   ├── receipts/
│   │   └── expenses/
│   └── reports/
```

Recommended flow:

```text
ApiClient
    ↓
DashboardService
ReceiptService
ExpenseService
ReportService
    ↓
Screens
```

The Flutter services should **consume the API**, not reproduce backend business logic.

---

## 8. Current API Scope

For now, the mobile developer needs to integrate:

```text
POST /api/v1/auth/login
GET  /api/v1/auth/me
POST /api/v1/auth/logout

GET  /api/v1/context

GET  /api/v1/dashboard

GET  /api/v1/receipts
GET  /api/v1/receipts/{id}
```

**Next backend endpoint:** `Expenses API`.

Once Expenses is implemented, the Flutter developer can connect the existing **ExpensesScreen** using the same pattern as Receipts.
