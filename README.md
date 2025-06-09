<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# ESMS - Educational School Management System (SaaS)

A comprehensive multi-tenant SaaS school management system built with **Laravel 12**, implementing Domain-Driven Design principles. This platform enables multiple schools to operate independently on a single installation with complete data isolation and tenant-specific databases.

## 🚀 Latest Updates (June 2025)

- ✅ **Complete Database Seeding**: Comprehensive test data for all modules
- ✅ **Postman API Collection**: 50+ endpoints with authentication
- ✅ **XAMPP Compatibility**: Optimized for local XAMPP development
- ✅ **Multi-Tenant Architecture**: Fully implemented with tenant databases
- ✅ **Superadmin Dashboard**: Complete school management system
- ✅ **Academic Management**: Students, teachers, classes, subjects, exams
- ✅ **Library System**: Book management and issue tracking
- ✅ **Financial Management**: Fee collection and expense tracking
- ✅ **Attendance System**: Daily attendance tracking and reporting

## 🎯 Core Features

### **🏢 Multi-Tenancy & Infrastructure**
- **Database Isolation**: Complete database-per-tenant architecture using Stancl Tenancy
- **Domain-Driven Design**: Clean architecture with clear separation of concerns
- **Role-based Authorization**: Comprehensive permissions using Spatie Permission
- **API Authentication**: Secure authentication using Laravel Sanctum with token management
- **XAMPP Support**: Optimized for local development environment

### **🏫 School Management (Superadmin)**
- **School Registration**: Complete school onboarding with tenant database creation
- **Subscription Management**: Flexible subscription plans with feature controls
- **School Monitoring**: Real-time statistics and school status management
- **Inquiry System**: Public inquiry forms with approval workflows
- **Dynamic Forms**: Customizable form fields for school registration

### **👨‍🎓 Academic Management (Tenant)**
- **Student Information System**: Complete student profiles, enrollment, and tracking
- **Staff Management**: Teachers, administrators, and support staff management
- **Class & Section Management**: Organize students into classes and sections
- **Subject Management**: Curriculum organization and subject assignments
- **Academic Sessions**: Year-wise academic session management

### **📚 Educational Operations**
- **Examination System**: Create exams, manage results, and generate reports
- **Attendance Tracking**: Daily attendance with detailed reporting
- **Library Management**: Book catalog, issue/return tracking, and inventory
- **Notice Board**: School-wide announcements and communications
- **Fee Management**: Fee collection, payment tracking, and financial reports

## 🛠️ Project Setup

### **Prerequisites**

- **PHP**: 8.1 or higher
- **Composer**: Latest version
- **Database**: MySQL 8.0+ or PostgreSQL
- **Web Server**: Apache (XAMPP) or Nginx
- **Node.js**: For frontend assets (optional)

### **XAMPP Installation (Recommended for Local Development)**

1. **Download and Install XAMPP**:
   - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install with Apache and MySQL components

2. **Clone the Repository**:
   ```powershell
   cd C:\xampp\htdocs
   git clone https://github.com/pranav142240/esms.git
   cd esms
   ```

3. **Install Dependencies**:
   ```powershell
   composer install
   ```

4. **Environment Configuration**:
   ```powershell
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Configuration** (Update `.env`):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=esms_main
   DB_USERNAME=root
   DB_PASSWORD=
   
   # Multi-tenancy Configuration
   TENANCY_DATABASE_AUTO_CREATE=true
   TENANCY_DATABASE_AUTO_MIGRATE=true
   TENANCY_DATABASE_AUTO_DELETE=false
   ```

6. **Run Migrations and Seeders**:
   ```powershell
   php artisan migrate:fresh
   php artisan db:seed
   ```

7. **Access the Application**:
   - **Application URL**: `http://localhost/esms/public`
   - **Superadmin Login**: `superadmin@esms.com` / `SuperAdmin123!`

### **Alternative: Laravel Development Server**

If you prefer using Laravel's built-in server:

```powershell
php artisan serve
# Access at: http://localhost:8000
```

## 🏗️ Multi-Tenancy Architecture

This application implements **database-per-tenant** multi-tenancy using the Stancl Tenancy package:

### **Key Configuration**
```env
# Tenant Database Settings
TENANCY_DATABASE_AUTO_CREATE=true    # Auto-create tenant databases
TENANCY_DATABASE_AUTO_MIGRATE=true   # Auto-run migrations on tenant creation
TENANCY_DATABASE_AUTO_DELETE=false   # Preserve data on school suspension
TENANCY_DATABASE_PREFIX=esms_tenant_  # Database naming convention
```

