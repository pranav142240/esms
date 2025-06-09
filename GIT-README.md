# ESMS - SaaS School Management System

A comprehensive multi-tenant SaaS school management system built with Laravel 12, featuring Domain-Driven Design principles and robust subscription management.

## 🚀 Latest Updates (June 2025)
- ✅ **XAMPP Compatibility**: Full production-ready deployment
- ✅ **Comprehensive Database Seeding**: Realistic test data for all modules
- ✅ **Enhanced Multi-Tenancy**: Improved school isolation and management
- ✅ **Postman API Collection**: Complete testing environment setup
- ✅ **Subscription Management**: Advanced billing and plan handling

## 📋 Quick Start

### For XAMPP Users (Recommended)
```powershell
# Clone the repository
git clone https://github.com/pranav142240/esms.git c:\xampp\htdocs\esms

# Navigate to the project directory
cd c:\xampp\htdocs\esms

# Install PHP dependencies
composer install

# Copy environment file and generate application key
cp .env.example .env
php artisan key:generate

# Run fresh migrations with comprehensive seeding
php artisan migrate:fresh
php artisan db:seed

# Start XAMPP Apache and MySQL
# Access at: http://localhost/esms/public
```

### For Laravel Development Server
```powershell
# After the above setup steps
php artisan serve --host=localhost --port=8000
# Access at: http://localhost:8000
```

## Git Setup Instructions

This project uses Git for version control with comprehensive documentation and API testing.

### Cloning the Repository

To work on this project from a new system:

```powershell
# Clone the repository
git clone https://github.com/pranav142240/esms.git

# Navigate to the project directory
cd esms

# Install PHP dependencies
composer install

# Copy environment file and generate application key
cp .env.example .env
php artisan key:generate

# Setup database with comprehensive seeding
php artisan migrate:fresh
php artisan db:seed

# For frontend assets (if needed)
npm install
npm run dev
```

## 🔑 Default Credentials

After seeding, use these credentials to access the system:

- **Superadmin Email**: `superadmin@esms.com`
- **Superadmin Password**: `SuperAdmin123!`

## 📊 Seeded Test Data

The database seeder provides:
- 4 Subscription plans with different pricing tiers
- 3 Sample schools with complete tenant setup
- 50+ Students with academic records
- 15+ Teachers with subject assignments
- Library records and financial data
- School inquiries and form configurations

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
