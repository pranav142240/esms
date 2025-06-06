# Product Requirements Document (PRD)
## Educational School Management System (ESMS)
### Multi-Tenant SaaS Application

---

## 1. Project Overview

### 1.1 Product Vision
Build a comprehensive multi-tenant SaaS application for educational institutions using Laravel 12, where a superadmin manages paid school subscriptions with expiration-based access control and each school operates as an isolated tenant with their own database and authentication system.

### 1.2 Technology Stack
- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum (API-only)
- **Multi-tenancy**: Tenancy for Laravel
- **Database**: MySQL (Central + Tenant databases)
- **Architecture**: RESTful API-only (no frontend, JSON responses)
- **Controllers**: Resource Controllers for CRUD operations
- **Code Generation**: Laravel Artisan commands for scaffolding
- **Data Deletion**: Soft deletes only (no hard deletes)
- **Business Model**: Subscription-based with expiration management

---

## 2. Architecture Overview

### 2.1 Database Architecture
- **Central Database**: Stores superadmin data, subscription plans, school inquiries, registered schools metadata, custom form fields, and billing information
- **Tenant Databases**: Individual databases per school with isolated data and users
- **Soft Deletes**: All models use soft deletes - no hard deletion of data

### 2.2 API Architecture
- **RESTful API Design**: All endpoints follow REST conventions
- **Resource Controllers**: Laravel resource controllers for CRUD operations
- **JSON Responses**: All responses in JSON format
- **API Versioning**: Version prefix (e.g., `/api/v1/`)
- **Authentication**: Token-based using Laravel Sanctum

### 2.3 Domain Structure
- **Central API**: `superadmin.localhost/api` - Superadmin management endpoints
- **Tenant APIs**: `{schoolname}.localhost/api` - Individual school endpoints

---

## 3. User Roles & Authentication

### 3.1 Superadmin (Owner - Central Database)
- **Role**: System owner who manages the entire SaaS platform
- **Single User**: One hardcoded/seeded superadmin account
- **Credentials**:
  - Email: `superadmin@example.com`
  - Password: Securely hashed
- **Guard**: `superadmin`
- **Access**: Central API only (`superadmin.localhost/api`)
- **Authentication**: Sanctum token-based (7-day expiration)
- **Responsibilities**:
  - Add, approve, and manage schools
  - Create and manage customized subscription packages
  - Manage addon bundles to enhance application features
  - Set subscription expiration dates and monitor renewals
  - Configure system settings (payment, language, SMTP, about, session manager)
  - Update account settings and profile management
  - Monitor payments and financial activities
  - Manage system-wide configurations and preferences
  - Add, edit, and remove school admins

### 3.2 School User Hierarchy (Tenant Database)
This school management system is designed to manage seven kinds of users with different roles and permissions using Spatie Laravel Permissions package.

#### 3.2.1 Admin (School Administrator)
- **Role**: Highest permissions after superadmin within the school
- **Access**: Full school management capabilities
- **Permissions**:
  - Monitor, control academics, examinations, live classes
  - Manage accounting and back-office activities
  - User management for the school
  - Access all school features based on subscription plan
- **Subscription Dependent**: Account status tied to school subscription

#### 3.2.2 Teacher
- **Role**: Teaching and academic-focused activities
- **Permissions**:
  - Overview student lists, class routines, subjects
  - Manage event calendar and back-office activities
  - Edit and delete student and parent information
  - Create syllabus and manage live classes
  - Provide marks and comments on examinations
  - Add assignments, create questions, and review them
  - Create and modify online courses
  - Publish, draft, and expire content

#### 3.2.3 Parent
- **Role**: Monitor their children's academic progress
- **Permissions**:
  - Overview teacher lists and academic activities
  - View daily attendance, class routines, syllabus
  - Monitor event calendar and back-office activities
  - View exam marks and grades
  - Pay fees for their children
  - Access student-related reports

#### 3.2.4 Student
- **Role**: Access academic content and submit work
- **Permissions**:
  - Overview teacher lists and daily attendance
  - View class routines, syllabus, event calendar
  - Access back-office, exam marks, and grades
  - Submit assignments and join live classes
  - Pay fees and issue library books
  - Watch online courses and access course information

