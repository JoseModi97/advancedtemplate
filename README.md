# Trivia Quiz Application

This is a web-based trivia quiz application built with PHP, jQuery, and Tailwind CSS. It allows users to register, log in, take quizzes on various topics, and view their quiz history.

## Features

- User registration and login
- Quiz customization (category, difficulty, number of questions)
- Timed quizzes
- Quiz history and results tracking
- Data migration from the Open Trivia Database API

## Requirements

- PHP 8.0 or higher
- MariaDB or MySQL
- Apache or Nginx web server

## Setup

1. **Clone the repository:**

   ```bash
   git clone https://github.com/your-username/your-repository.git
   ```

2. **Create a database:**

   - Create a new database in MariaDB or MySQL.
   - Import the `schema.sql` file to create the necessary tables.

3. **Configure the database connection:**

   - Open the `api/db.php` file and update the following constants with your database credentials:

     ```php
     define('DB_HOST', 'your_database_host');
     define('DB_NAME', 'your_database_name');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     ```

4. **Install dependencies:**

   - This project uses Tailwind CSS for styling. To compile the CSS, you need to install Node.js and npm. Then, run the following commands:

     ```bash
     npm install
     npm run build
     ```

5. **Run the application:**

   - You can use a local web server like XAMPP or MAMP, or you can use the built-in PHP web server:

     ```bash
     php -S localhost:8000
     ```

     Then, open your web browser and navigate to `http://localhost:8000`.

## Data Migration

This project includes a script to migrate data from the Open Trivia Database API to your local database. To run the migration, execute the following command in your terminal:

```bash
php api/migrate_data.php
```

This will fetch all the categories and 50 questions for each category from the API and store them in your database.

## Usage

- **Register:** Create a new account by providing a username and password.
- **Login:** Log in to your account to access the quiz functionality.
- **Customize Quiz:** Choose a category, difficulty level, and the number of questions for your quiz.
- **Take Quiz:** Answer the questions within the time limit.
- **View History:** View your past quiz results and track your progress.
- **Logout:** Log out of your account.
