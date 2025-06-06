<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# ESMS - SaaS School Management System

A multi-tenant SaaS school management system built with Laravel, implementing Domain-Driven Design principles. This platform allows multiple schools to operate on a single installation with complete data isolation.

## Features

- **Multi-tenancy**: Complete database isolation per school using Stancl Tenancy
- **Domain-Driven Design**: Clean architecture with clear separation of concerns
- **Role-based Authorization**: Using Spatie Permission for staff, teachers, and students
- **API Authentication**: Using Laravel Sanctum
- **School Management**: Comprehensive tools for managing schools
- **Student Information System**: Track student data, attendance, and performance
- **Staff Management**: Manage teachers and administrative staff
- **Curriculum Management**: Organize courses, classes, and learning materials
- **Assessment & Grading**: Track and report on student assessments

## Project Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL or PostgreSQL
- Node.js & NPM (for frontend assets)

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/pranav142240/esms.git
   cd esms
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Copy environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Configure your database connection in `.env`

6. Run migrations:
   ```bash
   php artisan migrate
   ```

7. Run the development server:
   ```bash
   php artisan serve
   ```

### Multi-tenancy Setup

This application uses database-per-tenant multi-tenancy. Important environment variables:

```
TENANCY_DATABASE_AUTO_CREATE=true
TENANCY_DATABASE_AUTO_MIGRATE=true
```

## Domain-Driven Design

The project follows DDD principles with a clear separation of:

- **Domain Layer** (`app/Domain/`): Core business logic
- **Application Layer** (`app/Application/`): Orchestrates domain operations
- **Infrastructure Layer** (`app/Infrastructure/`): Technical implementations
- **Interface Layer** (`app/Interfaces/`): User interface concerns

For more details on the DDD implementation, see `README-DDD.md`.

## Working with Git

For detailed instructions on how to work with this Git repository, see `GIT-README.md`.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