#### 3.2.5 Accountant
- **Role**: Financial management and reporting
- **Permissions**:
  - Full financial activities access
  - Add, edit, delete expenses and expense categories
  - Create mass or single invoices
  - Export financial reports (CSV, PDF, Print)
  - Manage fee collection and payment tracking

#### 3.2.6 Librarian
- **Role**: Library management and book tracking
- **Permissions**:
  - Track all library books and resources
  - Issue and manage book loans
  - Add, edit, remove books from library system
  - Generate library reports and statistics
  - Manage library member records

### 3.3 Authentication & Access Control
- **Guard**: `web` or `tenant` for all school users
- **Access**: School-specific API (`{schoolname}.localhost/api`)
- **Authentication**: Sanctum token-based (1-2 day expiration)
- **RBAC**: Spatie Laravel Permissions for role-based access control
- **Subscription Validation**: All school user access depends on active subscription

---

## 5. Core Features

### 5.1 Superadmin Features (Central API)

#### 5.1.1 Subscription Plans Management
**Purpose**: Create and manage subscription plans for schools

**Features**:
- Create, edit, delete subscription plans
- Set pricing (monthly/yearly)
- Define feature limits (users, storage, etc.)
- Plan activation/deactivation
- Default plan templates

**Plan Properties**:
- Plan name and description
- Pricing structure (monthly/yearly)
- User limits
- Storage limits
- Feature permissions
- Support level
- Custom branding options

#### 5.1.2 Custom School Registration Form Management
**Purpose**: Configure dynamic registration forms for schools

**Features**:
- Create, edit, delete custom form fields
- Field types: Text, Email, Phone, File Upload, Textarea, Select, etc.
- Mark fields as required or optional
- Drag-and-drop field ordering
- Field validation rules

**Default Required Fields**:
- School Name (text, required)
- School Logo (file upload, required)
- School Email (email, required)
- Support Email (email, optional)
- School Phone (phone, required)
- Tagline (text, optional)
- Address (textarea, required)
- School Code Prefix (text, auto-generated pattern: "SCH{YYYY}{incremental}")
- Domain Name (text, required, validation for subdomain format) unique

**Additional Custom Fields** (configurable):
- Principal Name
- Establishment Year
- Student Capacity
- School Type (Primary/Secondary/Higher)
- Board Affiliation
- License Number
- etc.

#### 5.1.3 School Inquiry Management
**Purpose**: Handle incoming registration requests from prospective schools

**Features**:
- View all school inquiries in a data table
- Filter by status (Pending, Under Review, Approved, Rejected)
- Search by school name, email, or domain
- View detailed inquiry information
- Add internal notes/comments
- Email communication with applicants
- Bulk actions (approve, reject, archive)

**Inquiry Statuses**:
- `pending` - Newly submitted
- `under_review` - Being evaluated
- `approved` - Ready for registration
- `rejected` - Application denied
- `registered` - School is now a tenant

#### 5.1.4 School Management & Subscription Control
**Purpose**: Manage registered schools, their subscriptions, and access control

**School Creation Workflow**:
1. Superadmin logs into the application
2. Clicks "Create school" option from left menu
3. Provides required school information:
   - School name, email, phone
   - Address and domain details
   - Logo upload
   - Subscription plan assignment
   - Expiration date setting
4. Clicks "Submit" button for confirmation
5. System auto-creates tenant database with default schema

**School Editing Workflow**:
1. Superadmin logs into the application
2. Selects "Schools" option from left menu
3. Chooses a school from the list
4. Clicks action option for more options
5. Selects "Edit" button for confirmation
6. Updates required information
7. Clicks "Save" button to save changes

**School Activation/Deactivation Workflow**:
1. Superadmin logs into the application
2. Selects "Schools" option from left menu
3. Chooses a school from the list
4. Clicks action option for more options
5. Selects "Active/Deactivate" button for confirmation
6. System updates school status immediately

