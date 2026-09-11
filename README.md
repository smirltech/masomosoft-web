# MasomoSoft

> School Management System

MasomoSoft is a school management platform designed to support the academic, administrative, financial, and operational management of schools.

The platform is built as a **self-hosted application**. Each school operates its own MasomoSoft instance and database, while the same application codebase can be deployed independently for multiple schools.

## Project Overview

MasomoSoft provides a web-based administration platform for managing school operations and exposes a versioned REST API for mobile applications.

### Main Components

- **MasomoSoft Web Application** — administration and school management platform
- **Manager Mobile App** — mobile application for school managers and administrators
- **POS Mobile App** — point-of-sale application for operational/payment activities
- **REST API** — backend interface consumed by the mobile applications
- **MySQL Database** — school-specific application data

### Multi-Customer / Self-Hosted Architecture

Each school has its own MasomoSoft deployment.

```text
School A
    ↓
MasomoSoft instance
    ↓
School A database

School B
    ↓
MasomoSoft instance
    ↓
School B database
```

The mobile applications do not use one global MasomoSoft server.

Instead, the user configures the **school's server URL** when opening the mobile application for the first time.

Example:

```text
https://masomosoft-ufecvqkb.apps.smirltech-sarl.com
```

The mobile application then uses:

```text
/api/v1
```

as the API base path.

## Manager Mobile API

The Manager Mobile App communicates with MasomoSoft through the versioned REST API.

For the current Manager Mobile API scope, architecture, authentication, endpoints, filtering, pagination, response structure, and mobile integration guidelines, see:

**[MasomoSoft Manager Mobile API Integration Quick Guide](API_Integration_Quick_Guide.md)**

## Technology Stack

The project currently runs on:

- **Laravel 9**
- **PHP 8.1**
- **MySQL**
- **Node.js**
- **Composer**

## Data Modeling

[Data Modeling](https://drawsql.app/teams/smirltech/diagrams/college-enk)

## Getting Started

Assuming you've already installed on your machine:

- [PHP 8.1](https://www.php.net/releases/8.1/en.php) (>= 8.1.0)
- [Composer](https://getcomposer.org)
- [MySQL](https://www.mysql.com)
- [Node.js](https://nodejs.org)

and that you are familiar with [Laravel](https://laravel.com).

### Install Dependencies

```bash
composer install
npm install
```

### Configure the Application

Create the `.env` file and generate the application key:

```bash
cp .env.example .env
php artisan key:generate
```

Configure the database connection in `.env` before running the migrations.

### Build Assets

For development:

```bash
npm run dev
```

Or, for production/minified assets:

```bash
npm run prod
```

### Database

Run migrations:

```bash
php artisan migrate
```

Optionally seed the database:

```bash
php artisan db:seed
```

### Start the Application

```bash
php artisan serve
```

The application will be available at:

```text
http://localhost:8000
```

## Packages

- Laravel Sanctum
- Orion
- Laravel Permission

## Functionalities

To see the list of available modules and functionalities:

[FUNCTIONALITIES.md](FUNCTIONALITIES.md)

## API Development

The API follows a versioned structure:

```text
/api/v1
```

API endpoints should be designed primarily around the requirements of the consuming application rather than exposing database models directly.

Business logic and financial calculations should remain on the backend, while mobile applications should primarily handle:

- Data presentation
- User interaction
- Formatting
- Local caching where applicable
- Sending filters and pagination parameters

For the current Manager Mobile API implementation guide, see:

**[MasomoSoft Manager Mobile API Integration Quick Guide](API_Integration_Quick_Guide.md)**
