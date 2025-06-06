# ESMS Superadmin API - Quick Start Guide

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/MariaDB
- XAMPP (recommended for Windows)

### Installation & Setup

1. **Clone and Install Dependencies**
   ```bash
   cd c:\xampp\htdocs\esms
   composer install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Start Development Server**
   ```bash
   php artisan serve --host=localhost --port=8000
   ```

## 🔑 Authentication

### Get Access Token
```bash
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
    "user": { ... },
    "token": "1|xjKPK7ObSTSpnLXdhaHyxODqsz2wXSkjjiD2JIZn1ae93600",
    "token_type": "Bearer",
    "expires_in": 604800
  }
}
```

### Use Token in Headers
```bash
Authorization: Bearer 1|xjKPK7ObSTSpnLXdhaHyxODqsz2wXSkjjiD2JIZn1ae93600
```

## 📋 Core API Endpoints

### School Management
```bash
# List all schools
GET /api/v1/schools

# Create new school
POST /api/v1/schools
{
  "name": "Demo School",
  "email": "admin@demo.edu",
  "phone": "+1234567890",
  "address": "123 Education St",
  "city": "Knowledge City",
  "state": "CA",
  "country": "USA",
  "postal_code": "12345",
  "website": "https://demo.edu",
  "domain": "demo",
  "subscription_plan_id": 2,
  "admin_first_name": "John",
  "admin_last_name": "Doe",
  "admin_email": "john@demo.edu",
  "admin_phone": "+1234567891"
}

# Get school details
GET /api/v1/schools/{id}

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
