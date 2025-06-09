# ESMS Superadmin API - Quick Start Guide

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/MariaDB
- XAMPP (recommended for Windows)

### Latest Updates (June 2025)
- ✅ **XAMPP Compatibility**: Full support for XAMPP server environment
- ✅ **Database Seeding**: Comprehensive test data for all modules
- ✅ **Postman Collection**: Complete API collection with fixed environment
- ✅ **Multi-Tenant Architecture**: Enhanced school isolation and management

### Installation & Setup

1. **Clone and Install Dependencies**
   ```powershell
   cd c:\xampp\htdocs\esms
   composer install
   ```

2. **Environment Configuration**
   ```powershell
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup with Fresh Migration & Seeding**
   ```powershell
   php artisan migrate:fresh
   php artisan db:seed
   ```

4. **XAMPP Server Setup**
   - Start Apache and MySQL in XAMPP Control Panel
   - Access the application at: `http://localhost/esms/public`
   - API Base URL: `http://localhost/esms/public/api/v1`

   **Alternative - Laravel Development Server:**
   ```powershell
   php artisan serve --host=localhost --port=8000
   ```

## 🔑 Authentication

### Get Access Token (XAMPP)
```powershell
$headers = @{
    'Content-Type' = 'application/json'
}
$body = @{
    email = "superadmin@esms.com"
    password = "SuperAdmin123!"
} | ConvertTo-Json

Invoke-RestMethod -Uri 'http://localhost/esms/public/api/v1/auth/login' -Method POST -Headers $headers -Body $body
```

### Get Access Token (Laravel Serve)
```powershell
POST http://localhost:8000/api/v1/auth/login
Content-Type: application/json

{
  "email": "superadmin@esms.com",
  "password": "SuperAdmin123!"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Super Admin",
      "email": "superadmin@esms.com"
    },
    "token": "1|xjKPK7ObSTSpnLXdhaHyxODqsz2wXSkjjiD2JIZn1ae93600",
    "token_type": "Bearer",
    "expires_in": 604800
  }
}
```

### Use Token in Headers
```powershell
# For PowerShell requests
$headers = @{
    'Authorization' = 'Bearer YOUR_TOKEN_HERE'
    'Content-Type' = 'application/json'
}
```

## 📊 Seeded Test Data

The database seeder creates comprehensive test data:

### Superadmin Account
- **Email**: `superadmin@esms.com`
- **Password**: `SuperAdmin123!`

### Subscription Plans (4 plans)
- **Basic Plan**: $19.99/month - Up to 100 students
- **Standard Plan**: $49.99/month - Up to 500 students  
- **Pro Plan**: $99.99/month - Up to 2000 students
- **Enterprise Plan**: $199.99/month - Unlimited students

### Sample Schools (3 schools)
- **Greenwood High School** (domain: greenwood)
- **Sunrise Elementary** (domain: sunrise)
- **Oakridge Academy** (domain: oakridge)

### Student & Teacher Data
- 50+ students across different grades
- 15+ teachers with various subjects
- Academic records and library data
- Financial records and transactions

## 📋 Core API Endpoints

### School Management
```powershell
# List all schools (XAMPP)
Invoke-RestMethod -Uri 'http://localhost/esms/public/api/v1/schools' -Headers $headers

# Create new school (XAMPP)
$schoolData = @{
  name = "Demo School"
  email = "admin@demo.edu"
  phone = "+1234567890"
  address = "123 Education St"
  city = "Knowledge City"
  state = "CA"
  country = "USA"
  postal_code = "12345"
  website = "https://demo.edu"
  domain = "demo"
  subscription_plan_id = 2
  admin_first_name = "John"
  admin_last_name = "Doe"
  admin_email = "john@demo.edu"
  admin_phone = "+1234567891"
} | ConvertTo-Json

Invoke-RestMethod -Uri 'http://localhost/esms/public/api/v1/schools' -Method POST -Headers $headers -Body $schoolData

# Get school details (XAMPP)
Invoke-RestMethod -Uri 'http://localhost/esms/public/api/v1/schools/1' -Headers $headers

# Update school
PUT /api/v1/schools/{id}

# Delete school (soft delete)
DELETE /api/v1/schools/{id}

# School statistics
GET /api/v1/schools-statistics
```

### Subscription Plans
```bash
# List subscription plans
GET /api/v1/subscription-plans

# Create subscription plan
POST /api/v1/subscription-plans
{
  "name": "Custom Plan",
  "description": "A custom subscription plan",
  "price": 99.99,
  "currency": "USD",
  "billing_cycle": "monthly",
  "max_students": 1000,
  "max_teachers": 50,
  "max_admins": 5,
  "features": [
    "student_management",
    "attendance_tracking",
    "grade_management",
    "report_generation"
  ],
  "is_active": true
}
```

### School Inquiries (Public)
```bash
# Submit inquiry (No authentication required)
POST /api/v1/inquiries
{
  "school_name": "New School",
  "email": "contact@newschool.edu",
  "phone": "+1987654321",
  "domain": "newschool",
  "address": "456 Learning Ave",
  "contact_person": "Jane Smith",
  "tagline": "Excellence in Education"
}

# List inquiries (Authentication required)
GET /api/v1/inquiries

# Inquiry statistics
GET /api/v1/inquiries-statistics

# Approve inquiry
POST /api/v1/inquiries/{id}/approve
```

### Form Fields (Public)
```bash
# Get active form fields
GET /api/v1/form-fields/active
```

## 📊 Response Format

All API responses follow this structure:

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  },
  "meta": {
    // Pagination metadata (for paginated responses)
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

## 🔧 Error Handling

### Validation Errors
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "domain": ["The domain has already been taken."]
  }
}
```

### Authentication Errors
```json
{
  "success": false,
  "message": "Unauthenticated",
  "error": "Token expired or invalid"
}
```

## 🎯 Postman Collection

Import the following files into Postman:
- `postman/ESMS_Superadmin_APIs.postman_collection.json`
- `postman/ESMS_Local_Development.postman_environment.json`

**Pre-configured with:**
- All 35+ API endpoints
- Authentication flow
- Environment variables
- Request examples
- Test scripts

## 🏗️ Architecture

### Domain-Driven Design (DDD)
```
app/
├── Domain/           # Business logic and models
├── Application/      # Services and use cases  
├── Infrastructure/   # External services
└── Interfaces/       # Controllers and API layer
```

### Key Features
- ✅ Multi-tenant architecture
- ✅ Token-based authentication (7-day expiration)
- ✅ Role-based access control
- ✅ Subscription management
- ✅ Soft deletes (no hard deletes)
- ✅ Request validation
- ✅ API resource formatting
- ✅ Statistics and analytics

## 🔐 Security

- Sanctum token authentication
- Request validation and sanitization
- CORS middleware
- SQL injection protection
- Input validation for all endpoints
- Domain uniqueness validation
- Rate limiting ready

## 🚧 Development Status

**✅ COMPLETED:**
- Core superadmin authentication
- School management (CRUD)
- Subscription plan management
- School inquiry system
- Form fields management
- Statistics and analytics
- API documentation
- Postman collection

**🔄 TODO:**
- File upload handling
- Tenant database auto-creation
- Email notifications
- Advanced filtering
- Comprehensive testing suite

---

**🎉 SUPERADMIN CORE FUNCTIONALITY IS COMPLETE AND READY FOR DEVELOPMENT!**