**Features**:
- Create new schools with complete setup
- Edit existing school information and settings
- Assign and modify subscription plans
- Set subscription start and expiration dates
- Manage school status (active/inactive/suspended)
- Track subscription renewals and payments
- Configure school metadata (domain, email, status)
- View school statistics and usage analytics
- Manual subscription extensions and modifications
- Payment tracking and confirmation workflows

**Subscription Management**:
- Assign plan during school creation or editing
- Set expiration dates (1 month, 2 months, custom periods)
- Payment confirmation workflow with manual verification
- Automatic status updates based on subscription expiration
- Grace period management (7 days post-expiration)
- Renewal notifications and tracking system

**School Statuses**:
- `active` - Subscription valid and fully operational
- `inactive` - Subscription expired or not yet activated
- `suspended` - Manually suspended by superadmin action
- `terminated` - Permanently closed (soft deleted)

### 5.2 Tenant Features (School APIs)

#### 5.2.1 School Admin Features (Based on Screenshot)
**Purpose**: Complete school management system with all features shown in the provided screenshot

**Features**:
- **Manage Admin**: Add/edit school admin accounts
- **Inactive School**: View and reactivate inactive status
- **Edit**: Modify school information and settings
- **Delete**: Soft delete records (no hard deletes)
- **User Management**: Complete CRUD for school users
- **Role Management**: Create and assign roles with permissions
- **Dashboard**: School-specific analytics and overview
- **Profile Management**: User profile and settings

**Access Control**:
- Plan-based feature restrictions
- Role-based permissions within school
- Subscription status validation
- Expiration date enforcement

#### 5.2.2 Authentication & User Management
- Separate login system per school
- User registration for school staff
- Role-based permissions (Admin, Teacher, Staff, etc.)
- Password reset functionality
- User profile management

#### 5.2.3 School Dashboard
- School-specific dashboard
- Quick stats and metrics
- Recent activities
- Navigation to school modules

#### 5.2.4 RBAC (Role-Based Access Control)
- Define custom roles per school
- Assign permissions to roles
- User role assignment
- Permission-based route protection

---

## 4. Subscription Plans & Billing

### 4.1 Subscription Plans Management
The superadmin can create and manage multiple subscription plans with different features and pricing.

#### 4.1.1 Default Subscription Plans

**Basic Plan**
- **Price**: $29/month
- **Features**:
  - Up to 50 users
  - Basic user management
  - Basic reporting
  - Email support
  - 1GB storage
- **Duration**: Monthly/Yearly billing
- **School Features**: User management, basic dashboard

**Pro Plan**
- **Price**: $79/month
- **Features**:
  - Up to 200 users
  - Advanced user management
  - Advanced reporting & analytics
  - Priority email support
  - 10GB storage
  - Custom roles & permissions
  - API access
- **Duration**: Monthly/Yearly billing
- **School Features**: All Basic + advanced reporting, custom roles

**Enterprise Plan**
- **Price**: $199/month
- **Features**:
  - Unlimited users
  - Full feature access
  - Priority phone & email support
  - 100GB storage
  - Advanced integrations
  - Custom branding
  - Dedicated support
- **Duration**: Monthly/Yearly billing
- **School Features**: All Pro + custom branding, integrations

**Custom Plan**
- **Price**: Custom pricing
- **Features**: Tailored to specific school needs
- **Duration**: Flexible
- **School Features**: Customizable feature set

### 4.2 Subscription Management
- **Plan Assignment**: Superadmin assigns plans during school approval
- **Expiration Tracking**: Automatic expiration date calculation
- **Status Management**: Active/Inactive based on subscription validity
- **Payment Tracking**: Manual payment confirmation by superadmin
- **Renewal Process**: Superadmin extends expiration dates upon payment

### 4.3 School Access Control
- **Active Status**: Full access to assigned plan features
- **Inactive Status**: Login disabled, data preserved
- **Grace Period**: 7 days after expiration before account deactivation
- **Reactivation**: Immediate upon subscription renewal

---

## 7. Technical Requirements

### 7.1 API Response Format
All API responses should follow a consistent JSON structure:

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        // Response data here
    },
    "meta": {
        // Pagination, timestamps, etc.
    }
}
```

Error responses:
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Validation error messages"]
    }
}
```