### **Tenant Database Structure**
- **Central Database**: `esms_main` (superadmin data, schools, plans)
- **Tenant Databases**: `esms_tenant_{id}` (school-specific data)
- **Automatic Creation**: Tenant databases are created when schools are registered
- **Data Isolation**: Complete separation between school data

### **Domain Management**
- **Central Domain**: `localhost/esms/public` (Superadmin access)
- **Tenant Domains**: `localhost/esms/public/{school-domain}` (School access)
- **Dynamic Routing**: Automatic tenant resolution based on domain

## 🏛️ Domain-Driven Design Architecture

The project follows DDD principles with clear layered separation:

### **📁 Directory Structure**
```
app/
├── Domain/               # Core business logic
│   ├── Shared/          # Shared domain concepts
│   ├── Superadmin/      # Superadmin business rules
│   └── User/            # User domain logic
├── Application/         # Application services
│   └── Services/        # Use case implementations
├── Infrastructure/      # Technical implementations
│   └── Persistence/     # Data access layer
└── Interfaces/          # External interfaces
    └── Api/            # REST API controllers
```

### **🎯 Layer Responsibilities**
- **Domain Layer**: Business rules, entities, value objects
- **Application Layer**: Use cases, orchestration, validation
- **Infrastructure Layer**: Database, external services, caching
- **Interface Layer**: API controllers, request handling, responses

For detailed DDD implementation, see [`README-DDD.md`](README-DDD.md).

## 🔧 API Documentation

### **Postman Collection**
A comprehensive Postman collection with 50+ endpoints is available:

- **Collection**: `postman/ESMS_Superadmin_APIs.postman_collection.json`
- **Environment**: `postman/ESMS_Local_Development.postman_environment.json`
- **Documentation**: `postman/README.md`

### **Authentication**
```bash
POST /api/v1/auth/login
{
  "email": "superadmin@esms.com",
  "password": "SuperAdmin123!"
}
```

### **Key Endpoints**
- **Schools**: `/api/v1/schools` (CRUD operations)
- **Subscription Plans**: `/api/v1/subscription-plans`
- **School Inquiries**: `/api/v1/inquiries` (public endpoint)
- **Form Fields**: `/api/v1/form-fields`

For complete API documentation, see [`QUICK_START_GUIDE.md`](QUICK_START_GUIDE.md).

## 🗃️ Database Schema

### **Central Database Tables**
- `superadmins` - System administrators
- `schools` - Registered schools
- `subscription_plans` - Available plans
- `school_inquiries` - Registration requests
- `form_fields` - Dynamic form configuration

### **Tenant Database Tables**
- `users` - School staff and administrators
- `students` - Student information
- `teachers` - Teaching staff
- `classes` - Class organization
- `subjects` - Curriculum subjects
- `exams` - Examination management
- `daily_attendances` - Attendance tracking
- `books` - Library catalog
- `book_issues` - Library transactions
- `notices` - School announcements
- `expenses` - Financial tracking

## 🚀 Quick Start Guide

### **1. Initial Setup**
```powershell
# Start XAMPP (Apache + MySQL)
# Clone and setup project
cd C:\xampp\htdocs
git clone <repository-url> esms
cd esms
composer install
cp .env.example .env
php artisan key:generate
```

### **2. Database Setup**
```powershell
# Configure .env file with database credentials
php artisan migrate:fresh
php artisan db:seed
```

### **3. Test the System**
```powershell
# Access application
# URL: http://localhost/esms/public
# Login: superadmin@esms.com / SuperAdmin123!

# Test API endpoints
# Import Postman collection from /postman directory
```

### **4. Create First School**
1. Login as superadmin
2. Create subscription plan (or use seeded plans)
3. Create school inquiry (public endpoint)
4. Approve inquiry to create school with tenant database
5. Access tenant domain for school operations

## 📊 Testing & Validation

### **Seeded Data**
The system includes comprehensive test data:
- **1 Superadmin**: System administrator
- **3 Subscription Plans**: Basic, Standard, Premium
- **Multiple Schools**: With complete academic data
- **Students & Teachers**: Realistic profiles with relationships
- **Academic Data**: Classes, subjects, exams, attendance
- **Library Data**: Books and issue records
- **Financial Data**: Fee structures and expenses

### **API Testing**
Use the provided Postman collection for testing:
```powershell
# Quick API test
$body = @{email = "superadmin@esms.com"; password = "SuperAdmin123!"} | ConvertTo-Json
$headers = @{"Content-Type" = "application/json"; "Accept" = "application/json"}
$response = Invoke-RestMethod -Uri "http://localhost/esms/public/api/v1/auth/login" -Method POST -Body $body -Headers $headers
Write-Host "Token: $($response.data.token)"
```

## Working with Git

For detailed instructions on how to work with this Git repository, see `GIT-README.md`.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
