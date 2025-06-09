# ESMS Superadmin API Collection

## Overview
This Postman collection provides comprehensive API testing for the ESMS (Educational School Management System) Laravel 12 Multi-Tenant SaaS application. The collection includes all superadmin management endpoints with proper authentication, validation, and error handling.

## 🚀 Latest Updates (June 2025)
- ✅ **XAMPP Compatibility**: Updated all URLs for XAMPP deployment
- ✅ **Environment Configuration**: Fixed base URL and authentication setup
- ✅ **PowerShell Examples**: Added Windows-compatible testing commands
- ✅ **Comprehensive Seeding**: Pre-populated realistic test data
- ✅ **Token Management**: Automatic token refresh and validation

## Prerequisites
- XAMPP/WAMP/LAMP server running (XAMPP recommended)
- Laravel application accessible at `http://localhost/esms/public`
- Database properly migrated and seeded with comprehensive test data
- Postman application installed

## Getting Started

### 1. Import the Collection
1. Open Postman
2. Click "Import" button
3. Select `ESMS_Superadmin_APIs.postman_collection.json`
4. Import `ESMS_Local_Development.postman_environment.json`

### 2. Set Environment
1. Select "ESMS Local Development" environment from the dropdown
2. Verify the following variables are correctly set:
   - `base_url`: `http://localhost/esms/public` (for XAMPP)
   - `superadmin_email`: `superadmin@esms.com`
   - `superadmin_password`: `SuperAdmin123!`
   - `auth_token`: (auto-populated after login)

### 3. Authentication Flow
1. **Login First**: Run the "Authentication > Login" request
2. The auth token will be automatically saved to the environment
3. All subsequent requests will use this token automatically
4. Token expires in 7 days (604800 seconds)

## 📊 Pre-seeded Test Data

The database comes with comprehensive test data:
- **1 Superadmin Account**: `superadmin@esms.com` / `SuperAdmin123!`
- **4 Subscription Plans**: Basic ($19.99), Standard ($49.99), Pro ($99.99), Enterprise ($199.99)
- **3 Sample Schools**: Greenwood High, Sunrise Elementary, Oakridge Academy
- **50+ Students**: Across different grades with complete academic records
- **15+ Teachers**: With subject assignments and contact information
- **Academic Data**: Grades, library records, financial transactions

## API Endpoints Overview

### 🔐 Authentication
- **POST** `/api/v1/auth/login` - Login superadmin
- **GET** `/api/v1/auth/user` - Get authenticated user info
- **POST** `/api/v1/auth/refresh` - Refresh authentication token
- **POST** `/api/v1/auth/logout` - Logout superadmin

### 🏫 School Management
- **GET** `/api/v1/schools` - List all schools with pagination/filtering
- **POST** `/api/v1/schools` - Create new school (with tenant database)
- **GET** `/api/v1/schools/{id}` - Get school details
- **PUT** `/api/v1/schools/{id}` - Update school information
- **DELETE** `/api/v1/schools/{id}` - Soft delete school
- **POST** `/api/v1/schools/{id}/suspend` - Suspend school
- **POST** `/api/v1/schools/{id}/activate` - Activate suspended school
- **GET** `/api/v1/schools-statistics` - Get schools statistics

### 💳 Subscription Plans
- **GET** `/api/v1/subscription-plans` - List all subscription plans
- **POST** `/api/v1/subscription-plans` - Create new subscription plan
- **GET** `/api/v1/subscription-plans/{id}` - Get subscription plan details
- **PUT** `/api/v1/subscription-plans/{id}` - Update subscription plan
- **DELETE** `/api/v1/subscription-plans/{id}` - Soft delete subscription plan
- **POST** `/api/v1/subscription-plans/{id}/toggle-status` - Toggle plan status

### 📝 Form Fields Management
- **GET** `/api/v1/form-fields` - List all form fields
- **GET** `/api/v1/form-fields/active` - Get active form fields (public)
- **POST** `/api/v1/form-fields` - Create new form field
- **GET** `/api/v1/form-fields/{id}` - Get form field details
- **PUT** `/api/v1/form-fields/{id}` - Update form field
- **DELETE** `/api/v1/form-fields/{id}` - Soft delete form field
- **POST** `/api/v1/form-fields/update-order` - Update form fields order
- **POST** `/api/v1/form-fields/{id}/toggle-status` - Toggle field status

