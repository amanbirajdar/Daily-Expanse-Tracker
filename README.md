# Daily Expense Tracker

A responsive PHP and MySQL web application for recording expenses, managing budgets, storing receipts, and understanding spending patterns through interactive reports.

> **Project status:** This repository contains the PHP/MySQL application. A public live URL requires deployment to a PHP/MySQL hosting provider; the local XAMPP URL is not accessible from other laptops.

## Highlights

- Secure user registration and login
- Add, edit, and delete expense records
- Organize spending with predefined categories
- Upload and manage receipt images
- Set category budgets by daily, weekly, monthly, or yearly period
- Dashboard cards for totals, transaction count, and daily average
- Daily trend and category breakdown charts
- Date and category filters for detailed reports
- Responsive interface for desktop and mobile browsers
- Password hashing and prepared database statements

## Technology

- PHP 7.4+
- MySQL 5.7+ or MariaDB
- HTML, CSS, and JavaScript
- Chart.js for reports
- Apache through XAMPP, or another PHP-compatible web server

## Interface Preview

All images below are real screenshots captured from this repository's own Expance Daily Expense Tracker application. They are stored in the repository root and are not images from another project.

### Login page
![Expance login page](./1.png)

### Dashboard overview
![Expance dashboard overview](./2.png)

### Dashboard charts and actions
![Expance dashboard charts](./3.png)

### Landing page
![Expance landing page](./4.png)

### Add expense form
![Expance add expense form](./5.png)

### Budget management
![Expance budget management](./6.png)

### Expense reports
![Expance expense reports](./7.png)

## Local Setup with XAMPP

1. Clone the repository:

   ```bash
   git clone https://github.com/amanbirajdar/Daily-Expanse-Tracker.git
   cd Daily-Expanse-Tracker
   ```

2. Start Apache and MySQL from XAMPP.
3. Import `database.sql` into MySQL. It creates the `Expance_db` database.
4. Update `config/database.php` with your MySQL credentials.
5. Open `http://localhost/Daily-Expanse-Tracker/`.
6. Register an account and use Reports to view the charts.

## Database Schema

`database.sql` creates `users`, `expenses`, `budgets`, and `notifications` tables with foreign keys for user-owned records.

## Deployment Notes

For a public URL that works from any laptop, use hosting that supports PHP and MySQL/MariaDB, import `database.sql`, update `config/database.php` with hosted credentials, upload the files, and make `uploads/receipts/` writable. GitHub alone does not run PHP or provide MySQL hosting.

## Security

Passwords use PHP `password_hash()`, database operations use prepared statements, and user records are scoped to the authenticated session. Never commit production database passwords or API keys.

## License

This project is available under the MIT License.
