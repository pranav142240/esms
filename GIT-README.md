# ESMS - SaaS School Management System

A multi-tenant SaaS school management system built with Laravel, using Domain-Driven Design principles.

## Git Setup Instructions

This project is set up with Git for version control, allowing you to work on it from anywhere.

### Cloning the Repository

To work on this project from a new system:

```bash
# Clone the repository
git clone https://github.com/pranav142240/esms.git

# Navigate to the project directory
cd esms

# Install PHP dependencies
composer install

# Copy environment file and generate application key
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# If you're using npm for frontend assets
npm install
npm run dev
```

### Working with Branches

Good Git workflow practices:

```bash
# Create a new feature branch
git checkout -b feature/new-feature

# Make your changes and commit them
git add .
git commit -m "Implemented new feature"

# Push your changes to GitHub
git push origin feature/new-feature

# When ready, merge back to main
git checkout main
git merge feature/new-feature
git push origin main
```

### Setting Up a New Environment

When setting up the project on a new machine:

1. Clone the repository
2. Copy `.env.example` to `.env` and configure your environment variables
3. Generate an application key: `php artisan key:generate`
4. Set up your database connection in `.env`
5. Run migrations: `php artisan migrate`
6. Set up tenancy: Make sure `TENANCY_DATABASE_AUTO_CREATE=true` and `TENANCY_DATABASE_AUTO_MIGRATE=true` are in your `.env`

### Multi-tenancy Configuration

This application uses the Stancl Tenancy package to implement multi-tenancy. Key configuration:

- Each school (tenant) has its own database
- Tenant routes are defined in `routes/tenant.php`
- The system uses domain-based identification with `InitializeTenancyByDomain` middleware
- Each school can be accessed via their own subdomain

### Domain-Driven Design Structure

The project follows DDD principles with a clear separation of:

- Domain layer (`app/Domain/`) - Core business logic
- Application layer (`app/Application/`) - Orchestrates domain operations
- Infrastructure layer (`app/Infrastructure/`) - Technical implementations
- Interface layer (`app/Interfaces/`) - User interface concerns

See `README-DDD.md` for more details on the domain-driven design implementation.