### 📧 School Inquiries
- **POST** `/api/v1/inquiries` - Create school inquiry (public endpoint)
- **GET** `/api/v1/inquiries` - List all school inquiries
- **GET** `/api/v1/inquiries/{id}` - Get inquiry details
- **PUT** `/api/v1/inquiries/{id}` - Update inquiry status/notes
- **DELETE** `/api/v1/inquiries/{id}` - Soft delete inquiry
- **POST** `/api/v1/inquiries/{id}/approve` - Approve inquiry & create school
- **POST** `/api/v1/inquiries/bulk-action` - Bulk actions on inquiries
- **GET** `/api/v1/inquiries-statistics` - Get inquiries statistics

## Authentication Details

### Token-Based Authentication
- Uses Laravel Sanctum for API authentication
- Tokens expire after 7 days
- Automatic token refresh available
- Middleware validates superadmin permissions

### Login Credentials
- **Email**: `superadmin@esms.com`
- **Password**: `SuperAdmin123!`

## Request/Response Format

### Standard Response Structure
```json
{
    "success": true|false,
    "message": "Response message",
    "data": {...}, // Response data
    "meta": {...}  // Pagination/additional metadata
}
```

### Error Response Structure
```json
{
    "success": false,
    "message": "Error message",
    "errors": {...} // Validation errors (if applicable)
}
```

## Testing Scenarios

### 1. Basic Workflow
1. Login as superadmin
2. Get subscription plans
3. Create a new school
4. View school details
5. Update school information

### 2. School Management Workflow
1. Create school inquiry (public)
2. View all inquiries
3. Approve inquiry (creates school automatically)
4. Manage school (suspend/activate)
5. View school statistics

### 3. Subscription Management
1. Create new subscription plan
2. Update plan features
3. Toggle plan status
4. Assign plan to school

### 4. Form Fields Management
1. Create custom form fields
2. Update field order
3. Toggle field status
4. Get active fields for public use

## Environment Variables

| Variable | Description | Default Value |
|----------|-------------|---------------|
| `base_url` | Application base URL | `http://localhost/esms/public` |
| `superadmin_email` | Superadmin login email | `superadmin@esms.com` |
| `superadmin_password` | Superadmin login password | `SuperAdmin123!` |
| `auth_token` | Authentication token | *Auto-populated after login* |

## Features

### Automatic Token Management
- Login request automatically saves token to environment
- All authenticated requests use the saved token
- Refresh token request updates the stored token

### Comprehensive Validation
- All requests include proper validation
- Clear error messages for validation failures
- Proper HTTP status codes

### Pagination Support
- List endpoints support pagination
- Configurable page size
- Search and filtering capabilities

### Soft Deletes
- All delete operations are soft deletes
- Data preservation for audit trails
- Ability to restore deleted records

## Troubleshooting

### Common Issues

1. **Token Not Found Error**
   - Run the login request first
   - Check if token is saved in environment variables

2. **Route Not Found**
   - Verify base_url is correct
   - Ensure Laravel server is running
   - Check route cache: `php artisan route:clear`

3. **Validation Errors**
   - Check request body format
   - Verify required fields are provided
   - Ensure data types match requirements

4. **Database Errors**
   - Run migrations: `php artisan migrate`
   - Run seeders: `php artisan db:seed`
   - Check database connection

## API Documentation Features

### Request Examples
Each endpoint includes:
- Sample request body
- Required headers
- Query parameters
- Expected response format

### Error Handling
- Comprehensive error responses
- Validation error details
- HTTP status code explanations

### Business Logic
- School tenant database creation
- Subscription plan management
- Role-based access control
- Multi-tenant architecture support

## Development Notes

### Architecture
- Domain-Driven Design (DDD) structure
- Repository pattern for data access
- Service layer for business logic
- API-only architecture with Sanctum

### Security
- Token-based authentication
- Middleware authorization
- Input validation and sanitization
- SQL injection prevention

### Performance
- Pagination for large datasets
- Efficient database queries
- Route caching
- Optimized responses

---

## Support
For issues or questions, please refer to the main application documentation or contact the development team.
