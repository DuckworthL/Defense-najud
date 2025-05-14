# Employee Attendance System

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Employee Attendance System

This is a Laravel-based employee attendance tracking system that allows organizations to track and manage employee attendance records efficiently.

## Features

-   Employee management
-   Attendance tracking
-   Reporting system
-   Excel export capabilities (via Maatwebsite/Excel)
-   User authentication (via Laravel Breeze)

## System Requirements

-   PHP ^8.1
-   MySQL 5.7+ or MariaDB 10.3+
-   Composer
-   Node.js & NPM (for frontend assets)

## Installation

### Using Laragon

1. **Install Laragon**

    - Download from [laragon.org](https://laragon.org/download/)
    - Install the full version

2. **Clone Repository**

    ```bash
    cd C:\laragon\www
    git clone https://github.com/DuckworthL/employee-attendance.git
    cd employee-attendance
    ```

3. **Install Dependencies**

    ```bash
    composer install
    ```

4. **Set Up Environment**

    ```bash
    cp .env.example .env
    ```

    Edit the `.env` file to configure your database:

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=employee_attendance
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5. **Generate Application Key**

    ```bash
    php artisan key:generate
    ```

6. **Run Migrations**

    ```bash
    php artisan migrate
    ```

7. **Seed Database** (optional)

    ```bash
    php artisan db:seed
    ```

8. **Install Frontend Dependencies** (if applicable)

    ```bash
    npm install && npm run dev
    ```

9. **Access Application**
    - Visit http://employee-attendance.test in your browser (if Laragon's auto virtual hosts is enabled)
    - Or run `php artisan serve` and access http://localhost:8000

## Usage

[Add specific usage instructions here]

## Contributing

Thank you for considering contributing to the Employee Attendance System!

## Security

If you discover any security vulnerabilities, please create an issue or contact the repository owner.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Last Updated

2025-05-01