### 7.2 Database Design

#### 7.2.1 Central Database Tables
```sql
-- Superadmin
superadmins (id, name, email, password, created_at, updated_at)

-- Custom Form Fields
form_fields (id, name, label, type, required, options, validation_rules, order, created_at, updated_at)

-- School Inquiries
school_inquiries (id, form_data_json, status, notes, submitted_at, reviewed_at, created_at, updated_at)

-- Registered Schools (Tenant Metadata)
schools (id, name, domain, email, status, database_name, form_data_json, approved_at, created_at, updated_at)

-- Tenancy Package Tables
tenants (id, data, created_at, updated_at)
domains (id, domain, tenant_id, created_at, updated_at)

-- Subscription Plans
subscription_plans (id, name, price, features, duration, created_at, updated_at)

-- School Subscriptions
school_subscriptions (id, school_id, plan_id, start_date, end_date, status, created_at, updated_at)
```

#### 7.2.2 Tenant Database Schema
```sql
-- Default schema auto-created for each school
users (id, name, email, password, created_at, updated_at, deleted_at)
roles (id, name, guard_name, created_at, updated_at) -- Spatie roles
permissions (id, name, guard_name, created_at, updated_at) -- Spatie permissions
model_has_roles (role_id, model_type, model_id) -- Spatie user-role assignment
model_has_permissions (permission_id, model_type, model_id) -- Spatie user-permission assignment
role_has_permissions (permission_id, role_id) -- Spatie role-permission assignment

-- School-specific tables
students (id, user_id, student_code, class_id, section_id, created_at, updated_at, deleted_at)
parents (id, user_id, student_id, relationship, created_at, updated_at, deleted_at)
teachers (id, user_id, employee_code, subject_ids, created_at, updated_at, deleted_at)
classes (id, name, grade_level, created_at, updated_at, deleted_at)
subjects (id, name, code, class_id, teacher_id, created_at, updated_at, deleted_at)
-- Additional school management tables as needed
```

#### 7.2.3 Default Roles & Permissions Setup
Each tenant database will be seeded with predefined roles and permissions:

**Roles:**
- Admin (full school access)
- Teacher (academic activities)
- Parent (student monitoring)
- Student (learning activities)
- Accountant (financial management)
- Librarian (library management)

**Permission Categories:**
- User Management (create, read, update, delete users)
- Academic Management (classes, subjects, syllabus)
- Examination Management (exams, marks, grades)
- Financial Management (fees, expenses, invoices)
- Library Management (books, issues, returns)
- Reporting (generate various reports)
- Communication (messages, notifications)

### 7.3 Middleware & Guards

#### 7.3.1 API Route Groups
```php
// Central API Routes (superadmin.localhost/api)
Route::prefix('api/v1')->middleware(['api', 'superadmin.auth'])->group(function () {
    Route::apiResource('form-fields', FormFieldController::class);
    Route::apiResource('inquiries', SchoolInquiryController::class);
    Route::apiResource('schools', SchoolController::class);
    Route::apiResource('subscription-plans', SubscriptionPlanController::class);
    Route::apiResource('school-subscriptions', SchoolSubscriptionController::class);
});

// Tenant API Routes (schoolname.localhost/api)
Route::prefix('api/v1')->middleware(['api', 'tenant.auth'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);
});
```

#### 7.3.2 Custom Middleware
- `SuperadminAuth`: Verify superadmin authentication
- `TenantAuth`: Verify tenant user authentication
- `TenantContext`: Set tenant database context
- `DomainResolver`: Resolve tenant from subdomain

### 7.4 Security Requirements
- CSRF protection on all forms
- Rate limiting on login attempts
- Secure password hashing (bcrypt)
- SQL injection prevention
- XSS protection
- Secure file upload handling
- SSL/TLS enforcement

---

## 8. User Workflows

