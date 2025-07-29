# Yii 2 Advanced Project Template

Yii 2 Advanced Project Template is a skeleton Yii 2 application best for
developing complex Web applications with multiple tiers.

The template includes three tiers: front end, back end, and console, each of which
is a separate Yii application.

The template is designed to work in a team development environment. It supports
deploying the application in different environments.

[Documentation is at docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-app-advanced.svg)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-app-advanced.svg)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![build](https://github.com/yiisoft/yii2-app-advanced/workflows/build/badge.svg)](https://github.com/yiisoft/yii2-app-advanced/actions?query=workflow%3Abuild)

## Project Setup

### 1. Clone the repository

```bash
git clone https://github.com/your-username/your-repository.git
cd your-repository
```

### 2. Install dependencies

```bash
composer install
```

### 3. Initialize the application

```bash
php init
```

Choose `dev` when prompted.

### 4. Configure the database

Create a new database and update the `common/config/main-local.php` file with your database credentials:

```php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=your_database_name',
            'username' => 'your_database_username',
            'password' => 'your_database_password',
            'charset' => 'utf8',
        ],
    ],
];
```

### 5. Run migrations

```bash
php yii migrate
```

### 6. Serve the application

#### Frontend

To serve the frontend, run the following command from the project root:

```bash
php -S localhost:8080 -t frontend/web
```

The frontend will be available at `http://localhost:8080`.

#### Backend

To serve the backend, run the following command from the project root:

```bash
php -S localhost:8081 -t backend/web
```

The backend will be available at `http://localhost:8081`.

## Quiz Application

The frontend includes a quiz application built with HTML, Tailwind CSS, and jQuery. It uses a Yii2-powered API to fetch questions, handle user authentication, and store results.

### API Endpoints

- `GET /api/categories`: Get all quiz categories.
- `GET /api/questions`: Get quiz questions.
  - `amount` (integer): Number of questions to fetch.
  - `category` (integer): Category ID.
  - `difficulty` (string): 'easy', 'medium', or 'hard'.
  - `type` (string): 'multiple' or 'boolean'.
- `POST /api/register`: Register a new user.
  - `username` (string): User's username.
  - `password` (string): User's password.
- `POST /api/login`: Log in a user.
  - `username` (string): User's username.
  - `password` (string): User's password.
- `POST /api/results`: Store quiz results.
  - `score` (integer): The user's score.
  - `total` (integer): The total number of questions.
- `GET /api/history`: Get the user's quiz history.