### 8.1 School Registration Flow (API)
1. Prospective school visits public registration page
2. Fills out dynamic form (based on superadmin configuration)
3. Submits inquiry → stored in central database
4. Superadmin reviews inquiry in admin panel
5. Superadmin approves → triggers tenant creation
6. System auto-creates tenant database with default schema
7. School receives credentials and access to their subdomain
8. School sets up their portal and users

### 6.2 Superadmin Daily Workflow
1. Login to `superadmin.localhost`
2. Review new school inquiries
3. Manage form fields configuration
4. Approve/reject pending applications
5. Monitor active schools status
6. Handle support requests

### 6.3 School User Workflow
1. Access school-specific subdomain (`schoolname.localhost`)
2. Login with school credentials
3. Access role-based dashboard
4. Perform school-specific tasks
5. Manage users within school (if admin)

---

## 7. Implementation Phases

### Phase 1: Core Infrastructure
- [x] Laravel 12 setup with tenancy package
- [ ] Central database structure
- [ ] Superadmin authentication
- [ ] Basic tenant creation
- [ ] Domain routing

### Phase 2: Superadmin Features
- [ ] Custom form field management
- [ ] School inquiry system
- [ ] School approval workflow
- [ ] Tenant database auto-creation

### Phase 3: Tenant Features
- [ ] Tenant authentication system
- [ ] Basic school dashboard
- [ ] User management
- [ ] RBAC implementation

### Phase 4: Advanced Features
- [ ] File upload handling
- [ ] Email notifications
- [ ] Advanced reporting
- [ ] API endpoints

---

## 8. Future Enhancements
- Multi-language support
- Advanced reporting and analytics
- Mobile application
- Integration with third-party services
- Subscription and billing management
- Advanced school modules (students, classes, etc.)

---

## 9. Success Criteria
- Superadmin can efficiently manage school registrations
- Schools operate independently with full data isolation
- System scales to handle multiple tenants
- Security standards are maintained
- Performance remains optimal with growing tenants

---

## 5. API Endpoints Design

### 5.1 Central API Endpoints (Superadmin)

#### 5.1.1 Authentication
```
POST   /api/auth/login                 # Superadmin login
POST   /api/auth/logout                # Superadmin logout
GET    /api/auth/user                  # Get authenticated superadmin
```

#### 5.1.2 Form Fields Management
```
GET    /api/form-fields                # List all form fields
POST   /api/form-fields                # Create new form field
GET    /api/form-fields/{id}           # Show specific form field
PUT    /api/form-fields/{id}           # Update form field
DELETE /api/form-fields/{id}           # Delete form field
POST   /api/form-fields/reorder        # Reorder form fields
```

#### 5.1.3 School Inquiries Management
```
GET    /api/inquiries                  # List all inquiries (with filters)
POST   /api/inquiries                  # Create new inquiry (public endpoint)
GET    /api/inquiries/{id}             # Show specific inquiry
PUT    /api/inquiries/{id}             # Update inquiry (add notes, change status)
DELETE /api/inquiries/{id}             # Delete inquiry
POST   /api/inquiries/bulk-action      # Bulk approve/reject/archive
```

#### 5.1.4 Schools Management
```
GET    /api/schools                    # List all registered schools
POST   /api/schools                    # Create/register new school
GET    /api/schools/{id}               # Show specific school
PUT    /api/schools/{id}               # Update school details
DELETE /api/schools/{id}               # Delete/terminate school
POST   /api/schools/{id}/suspend       # Suspend school
POST   /api/schools/{id}/activate      # Activate school
```

#### 5.1.5 Subscription Plans Management
```
GET    /api/subscription-plans          # List all subscription plans
POST   /api/subscription-plans          # Create new subscription plan
GET    /api/subscription-plans/{id}     # Show specific subscription plan
PUT    /api/subscription-plans/{id}     # Update subscription plan
DELETE /api/subscription-plans/{id}     # Delete subscription plan
```

#### 5.1.6 School Subscriptions Management
```
GET    /api/school-subscriptions        # List all school subscriptions
POST   /api/school-subscriptions        # Create new school subscription
GET    /api/school-subscriptions/{id}   # Show specific school subscription
PUT    /api/school-subscriptions/{id}   # Update school subscription
DELETE /api/school-subscriptions/{id}   # Delete school subscription
```

### 5.2 Tenant API Endpoints (Schools)

#### 5.2.1 Authentication
```
POST   /api/auth/login                 # School user login
POST   /api/auth/logout                # School user logout
POST   /api/auth/register              # Register new school user
GET    /api/auth/user                  # Get authenticated user
```

#### 5.2.2 User Management
```
GET    /api/users                      # List school users
POST   /api/users                      # Create new user
GET    /api/users/{id}                 # Show specific user
PUT    /api/users/{id}                 # Update user
DELETE /api/users/{id}                 # Delete user
```

#### 5.2.3 Role Management
```
GET    /api/roles                      # List school roles
POST   /api/roles                      # Create new role
GET    /api/roles/{id}                 # Show specific role
PUT    /api/roles/{id}                 # Update role
DELETE /api/roles/{id}                 # Delete role
```

---

## 6. Laravel Artisan Commands for Development

### 6.1 Model Generation
```bash
# Central Models
php artisan make:model Superadmin -m
php artisan make:model FormField -m
php artisan make:model SchoolInquiry -m
php artisan make:model School -m
php artisan make:model SubscriptionPlan -m
php artisan make:model SchoolSubscription -m

# Tenant Models (will be created in tenant context)
php artisan make:model User -m
php artisan make:model Role -m
php artisan make:model Permission -m
```

### 6.2 Controller Generation (Resource Controllers)
```bash
# Central API Controllers
php artisan make:controller Api/SuperadminAuthController
php artisan make:controller Api/FormFieldController --resource --api
php artisan make:controller Api/SchoolInquiryController --resource --api
php artisan make:controller Api/SchoolController --resource --api
php artisan make:controller Api/SubscriptionPlanController --resource --api
php artisan make:controller Api/SchoolSubscriptionController --resource --api

# Tenant API Controllers
php artisan make:controller Api/Tenant/AuthController
php artisan make:controller Api/Tenant/UserController --resource --api
php artisan make:controller Api/Tenant/RoleController --resource --api
php artisan make:controller Api/Tenant/DashboardController
```

### 6.3 Request Validation Classes
```bash
# Central Requests
php artisan make:request SuperadminLoginRequest
php artisan make:request FormFieldRequest
php artisan make:request SchoolInquiryRequest
php artisan make:request SchoolRequest
php artisan make:request SubscriptionPlanRequest
php artisan make:request SchoolSubscriptionRequest

# Tenant Requests
php artisan make:request Tenant/LoginRequest
php artisan make:request Tenant/UserRequest
php artisan make:request Tenant/RoleRequest
```

### 6.4 Resource Classes (API Responses)
```bash
# Central Resources
php artisan make:resource SuperadminResource
php artisan make:resource FormFieldResource
php artisan make:resource SchoolInquiryResource
php artisan make:resource SchoolResource
php artisan make:resource SubscriptionPlanResource
php artisan make:resource SchoolSubscriptionResource

# Tenant Resources
php artisan make:resource Tenant/UserResource
php artisan make:resource Tenant/RoleResource
```

### 6.5 Middleware Generation
```bash
php artisan make:middleware SuperadminAuth
php artisan make:middleware TenantAuth
php artisan make:middleware SetTenantContext
```

### 6.6 Seeder Generation
```bash
php artisan make:seeder SuperadminSeeder
php artisan make:seeder DefaultFormFieldsSeeder
php artisan make:seeder TenantUserSeeder
php artisan make:seeder SubscriptionPlansSeeder
```

### 6.7 Migration Generation
```bash
# Central migrations
php artisan make:migration create_superadmins_table
php artisan make:migration create_form_fields_table
php artisan make:migration create_school_inquiries_table
php artisan make:migration create_schools_table
php artisan make:migration create_subscription_plans_table
php artisan make:migration create_school_subscriptions_table

# Tenant migrations
php artisan make:migration create_tenant_users_table
php artisan make:migration create_tenant_roles_table
php artisan make:migration create_tenant_permissions_table
```

---

*Last Updated: June 6, 2025*
*Version: 1.0*
