# Product Requirements Document (PRD)
## Educational School Management System (ESMS)
### Multi-Tenant SaaS Application
**Version 1.3** | **Last Updated: June 10, 2025**

## 🚀 Latest Updates (June 2025)
- ✅ **Admin Two-Phase System**: Complete implementation of admin-to-tenant conversion workflow
- ✅ **School Setup Controller**: Full school creation and conversion process with validation
- ✅ **Email Notifications**: Welcome emails and school creation confirmations
- ✅ **Tenant Database Migrations**: Automated tenant database creation with proper schema
- ✅ **Request Validation**: Comprehensive validation for school setup and admin operations
- ✅ **Error Handling**: Robust error handling with rollback capabilities
- ✅ **API Testing**: Updated Postman collection with school setup endpoints
- ✅ **Database Seeding**: Comprehensive test data with superadmin, schools, students, teachers, and academic records
- ✅ **XAMPP Compatibility**: Full compatibility with XAMPP server environment  
- ✅ **Postman API Collection**: Complete API collection with fixed environment configurations
- ✅ **Multi-Tenant Enhancement**: Improved tenant isolation and school management
- ✅ **Authentication System**: Robust token-based authentication with proper validation
- ✅ **Subscription Management**: Enhanced subscription plans with expiration handling

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
- **Development Environment**: XAMPP compatible with proper URL routing
- **API Testing**: Postman collection with environment configurations
- **Database Seeding**: Comprehensive test data for all modules

---

## 2. Architecture Overview

### 2.1 Database Architecture
- **Central Database**: 
  - Stores superadmin data and authentication
  - Stores school admin data (Phase 1 - before tenant conversion)
  - Stores subscription plans and billing information  
  - Stores school inquiries and registration requests
  - Stores registered schools metadata and configurations
  - Stores custom form fields for school registration
  - Maintains admin-to-tenant conversion tracking and history
- **Tenant Databases**: 
  - Individual databases per school with complete data isolation
  - Contains converted admin data (Phase 2 - post tenant conversion)
  - Stores all school-specific users (teachers, students, parents, staff)
  - Contains all academic, financial, and operational school data
- **Soft Deletes**: All models use soft deletes - no hard deletion of data
- **Database Isolation**: Complete separation between central admin management and tenant school operations

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

### 3.1 User Role Hierarchy

#### 3.1.1 Superadmin (Owner - Central Database)
- **Role**: System owner who manages the entire SaaS platform
- **Single User**: One hardcoded/seeded superadmin account
- **Credentials**:
  - Email: `superadmin@example.com`
  - Password: Securely hashed
- **Guard**: `superadmin`
- **Access**: Central API only (`superadmin.localhost/api`)
- **Authentication**: Sanctum token-based (7-day expiration)
- **Responsibilities**:
  - Create and manage school admins in central database (Phase 1)
  - Monitor admin-to-tenant conversion process and track school creation
  - Create and manage customized subscription packages for schools
  - Configure system settings (payment, language, SMTP, about)
  - Monitor system health, performance, and tenant database management
  - System-level configuration management and multi-tenant coordination
  - Oversee admin lifecycle from central storage to tenant independence

#### 3.1.2 School Admin (Two-Phase Architecture)
- **Role**: School owner/administrator who will manage their educational institution
- **Phase 1 - Central Database Storage**: Initially stored alongside superadmin data in the main database
- **Phase 2 - Tenant Conversion**: Becomes a tenant with separate database after school creation

**Phase 1: Admin Creation & Storage**
- **Storage Location**: Central database (same as superadmin)
- **Data Structure**: Admin profile stored in central `admins` table
- **Authentication**: Uses central authentication system
- **Guard**: `admin` guard for central database access
- **Credentials Management**: Email/password authentication via central system
- **Profile Data**:
  - Personal information (name, email, phone)
  - Authentication credentials (password, tokens)
  - School assignment metadata
  - Creation timestamp and status
- **Access Level**: Limited admin panel access for profile management and school setup initiation

**Phase 2: Tenant Conversion Process**
1. **School Creation Trigger**: Admin initiates school setup process
2. **Tenant Database Creation**: System generates dedicated database for the school
3. **Data Migration**: Admin profile and credentials migrated to tenant database
4. **Permission Elevation**: Admin becomes school owner with full management capabilities
5. **Database Isolation**: Complete separation from central database
6. **Authentication Switch**: Uses tenant-specific authentication system

**Post-Conversion Admin Capabilities**:
- **Storage**: Dedicated tenant database for complete data isolation
- **Guard**: `tenant` guard for school-specific authentication
- **Access**: Full school management system with all features
- **Database Independence**: Separate database with own schema and data
- **School Ownership**: Complete control over school operations and users
- **Subscription Dependency**: All access tied to active school subscription status

**Admin-to-Tenant Workflow**:
1. Superadmin creates admin account in central database
2. Admin receives login credentials via email
3. Admin logs into central system using admin credentials
4. Admin completes school setup form (name, domain, details)
5. System creates tenant database with school schema
6. Admin profile and authentication data migrated to tenant database
7. Admin automatically logged into new tenant system as school owner
8. Central admin record marked as "converted" and archived
9. All future logins use tenant-specific authentication system

### 3.2 School User Hierarchy (Tenant Database)
This school management system is designed to manage seven kinds of users with different roles and permissions using Spatie Laravel Permissions package.

#### 3.2.0 School Administrators Hierarchy
The school can have multiple administrators with different permission levels, all managed through Spatie's permission system:

- **Super Administrator**: Has complete access to all school features and can manage other administrators. This role has permissions to manage all aspects of the school and can create and assign other admin roles.
- **Department Administrator**: Has administrative access limited to specific departments (e.g., Academic Department, Finance Department). Department admins can manage staff and resources within their department only.
- **Module Administrator**: Has administrative access limited to specific modules (e.g., Academic Admin, Finance Admin, Examination Admin). Module admins can only access and modify settings within their assigned modules.

All administrators share the same admin panel interface but with visibility and access controlled by their permission sets. This allows schools to create a hierarchical admin structure without requiring separate interfaces.

Each admin level is implemented with proper role-based access control through Spatie's Laravel Permission package. The system automatically filters available functionality based on the admin's permission level, showing only the features they have access to.

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

#### 5.1.5 Admin Management & Tenant Creation Workflow
**Purpose**: Comprehensive workflow for managing school administrators from creation to tenant conversion

**Admin Creation Workflow**:
1. **Admin Profile Creation**:
   - Superadmin logs into central system (`superadmin.localhost/api`)
   - Navigates to "Admin Management" section
   - Creates new admin profile with basic information:
     - Full name, email address, phone number
     - Temporary password (must be changed on first login)
     - Administrative role and permissions
     - Status (active/pending/suspended)
   - Admin profile stored in central database `admins` table
   - System sends welcome email with login credentials

2. **Admin Authentication Setup**:
   - Admin receives email notification with temporary credentials
   - Admin logs into central admin portal using provided credentials
   - Forced password change on first login for security
   - Profile completion with additional personal information
   - Access limited to profile management and school setup initiation

3. **School Setup Initiation**:
   - Admin navigates to "Create School" section in admin portal
   - Fills comprehensive school information form:
     - School name, logo, contact information
     - Domain name selection (unique subdomain requirement)
     - School address and administrative details
     - Subscription plan selection (if allowed)
   - System validates all information and domain availability
   - Admin submits school creation request

4. **Tenant Database Creation**:
   - System automatically creates dedicated tenant database
   - Database naming convention: `tenant_{domain_name}`
   - Complete school management schema deployment
   - Default roles and permissions structure setup
   - Initial configuration data seeding

5. **Admin-to-Tenant Migration**:
   - Admin profile and authentication data migrated to tenant database
   - Central admin record marked as "converted" with reference to tenant
   - Admin becomes school owner with full administrative privileges
   - System generates tenant-specific authentication tokens
   - Admin automatically redirected to tenant school portal

6. **Post-Conversion Management**:
   - Admin operates independently within tenant environment
   - Complete isolation from central database operations
   - Full access to school management features based on subscription
   - Ability to create and manage additional school users
   - Subscription-dependent feature access and limitations

**Admin Management Features for Superadmin**:
- View all created admins with status tracking
- Monitor admin-to-tenant conversion progress
- Manage admin profiles and permissions before conversion
- Reset admin passwords and manage authentication issues
- Track school creation requests and approval workflow
- Generate reports on admin creation and conversion rates
- Handle failed conversions and provide admin support

**Admin Status Tracking**:
- `pending` - Admin created but not yet logged in
- `active` - Admin logged in and managing profile
- `setting_up` - Admin in process of creating school
- `converted` - Successfully converted to tenant (archived in central DB)
- `suspended` - Admin access temporarily disabled
- `failed_conversion` - School creation process failed, requires support

### 5.2 Tenant Features (School APIs)

### 5.2 Tenant Features (School APIs)

#### 5.2.1 School Admin Features (Complete School Management System)
**Purpose**: Complete school management system with comprehensive administrative functionality

**Core Management Features**:
- **Admin Management**: Add/edit school admin accounts with hierarchical roles
- **Teacher Management**: Complete CRUD for teachers, departments, and assignments
- **Student Management**: Student enrollment, profiles, and academic records
- **Parent Management**: Parent profiles and student relationships
- **Accountant Management**: Financial staff and accounting permissions
- **Librarian Management**: Library staff and book management permissions

**Academic Management Features**:
- **Class & Section Management**: Create and manage academic classes and sections
- **Subject Management**: Subject creation, assignment to classes and teachers
- **Department Management**: Academic and administrative departments
- **Classroom Management**: Physical classroom allocation and management
- **Routine Management**: Class schedules and timetable creation
- **Syllabus Management**: Curriculum and syllabus tracking
- **Exam Management**: Examination categories, offline exams, and grading
- **Gradebook Management**: Grade recording and academic progress tracking
- **Attendance Management**: Daily attendance tracking for students
- **Promotion Management**: Student promotion between academic levels

**Financial Management Features**:
- **Fee Management**: Student fee structure and collection
- **Expense Management**: School expense tracking and categorization
- **Payment Management**: Payment processing and history
- **Invoice Management**: Mass and individual invoice generation

**Library Management Features**:
- **Book Management**: Library book catalog and inventory
- **Book Issue Management**: Book lending and return tracking
- **Library Reports**: Usage statistics and member records

**Communication & Collaboration Features**:
- **Noticeboard Management**: School announcements and notices
- **Event Management**: School events and calendar management
- **Message Threading**: Internal communication system
- **Chat System**: Real-time messaging capabilities
- **Feedback System**: Feedback collection and management

**Document & Profile Management Features**:
- **User Profiles**: Comprehensive user profile management
- **Document Management**: File upload and document storage per user
- **Photo Management**: Profile picture handling
- **Permission Management**: Role-based menu and feature permissions

**Reporting & Analytics Features**:
- **Student Reports**: Academic progress and attendance reports
- **Financial Reports**: Fee collection and expense reports
- **Library Reports**: Book usage and member statistics
- **Admit Card Generation**: Examination admit card creation

**Implemented Controllers**:
- **AuthController**: Handles login, logout, and user information retrieval
- **UserController**: Manages CRUD operations for users within the tenant
- **RoleController**: Handles role creation, assignment, and permission management
- **DashboardController**: Provides dashboard data and statistics
- **SettingController**: Manages school-specific settings
- **ProfileController**: Handles user profile management

**Access Control**:
- Plan-based feature restrictions
- Role-based permissions within school (Admin, Teacher, Student, Parent, Accountant, Librarian)
- Subscription status validation
- Expiration date enforcement
- Hierarchical admin structure with permission inheritance
- Menu-based permission system for granular access control

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

#### 5.2.4 Academic Management API Endpoints
**Purpose**: Complete academic management system with API endpoints for classes, subjects, exams, and grades

**Class & Section Management**:
- `GET /api/v1/classes` - List all classes with sections
- `POST /api/v1/classes` - Create new class with sections
- `GET /api/v1/classes/{id}` - Get class details
- `PUT /api/v1/classes/{id}` - Update class information
- `DELETE /api/v1/classes/{id}` - Soft delete class
- `GET /api/v1/classes/{id}/sections` - Get class sections
- `POST /api/v1/classes/{id}/sections` - Add section to class
- `PUT /api/v1/sections/{id}` - Update section details
- `DELETE /api/v1/sections/{id}` - Soft delete section

**Subject Management**:
- `GET /api/v1/subjects` - List all subjects
- `POST /api/v1/subjects` - Create new subject
- `GET /api/v1/subjects/{id}` - Get subject details
- `PUT /api/v1/subjects/{id}` - Update subject
- `DELETE /api/v1/subjects/{id}` - Soft delete subject
- `GET /api/v1/classes/{id}/subjects` - Get subjects for class

**Department Management**:
- `GET /api/v1/departments` - List all departments
- `POST /api/v1/departments` - Create new department
- `GET /api/v1/departments/{id}` - Get department details
- `PUT /api/v1/departments/{id}` - Update department
- `DELETE /api/v1/departments/{id}` - Soft delete department

**Classroom Management**:
- `GET /api/v1/classrooms` - List all classrooms
- `POST /api/v1/classrooms` - Create new classroom
- `GET /api/v1/classrooms/{id}` - Get classroom details
- `PUT /api/v1/classrooms/{id}` - Update classroom
- `DELETE /api/v1/classrooms/{id}` - Soft delete classroom

#### 5.2.5 Examination & Grading API Endpoints
**Purpose**: Complete examination management with categories, exams, grades, and gradebook

**Exam Category Management**:
- `GET /api/v1/exam-categories` - List exam categories
- `POST /api/v1/exam-categories` - Create exam category
- `GET /api/v1/exam-categories/{id}` - Get category details
- `PUT /api/v1/exam-categories/{id}` - Update category
- `DELETE /api/v1/exam-categories/{id}` - Soft delete category

**Offline Exam Management**:
- `GET /api/v1/exams` - List all exams
- `POST /api/v1/exams` - Create new exam
- `GET /api/v1/exams/{id}` - Get exam details
- `PUT /api/v1/exams/{id}` - Update exam
- `DELETE /api/v1/exams/{id}` - Soft delete exam
- `GET /api/v1/exams/{id}/export` - Export exam results

**Grade Management**:
- `GET /api/v1/grades` - List grade scale
- `POST /api/v1/grades` - Create grade scale
- `GET /api/v1/grades/{id}` - Get grade details
- `PUT /api/v1/grades/{id}` - Update grade
- `DELETE /api/v1/grades/{id}` - Soft delete grade

**Gradebook Management**:
- `GET /api/v1/gradebook` - Get gradebook data
- `POST /api/v1/gradebook/marks` - Add student marks
- `GET /api/v1/gradebook/student/{id}` - Get student marks
- `PUT /api/v1/gradebook/marks/{id}` - Update marks
- `GET /api/v1/gradebook/marks/export` - Export marks as PDF

#### 5.2.6 Financial Management API Endpoints
**Purpose**: Complete financial management system for fees, expenses, and payments

**Fee Management**:
- `GET /api/v1/fees` - List student fees
- `POST /api/v1/fees` - Create fee structure
- `GET /api/v1/fees/{id}` - Get fee details
- `PUT /api/v1/fees/{id}` - Update fee
- `DELETE /api/v1/fees/{id}` - Soft delete fee
- `GET /api/v1/fees/export` - Export fee reports
- `POST /api/v1/fees/invoice` - Generate mass invoices
- `GET /api/v1/fees/student/{id}` - Get student fee invoice

**Expense Management**:
- `GET /api/v1/expenses` - List all expenses
- `POST /api/v1/expenses` - Create new expense
- `GET /api/v1/expenses/{id}` - Get expense details
- `PUT /api/v1/expenses/{id}` - Update expense
- `DELETE /api/v1/expenses/{id}` - Soft delete expense

**Expense Category Management**:
- `GET /api/v1/expense-categories` - List expense categories
- `POST /api/v1/expense-categories` - Create category
- `GET /api/v1/expense-categories/{id}` - Get category details
- `PUT /api/v1/expense-categories/{id}` - Update category
- `DELETE /api/v1/expense-categories/{id}` - Soft delete category

#### 5.2.7 Library Management API Endpoints
**Purpose**: Complete library management system for books and issue tracking

**Book Management**:
- `GET /api/v1/books` - List all books
- `POST /api/v1/books` - Add new book
- `GET /api/v1/books/{id}` - Get book details
- `PUT /api/v1/books/{id}` - Update book
- `DELETE /api/v1/books/{id}` - Soft delete book

**Book Issue Management**:
- `GET /api/v1/book-issues` - List book issues
- `POST /api/v1/book-issues` - Issue book to student
- `GET /api/v1/book-issues/{id}` - Get issue details
- `PUT /api/v1/book-issues/{id}` - Update issue
- `PUT /api/v1/book-issues/{id}/return` - Return book
- `DELETE /api/v1/book-issues/{id}` - Soft delete issue

#### 5.2.8 Communication & Collaboration API Endpoints
**Purpose**: Internal communication system with notices, events, messages, and chat

**Noticeboard Management**:
- `GET /api/v1/notices` - List all notices
- `POST /api/v1/notices` - Create new notice
- `GET /api/v1/notices/{id}` - Get notice details
- `PUT /api/v1/notices/{id}` - Update notice
- `DELETE /api/v1/notices/{id}` - Soft delete notice

**Event Management**:
- `GET /api/v1/events` - List all events
- `POST /api/v1/events` - Create new event
- `GET /api/v1/events/{id}` - Get event details
- `PUT /api/v1/events/{id}` - Update event
- `DELETE /api/v1/events/{id}` - Soft delete event

**Message Threading**:
- `GET /api/v1/messages` - List message threads
- `POST /api/v1/messages` - Send new message
- `GET /api/v1/messages/{id}` - Get message thread
- `POST /api/v1/messages/{id}/reply` - Reply to message

**Chat System**:
- `GET /api/v1/chats/{userId}` - Get chat history
- `POST /api/v1/chats` - Send chat message
- `DELETE /api/v1/chats/{id}` - Clear chat history

**Feedback System**:
- `GET /api/v1/feedback` - List feedback
- `POST /api/v1/feedback` - Submit feedback
- `GET /api/v1/feedback/{id}` - Get feedback details
- `PUT /api/v1/feedback/{id}` - Update feedback
- `DELETE /api/v1/feedback/{id}` - Soft delete feedback

#### 5.2.9 Attendance & Routine Management API Endpoints
**Purpose**: Daily attendance tracking and class routine management

**Attendance Management**:
- `GET /api/v1/attendance` - Get attendance data
- `POST /api/v1/attendance` - Take attendance
- `GET /api/v1/attendance/student/{id}` - Get student attendance
- `GET /api/v1/attendance/export` - Export attendance CSV
- `GET /api/v1/attendance/class/{id}` - Get class attendance

**Routine Management**:
- `GET /api/v1/routines` - List class routines
- `POST /api/v1/routines` - Create routine
- `GET /api/v1/routines/{id}` - Get routine details
- `PUT /api/v1/routines/{id}` - Update routine
- `DELETE /api/v1/routines/{id}` - Soft delete routine

**Syllabus Management**:
- `GET /api/v1/syllabus` - List syllabus
- `POST /api/v1/syllabus` - Create syllabus
- `GET /api/v1/syllabus/{id}` - Get syllabus details
- `PUT /api/v1/syllabus/{id}` - Update syllabus
- `DELETE /api/v1/syllabus/{id}` - Soft delete syllabus

#### 5.2.10 Document & Profile Management API Endpoints
**Purpose**: User profile and document management system

**Profile Management**:
- `GET /api/v1/profile` - Get user profile
- `PUT /api/v1/profile` - Update profile
- `POST /api/v1/profile/photo` - Upload profile photo
- `DELETE /api/v1/profile/photo` - Remove profile photo

**Document Management**:
- `GET /api/v1/documents` - List user documents
- `POST /api/v1/documents` - Upload document
- `GET /api/v1/documents/{id}` - Download document
- `DELETE /api/v1/documents/{id}` - Remove document
- `GET /api/v1/users/{id}/documents` - Get user documents

#### 5.2.11 Student Promotion & Session Management API Endpoints
**Purpose**: Academic session and student promotion management

**Session Management**:
- `GET /api/v1/sessions` - List academic sessions
- `POST /api/v1/sessions` - Create session
- `GET /api/v1/sessions/{id}` - Get session details
- `PUT /api/v1/sessions/{id}` - Update session
- `PUT /api/v1/sessions/{id}/activate` - Set active session
- `DELETE /api/v1/sessions/{id}` - Soft delete session

**Student Promotion**:
- `GET /api/v1/promotions` - Get promotion candidates
- `POST /api/v1/promotions/promote` - Promote students
- `GET /api/v1/promotions/history` - Promotion history

#### 5.2.12 Admit Card & Reports API Endpoints
**Purpose**: Admit card generation and comprehensive reporting

**Admit Card Management**:
- `GET /api/v1/admit-cards` - List admit cards
- `POST /api/v1/admit-cards` - Create admit card template
- `GET /api/v1/admit-cards/{id}` - Get admit card
- `PUT /api/v1/admit-cards/{id}` - Update admit card
- `GET /api/v1/admit-cards/print` - Print admit cards
- `DELETE /api/v1/admit-cards/{id}` - Soft delete admit card

#### 5.2.13 Bulk Operations & CSV Import API Endpoints
**Purpose**: Bulk data operations and CSV import functionality

**Bulk Student Management**:
- `POST /api/v1/students/bulk-create` - Bulk create students
- `POST /api/v1/students/csv-import` - Import students from CSV
- `GET /api/v1/students/template` - Download CSV template

**Bulk User Management**:
- `POST /api/v1/users/bulk-create` - Bulk create users
- `POST /api/v1/users/csv-export` - Export users to CSV

#### 5.2.14 Permission Management API Endpoints
**Purpose**: Role-based access control and permission management

**Teacher Permission Management**:
- `GET /api/v1/teacher-permissions` - List teacher permissions
- `PUT /api/v1/teacher-permissions/{id}` - Update permissions
- `GET /api/v1/menu-permissions/{id}` - Get menu permissions
- `PUT /api/v1/menu-permissions/{id}` - Update menu permissions

#### 5.2.15 RBAC (Role-Based Access Control)
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
- `TenantAuthMiddleware`: Handles tenant-specific authentication and session management
- `TenantContext`: Set tenant database context
- `DomainResolver`: Resolve tenant from subdomain
- `RoleBasedAccessMiddleware`: Filter access based on user's role and permissions

### 7.4 Security Requirements
- CSRF protection on all forms
- Rate limiting on login attempts
- Secure password hashing (bcrypt)
- SQL injection prevention
- XSS protection
- Secure file upload handling
- SSL/TLS enforcement

### 7.5 Admin-Tenant Technical Implementation
**Purpose**: Technical specifications for implementing the admin-tenant two-phase architecture

#### 7.5.1 Database Schema Requirements

**Central Database Tables**:
```sql
-- Central database admin storage
CREATE TABLE admins (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    status ENUM('pending', 'active', 'setting_up', 'converted', 'suspended') DEFAULT 'pending',
    tenant_id BIGINT NULL, -- Reference to tenant after conversion
    converted_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Conversion tracking table
CREATE TABLE admin_tenant_conversions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    admin_id BIGINT NOT NULL,
    tenant_id BIGINT NOT NULL,
    old_admin_data JSON NOT NULL, -- Backup of original admin data
    conversion_status ENUM('initiated', 'completed', 'failed') DEFAULT 'initiated',
    error_message TEXT NULL,
    converted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);
```

**Tenant Database Schema**:
```sql
-- Tenant database contains converted admin as school owner
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'parent', 'accountant', 'librarian') DEFAULT 'admin',
    original_admin_id BIGINT NULL, -- Reference to original central admin
    is_school_owner BOOLEAN DEFAULT FALSE,
    -- ... other user fields
);
```

#### 7.5.2 Authentication Guards Configuration

**Central Authentication (config/auth.php)**:
```php
'guards' => [
    'superadmin' => [
        'driver' => 'sanctum',
        'provider' => 'superadmins',
    ],
    'admin' => [
        'driver' => 'sanctum',
        'provider' => 'admins',
    ],
],

'providers' => [
    'superadmins' => [
        'driver' => 'eloquent',
        'model' => App\Models\SuperAdmin::class,
    ],
    'admins' => [
        'driver' => 'eloquent',
        'model' => App\Models\Admin::class,
    ],
],
```

**Tenant Authentication**:
```php
'guards' => [
    'tenant' => [
        'driver' => 'sanctum',
        'provider' => 'tenant_users',
    ],
],

'providers' => [
    'tenant_users' => [
        'driver' => 'eloquent',
        'model' => App\Models\Tenant\User::class,
    ],
],
```

#### 7.5.3 Conversion Service Implementation

**AdminTenantConversionService Requirements**:
- Handle database connection switching during conversion
- Migrate admin authentication data securely
- Create tenant database with proper schema
- Backup original admin data before conversion
- Handle rollback in case of conversion failure
- Generate tenant-specific authentication tokens
- Update central database with conversion status

**Key Service Methods**:
```php
class AdminTenantConversionService
{
    public function convertAdminToTenant(Admin $admin, array $schoolData): Tenant
    public function createTenantDatabase(string $domain): bool
    public function migrateAdminData(Admin $admin, Tenant $tenant): User
    public function rollbackConversion(int $conversionId): bool
    public function validateConversionRequirements(Admin $admin): bool
}
```

#### 7.5.4 Middleware and Route Protection

**Central Admin Middleware**:
- `AdminAuth`: Verify admin authentication in central database
- `AdminNotConverted`: Ensure admin hasn't been converted to tenant yet
- `SchoolSetupAccess`: Control access to school setup features

**Tenant Middleware**:
- `TenantAuth`: Verify tenant user authentication
- `SchoolOwnerAccess`: Restrict certain features to converted admin (school owner)
- `SubscriptionValidation`: Ensure school subscription is active

#### 7.5.5 API Endpoint Structure

**Central Admin Endpoints**:
```
POST /api/admin/login - Admin authentication
GET /api/admin/profile - Admin profile management
POST /api/admin/create-school - Initiate school creation
GET /api/admin/conversion-status - Check conversion progress
```

**Tenant Endpoints** (after conversion):
```
{schooldomain}.localhost/api/auth/login - Tenant authentication
{schooldomain}.localhost/api/admin/dashboard - School owner dashboard
{schooldomain}.localhost/api/users - Manage school users
```

---

## 8. User Workflows

### 8.1 Admin Creation and Tenant Conversion Flow
**Complete workflow from admin creation to independent school management**

#### 8.1.1 Superadmin Creates Admin (Phase 1)
1. **Superadmin Authentication**:
   - Superadmin logs into central system (`superadmin.localhost/api`)
   - Accesses admin management dashboard

2. **Admin Profile Creation**:
   - Navigates to "Create Admin" section
   - Fills admin creation form:
     - Full name, email, phone number
     - Administrative role assignment
     - Initial status setting
   - System validates email uniqueness in central database
   - Admin profile stored in central `admins` table

3. **Credential Distribution**:
   - System generates temporary secure password
   - Welcome email sent to admin with login credentials
   - Admin status set to `pending`

#### 8.1.2 Admin Initial Setup (Phase 1)
1. **First-time Login**:
   - Admin receives email with credentials
   - Logs into central admin portal
   - Forced to change temporary password
   - Status updated to `active`

2. **Profile Completion**:
   - Admin completes personal information
   - Reviews available features and limitations
   - Accesses school setup initiation option

#### 8.1.3 School Creation Process (Phase 1 to Phase 2)
1. **School Setup Initiation**:
   - Admin clicks "Create My School" button
   - Status updated to `setting_up`
   - Access to comprehensive school setup form

2. **School Information Collection**:
   - School basic information (name, logo, contact)
   - Domain selection and validation
   - Administrative details and preferences
   - Subscription plan selection (if applicable)

3. **System Validation**:
   - Domain uniqueness verification
   - Form data validation and sanitization
   - Subscription plan compatibility check

4. **Tenant Database Creation**:
   - System creates dedicated tenant database
   - Database naming: `tenant_{domain_name}`
   - Complete school schema deployment
   - Default roles and permissions setup

5. **Admin Migration to Tenant**:
   - Admin data copied to tenant database
   - Admin becomes school owner with full privileges
   - Central admin record updated with conversion details
   - Status changed to `converted`

6. **Tenant System Activation**:
   - Admin automatically logged into tenant system
   - School subdomain becomes active
   - Full school management capabilities enabled
   - Welcome dashboard with setup guidance

#### 8.1.4 Post-Conversion School Management (Phase 2)
1. **Independent School Operations**:
   - Admin operates as school owner in tenant environment
   - Complete isolation from central database
   - Full access to school management features
   - Ability to create additional school users

2. **School User Management**:
   - Create and manage teachers, students, parents
   - Assign roles and permissions within school
   - Configure school-specific settings and preferences

### 8.2 Superadmin Daily Workflow
1. **Admin Management**:
   - Review pending admin creations
   - Monitor admin-to-tenant conversion progress
   - Handle failed conversions and provide support
   - Track admin status across all phases

2. **School Oversight**:
   - Monitor active school subscriptions
   - Review school creation requests
   - Handle school-related support issues
   - Generate admin and school management reports

3. **System Configuration**:
   - Manage subscription plans and pricing
   - Configure form fields for school registration
   - Update system settings and configurations
   - Monitor system health and performance

### 8.3 School User Workflow (Post-Conversion)
1. **Tenant-Specific Access**:
   - Access school subdomain (`{schoolname}.localhost`)
   - Login with tenant-specific credentials
   - Role-based dashboard access

2. **School-Specific Operations**:
   - Perform role-appropriate tasks
   - Access subscription-dependent features
   - Collaborate within isolated school environment

### 8.4 Admin Support and Troubleshooting Workflow
1. **Conversion Failure Recovery**:
   - Superadmin identifies failed conversions
   - Reviews error logs and failure reasons
   - Initiates manual conversion process or rollback
   - Communicates with affected admin

2. **Admin Assistance**:
   - Password reset for admins in Phase 1
   - Technical support during school setup
   - Guidance on feature limitations and usage
   - Escalation to development team if needed
5. Manage users within school (if admin)

---

## 7. Implementation Phases

### Phase 1: Core Infrastructure
- [x] Laravel 12 setup with tenancy package
- [x] Central database structure
- [x] Superadmin authentication
- [x] Basic tenant creation
- [x] Domain routing

### Phase 2: Superadmin Features
- [x] Custom form field management
- [x] School inquiry system
- [x] School approval workflow
- [x] Tenant database auto-creation

### Phase 3: Tenant Features
- [x] Tenant authentication system
- [x] Basic school dashboard
- [x] User management
- [x] RBAC implementation
- [x] Admin hierarchy with Spatie permissions
- [x] Tenant middleware for authentication
- [x] Role-based access control for different admin levels

### Phase 4: Advanced Features
- [x] Basic file upload handling for profiles
- [ ] Complete file upload system for school documents
- [ ] Email notifications for various events
- [x] Basic dashboard reporting
- [ ] Advanced analytics and reporting
- [x] Additional tenant-specific API endpoints

### Phase 5: Comprehensive Tenant API Implementation
**Academic Management Controllers**:
- [ ] ClassController - Class and section management
- [ ] SubjectController - Subject management with class assignments
- [ ] DepartmentController - Department organization
- [ ] ClassroomController - Physical classroom management

**Examination & Grading Controllers**:
- [ ] ExamCategoryController - Exam category management
- [ ] ExamController - Offline exam management
- [ ] GradeController - Grade scale management
- [ ] GradebookController - Student marks and gradebook

**Financial Management Controllers**:
- [ ] StudentFeeController - Fee structure and collection
- [ ] ExpenseController - Expense tracking
- [ ] ExpenseCategoryController - Expense categorization
- [ ] PaymentController - Payment processing and history

**Library Management Controllers**:
- [ ] BookController - Library book catalog
- [ ] BookIssueController - Book lending and returns

**Communication & Collaboration Controllers**:
- [ ] NoticeController - Noticeboard management
- [ ] EventController - School events and calendar
- [ ] MessageController - Internal messaging system
- [ ] ChatController - Real-time chat functionality
- [ ] FeedbackController - Feedback collection

**Attendance & Academic Controllers**:
- [ ] AttendanceController - Daily attendance tracking
- [ ] RoutineController - Class schedule management
- [ ] SyllabusController - Curriculum management

**Student & Enrollment Controllers**:
- [ ] StudentController - Student profile management
- [ ] EnrollmentController - Student enrollment tracking
- [ ] PromotionController - Student promotion management

**Document & Profile Controllers**:
- [ ] DocumentController - File upload and management
- [ ] TeacherPermissionController - Teacher-specific permissions
- [ ] MenuPermissionController - Menu-based access control

**Session & Administrative Controllers**:
- [ ] SessionController - Academic session management
- [ ] AdmitCardController - Admit card generation
- [ ] ReportController - Comprehensive reporting system

**Resource Transformers**:
- [ ] Create API resource transformers for all entities
- [ ] Implement consistent JSON response formatting
- [ ] Add proper error handling and validation

**Request Validation**:
- [ ] Create form request classes for all API endpoints
- [ ] Add comprehensive validation rules
- [ ] Implement custom validation messages

---

## 9. Admin-Tenant API Endpoints

### 9.1 Central Database Admin Management APIs
**Purpose**: API endpoints for managing admins in the central database before tenant conversion

#### 9.1.1 Superadmin Admin Management
**Base URL**: `superadmin.localhost/api/v1`

```http
# Admin CRUD Operations
GET /admins - List all created admins with status
POST /admins - Create new admin profile
GET /admins/{id} - Get specific admin details
PUT /admins/{id} - Update admin profile
DELETE /admins/{id} - Soft delete admin (before conversion)

# Admin Status Management
PUT /admins/{id}/status - Update admin status
GET /admins/pending - List pending admins
GET /admins/active - List active admins
GET /admins/setting-up - List admins in school setup process

# Conversion Tracking
GET /admins/{id}/conversion-status - Check conversion progress
POST /admins/{id}/force-conversion - Manual conversion trigger
GET /conversions - List all admin-tenant conversions
GET /conversions/failed - List failed conversions

# Admin Support
POST /admins/{id}/reset-password - Reset admin password
POST /admins/{id}/send-credentials - Resend login credentials
GET /admins/{id}/activity-log - Admin activity tracking
```

#### 9.1.2 Admin Authentication & Profile APIs
**Base URL**: `superadmin.localhost/api/v1/admin`

```http
# Authentication
POST /login - Admin login to central system
POST /logout - Admin logout
POST /refresh-token - Refresh authentication token
POST /change-password - Change password (first-time mandatory)

# Profile Management  
GET /profile - Get admin profile information
PUT /profile - Update admin profile
POST /profile/avatar - Upload profile picture

# School Setup Initiation
GET /school-setup - Get school setup form structure
POST /school-setup - Submit school creation request
GET /school-setup/status - Check school setup progress
```

### 9.2 Tenant Database APIs (Post-Conversion)
**Purpose**: API endpoints for converted admins managing their schools

#### 9.2.1 School Owner APIs
**Base URL**: `{schooldomain}.localhost/api/v1`

```http
# School Owner Dashboard
GET /admin/dashboard - School owner dashboard data
GET /admin/statistics - School statistics and analytics
GET /admin/recent-activities - Recent school activities

# School Configuration
GET /school/settings - Get school settings
PUT /school/settings - Update school settings
POST /school/logo - Update school logo
GET /school/subscription - Current subscription details

# User Management (School Owner Privileges)
GET /users - List all school users
POST /users - Create new school user
PUT /users/{id} - Update user details
DELETE /users/{id} - Soft delete user
POST /users/{id}/reset-password - Reset user password

# Role and Permission Management
GET /roles - List school roles
POST /roles - Create custom role
PUT /roles/{id} - Update role permissions
GET /permissions - List available permissions
POST /users/{id}/assign-role - Assign role to user
```

#### 9.2.2 Subscription and Access Control APIs
**Base URL**: `{schooldomain}.localhost/api/v1`

```http
# Subscription Validation
GET /subscription/status - Check subscription status
GET /subscription/features - Available features based on plan
GET /subscription/usage - Current usage statistics
GET /subscription/limits - Plan limits and restrictions

# Feature Access Control
GET /features/available - List available features
GET /menu/permissions - Menu access based on role and subscription
POST /access/validate - Validate access to specific feature
```

### 9.3 Cross-Database Reference APIs

#### 9.3.1 Conversion History APIs
**Base URL**: `superadmin.localhost/api/v1`

```http
# Conversion Tracking
GET /conversions/{id} - Get conversion details
POST /conversions/{id}/rollback - Rollback failed conversion
GET /conversions/statistics - Conversion statistics
GET /schools/{id}/original-admin - Get original admin reference

# Audit Trail
GET /admin-history/{adminId} - Complete admin lifecycle history
GET /tenant-origins/{tenantId} - Get tenant creation history
```

#### 9.3.2 Support and Troubleshooting APIs
**Base URL**: `superadmin.localhost/api/v1`

```http
# Admin Support
GET /admins/{id}/support-info - Get admin support information
POST /admins/{id}/support-note - Add support note
GET /admins/requiring-support - List admins needing assistance

# System Health
GET /conversions/health-check - Conversion system health
GET /tenant-databases/status - All tenant database status
POST /maintenance/cleanup-failed-conversions - Cleanup failed conversions
```

### 9.4 Middleware and Authentication Flow

#### 9.4.1 Authentication Middleware Chain
```http
# Central Admin Requests
Request → AdminAuth → AdminNotConverted → RouteHandler

# Tenant Requests  
Request → TenantAuth → SubscriptionValidation → RouteHandler

# School Owner Specific
Request → TenantAuth → SchoolOwnerAccess → RouteHandler
```

#### 9.4.2 Error Response Standards
```json
{
  "success": false,
  "error": {
    "code": "ADMIN_ALREADY_CONVERTED",
    "message": "This admin has already been converted to a tenant",
    "details": {
      "admin_id": 123,
      "tenant_id": 456,
      "converted_at": "2025-06-10T10:30:00Z"
    }
  }
}
```

---

## 10. Future Enhancements

### 10.1 Core System Enhancements
- Multi-language support with localization
- Advanced reporting and analytics dashboard
- Mobile application for administrators and users  
- Integration with third-party services (Payment gateways, LMS systems)
- Subscription and billing management automation
- Real-time notifications via WebSockets
- Advanced school modules (students, classes, etc.)

### 10.2 Admin-Tenant System Enhancements
- **Automated Admin Onboarding**: Self-service admin registration with approval workflow
- **Bulk Admin Creation**: CSV import for creating multiple admins simultaneously  
- **Admin Role Templates**: Predefined admin roles with specific permission sets
- **Conversion Analytics**: Detailed analytics on admin-to-tenant conversion rates and success metrics
- **Auto-Migration Tools**: Enhanced data migration with validation and rollback capabilities
- **Multi-Admin Support**: Allow multiple admins per school with hierarchical permissions
- **Admin Activity Monitoring**: Comprehensive tracking of admin actions across both phases

### 10.3 Advanced Tenant Management
- **Tenant Resource Monitoring**: Real-time monitoring of tenant database performance and usage
- **Cross-Tenant Analytics**: Aggregated reporting across all tenant schools for superadmin insights
- **Tenant Backup and Recovery**: Automated backup system for individual tenant databases
- **Schema Version Management**: Handle database schema updates across all tenant databases
- **Tenant Migration Tools**: Tools for moving tenants between different infrastructure

### 10.4 Security and Compliance Enhancements
- **Two-Factor Authentication**: Enhanced security for admin and superadmin accounts
- **Audit Trail System**: Complete audit logging for all admin and tenant activities
- **Data Privacy Controls**: GDPR compliance tools for data management and user rights
- **Advanced Access Control**: Time-based access restrictions and IP whitelisting
- **Security Monitoring**: Real-time security threat detection and prevention

### 10.5 User Experience Improvements
- **Progressive Web App**: PWA capabilities for mobile-friendly admin interfaces
- **User invitation system**: Email-based invitation system for administrators
- **Interactive Setup Wizard**: Guided school setup process with progress tracking
- **Dashboard Customization**: Personalized dashboards for different admin roles
- **Communication Platform**: Integrated messaging system between superadmin and admins

### 10.6 Integration and API Enhancements
- **Webhook System**: Event-driven webhooks for admin creation, conversion, and tenant activities
- **Third-party Integrations**: APIs for connecting with external school management tools
- **Data Export/Import**: Comprehensive data migration tools for existing school systems
- **API Rate Limiting**: Advanced rate limiting with tenant-specific quotas
- **GraphQL Support**: GraphQL endpoints alongside REST APIs for flexible data queries

---

## 10. Artisan Command Generation Guide

### 10.1 Tenant Controller Generation Commands
Generate all tenant controllers with resource methods:

```bash
# Academic Management Controllers
php artisan make:controller Interfaces/Api/V1/Tenant/ClassController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/SubjectController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/DepartmentController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/ClassroomController --api --resource

# Examination & Grading Controllers
php artisan make:controller Interfaces/Api/V1/Tenant/ExamCategoryController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/ExamController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/GradeController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/GradebookController --api --resource

# Financial Management Controllers
php artisan make:controller Interfaces/Api/V1/Tenant/StudentFeeController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/ExpenseController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/ExpenseCategoryController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/PaymentController --api --resource

# Library Management Controllers
php artisan make:controller Interfaces/Api/V1/Tenant/BookController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/BookIssueController --api --resource

# Communication & Collaboration Controllers
php artisan make:controller Interfaces/Api/V1/Tenant/NoticeController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/EventController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/MessageController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/ChatController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/FeedbackController --api --resource

# Attendance & Academic Controllers
php artisan make:controller Interfaces/Api/V1/Tenant/AttendanceController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/RoutineController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/SyllabusController --api --resource

# Student & Enrollment Controllers
php artisan make:controller Interfaces/Api/V1/Tenant/StudentController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/EnrollmentController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/PromotionController --api --resource

# Document & Profile Controllers
php artisan make:controller Interfaces/Api/V1/Tenant/DocumentController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/TeacherPermissionController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/MenuPermissionController --api --resource

# Session & Administrative Controllers
php artisan make:controller Interfaces/Api/V1/Tenant/SessionController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/AdmitCardController --api --resource
php artisan make:controller Interfaces/Api/V1/Tenant/ReportController --api --resource
```

### 10.2 Model Generation Commands
Generate tenant-specific models:

```bash
# Academic Models
php artisan make:model Models/Tenant/Classes -m
php artisan make:model Models/Tenant/Subject -m
php artisan make:model Models/Tenant/Department -m
php artisan make:model Models/Tenant/ClassRoom -m
php artisan make:model Models/Tenant/Section -m

# Examination Models
php artisan make:model Models/Tenant/ExamCategory -m
php artisan make:model Models/Tenant/Exam -m
php artisan make:model Models/Tenant/Grade -m
php artisan make:model Models/Tenant/Gradebook -m

# Student & Enrollment Models
php artisan make:model Models/Tenant/Student -m
php artisan make:model Models/Tenant/Parent -m
php artisan make:model Models/Tenant/Teacher -m
php artisan make:model Models/Tenant/Enrollment -m

# Financial Models
php artisan make:model Models/Tenant/StudentFeeManager -m
php artisan make:model Models/Tenant/Expense -m
php artisan make:model Models/Tenant/ExpenseCategory -m
php artisan make:model Models/Tenant/Payment -m

# Library Models
php artisan make:model Models/Tenant/Book -m
php artisan make:model Models/Tenant/BookIssue -m

# Communication Models
php artisan make:model Models/Tenant/Noticeboard -m
php artisan make:model Models/Tenant/FrontendEvent -m
php artisan make:model Models/Tenant/MessageThrade -m
php artisan make:model Models/Tenant/Chat -m
php artisan make:model Models/Tenant/Feedback -m

# Academic Support Models
php artisan make:model Models/Tenant/DailyAttendances -m
php artisan make:model Models/Tenant/Routine -m
php artisan make:model Models/Tenant/Syllabus -m
php artisan make:model Models/Tenant/Session -m
php artisan make:model Models/Tenant/AdmitCard -m
```

### 10.3 Request Validation Classes
Generate form request validation classes:

```bash
# Academic Request Classes
php artisan make:request Tenant/ClassRequest
php artisan make:request Tenant/SubjectRequest
php artisan make:request Tenant/DepartmentRequest
php artisan make:request Tenant/ClassroomRequest

# Examination Request Classes
php artisan make:request Tenant/ExamCategoryRequest
php artisan make:request Tenant/ExamRequest
php artisan make:request Tenant/GradeRequest
php artisan make:request Tenant/GradebookRequest

# Student Request Classes
php artisan make:request Tenant/StudentRequest
php artisan make:request Tenant/ParentRequest
php artisan make:request Tenant/TeacherRequest
php artisan make:request Tenant/EnrollmentRequest

# Financial Request Classes
php artisan make:request Tenant/StudentFeeRequest
php artisan make:request Tenant/ExpenseRequest
php artisan make:request Tenant/ExpenseCategoryRequest

# Library Request Classes
php artisan make:request Tenant/BookRequest
php artisan make:request Tenant/BookIssueRequest

# Communication Request Classes
php artisan make:request Tenant/NoticeRequest
php artisan make:request Tenant/EventRequest
php artisan make:request Tenant/MessageRequest
php artisan make:request Tenant/FeedbackRequest

# Academic Support Request Classes
php artisan make:request Tenant/AttendanceRequest
php artisan make:request Tenant/RoutineRequest
php artisan make:request Tenant/SyllabusRequest
```

### 10.4 API Resource Classes
Generate API resource transformers for JSON responses:

```bash
# Academic Resources
php artisan make:resource Tenant/ClassResource
php artisan make:resource Tenant/SubjectResource
php artisan make:resource Tenant/DepartmentResource
php artisan make:resource Tenant/ClassroomResource

# Examination Resources
php artisan make:resource Tenant/ExamCategoryResource
php artisan make:resource Tenant/ExamResource
php artisan make:resource Tenant/GradeResource
php artisan make:resource Tenant/GradebookResource

# Student Resources
php artisan make:resource Tenant/StudentResource
php artisan make:resource Tenant/ParentResource
php artisan make:resource Tenant/TeacherResource
php artisan make:resource Tenant/EnrollmentResource

# Financial Resources
php artisan make:resource Tenant/StudentFeeResource
php artisan make:resource Tenant/ExpenseResource
php artisan make:resource Tenant/ExpenseCategoryResource
php artisan make:resource Tenant/PaymentResource

# Library Resources
php artisan make:resource Tenant/BookResource
php artisan make:resource Tenant/BookIssueResource

# Communication Resources
php artisan make:resource Tenant/NoticeResource
php artisan make:resource Tenant/EventResource
php artisan make:resource Tenant/MessageResource
php artisan make:resource Tenant/FeedbackResource

# Academic Support Resources
php artisan make:resource Tenant/AttendanceResource
php artisan make:resource Tenant/RoutineResource
php artisan make:resource Tenant/SyllabusResource
php artisan make:resource Tenant/SessionResource
php artisan make:resource Tenant/AdmitCardResource
```

### 10.5 Middleware Generation Commands
Generate role-based access middleware:

```bash
# Role-based access middleware
php artisan make:middleware Tenant/AdminAccessMiddleware
php artisan make:middleware Tenant/TeacherAccessMiddleware
php artisan make:middleware Tenant/AccountantAccessMiddleware
php artisan make:middleware Tenant/LibrarianAccessMiddleware
php artisan make:middleware Tenant/StudentAccessMiddleware
php artisan make:middleware Tenant/ParentAccessMiddleware
```

### 10.6 Migration Generation Commands
Generate tenant-specific migrations:

```bash
# Academic migrations
php artisan make:migration tenant_create_classes_table
php artisan make:migration tenant_create_subjects_table
php artisan make:migration tenant_create_departments_table
php artisan make:migration tenant_create_classrooms_table
php artisan make:migration tenant_create_sections_table

# Examination migrations
php artisan make:migration tenant_create_exam_categories_table
php artisan make:migration tenant_create_exams_table
php artisan make:migration tenant_create_grades_table
php artisan make:migration tenant_create_gradebooks_table

# Student & Enrollment migrations
php artisan make:migration tenant_create_students_table
php artisan make:migration tenant_create_parents_table
php artisan make:migration tenant_create_teachers_table
php artisan make:migration tenant_create_enrollments_table

# Financial migrations
php artisan make:migration tenant_create_student_fee_managers_table
php artisan make:migration tenant_create_expenses_table
php artisan make:migration tenant_create_expense_categories_table
php artisan make:migration tenant_create_payments_table

# Library migrations
php artisan make:migration tenant_create_books_table
php artisan make:migration tenant_create_book_issues_table

# Communication migrations
php artisan make:migration tenant_create_noticeboards_table
php artisan make:migration tenant_create_frontend_events_table
php artisan make:migration tenant_create_message_thrades_table
php artisan make:migration tenant_create_chats_table
php artisan make:migration tenant_create_feedback_table

# Academic Support migrations
php artisan make:migration tenant_create_daily_attendances_table
php artisan make:migration tenant_create_routines_table
php artisan make:migration tenant_create_syllabuses_table
php artisan make:migration tenant_create_sessions_table
php artisan make:migration tenant_create_admit_cards_table
```

---

## 11. Implementation Status

### 11.1 Completed Features ✅
- [x] Laravel 12 setup with tenancy package
- [x] Central database structure for superadmin
- [x] Superadmin authentication system
- [x] **Admin Two-Phase System**: Complete admin-to-tenant conversion workflow
- [x] **Admin Models**: Admin and AdminTenantConversion models with relationships
- [x] **Admin Authentication**: Dedicated admin authentication system and middleware
- [x] **Admin Management APIs**: Complete CRUD operations for admin management
- [x] **School Setup System**: Full school creation and admin conversion process
- [x] **Email Notifications**: Welcome emails and school creation confirmations
- [x] **Tenant Database Creation**: Automated tenant database setup with migrations
- [x] **Request Validation**: Comprehensive validation for all admin operations
- [x] **Error Handling**: Robust error handling with rollback capabilities
- [x] **API Documentation**: Updated Postman collection with all admin endpoints
- [x] Custom form field management API
- [x] School inquiry system with dynamic forms
- [x] School approval workflow
- [x] Tenant database auto-creation
- [x] Tenant authentication system
- [x] Basic tenant dashboard and user management
- [x] RBAC implementation with Spatie permissions
- [x] Admin hierarchy with role-based access control
- [x] Tenant middleware for authentication
- [x] Basic API endpoints for user management
- [x] Profile management system

### 11.2 In Progress Features 🔄
- [x] **Admin System**: ✅ COMPLETED - Full admin lifecycle management
- [x] **Email Notifications**: ✅ COMPLETED - Welcome and school creation emails
- [ ] Comprehensive tenant API controllers implementation
- [ ] Complete model relationships and validation
- [ ] Advanced file upload and document management
- [ ] Comprehensive reporting and analytics

### 11.3 Pending Features ⏳
- [ ] Advanced Admin Features: Bulk admin creation, activity monitoring
- [ ] Enhanced Conversion System: Rollback capabilities, conversion analytics
- [ ] Mobile application support
- [ ] Real-time notifications via WebSockets
- [ ] Advanced reporting dashboard
- [ ] Third-party integrations
- [ ] Automated billing and subscription management
- [ ] Multi-language support

---

**Last Updated**: June 9, 2025  
**Version**: 1.1  
**Status**: Active Development - Tenant API Implementation Phase
php artisan make:resource Tenant/ParentResource
php artisan make:resource Tenant/TeacherResource
php artisan make:resource Tenant/EnrollmentResource

# Financial Resources
php artisan make:resource Tenant/StudentFeeResource
php artisan make:resource Tenant/ExpenseResource
php artisan make:resource Tenant/ExpenseCategoryResource

# Library Resources
php artisan make:resource Tenant/BookResource
php artisan make:resource Tenant/BookIssueResource

# Communication Resources
php artisan make:resource Tenant/NoticeResource
php artisan make:resource Tenant/EventResource
php artisan make:resource Tenant/MessageResource
php artisan make:resource Tenant/FeedbackResource

# Academic Support Resources
php artisan make:resource Tenant/AttendanceResource
php artisan make:resource Tenant/RoutineResource
php artisan make:resource Tenant/SyllabusResource
```

### 10.5 Migration Commands for Tenant Database
Generate tenant-specific migrations:

```bash
# Academic Migrations
php artisan make:migration create_classes_table --path=database/migrations/tenant
php artisan make:migration create_subjects_table --path=database/migrations/tenant
php artisan make:migration create_departments_table --path=database/migrations/tenant
php artisan make:migration create_classrooms_table --path=database/migrations/tenant
php artisan make:migration create_sections_table --path=database/migrations/tenant

# Examination Migrations
php artisan make:migration create_exam_categories_table --path=database/migrations/tenant
php artisan make:migration create_exams_table --path=database/migrations/tenant
php artisan make:migration create_grades_table --path=database/migrations/tenant
php artisan make:migration create_gradebooks_table --path=database/migrations/tenant

# Student & Staff Migrations
php artisan make:migration create_students_table --path=database/migrations/tenant
php artisan make:migration create_parents_table --path=database/migrations/tenant
php artisan make:migration create_teachers_table --path=database/migrations/tenant
php artisan make:migration create_enrollments_table --path=database/migrations/tenant

# Financial Migrations
php artisan make:migration create_student_fee_managers_table --path=database/migrations/tenant
php artisan make:migration create_expenses_table --path=database/migrations/tenant
php artisan make:migration create_expense_categories_table --path=database/migrations/tenant
php artisan make:migration create_payments_table --path=database/migrations/tenant

# Library Migrations
php artisan make:migration create_books_table --path=database/migrations/tenant
php artisan make:migration create_book_issues_table --path=database/migrations/tenant

# Communication Migrations
php artisan make:migration create_noticeboards_table --path=database/migrations/tenant
php artisan make:migration create_frontend_events_table --path=database/migrations/tenant
php artisan make:migration create_message_thrades_table --path=database/migrations/tenant
php artisan make:migration create_chats_table --path=database/migrations/tenant
php artisan make:migration create_feedback_table --path=database/migrations/tenant

# Academic Support Migrations
php artisan make:migration create_daily_attendances_table --path=database/migrations/tenant
php artisan make:migration create_routines_table --path=database/migrations/tenant
php artisan make:migration create_syllabuses_table --path=database/migrations/tenant
php artisan make:migration create_sessions_table --path=database/migrations/tenant
php artisan make:migration create_admit_cards_table --path=database/migrations/tenant
```

### 10.6 Middleware Generation
Generate role-based access middleware:

```bash
# Role-based access middleware
php artisan make:middleware Tenant/AdminOnlyMiddleware
php artisan make:middleware Tenant/TeacherAccessMiddleware
php artisan make:middleware Tenant/AccountantAccessMiddleware
php artisan make:middleware Tenant/LibrarianAccessMiddleware
php artisan make:middleware Tenant/RoleBasedAccessMiddleware
```

---

## 11. Success Criteria
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

#### 5.2.4 Dashboard Management
```
GET    /api/dashboard                  # Get dashboard statistics
GET    /api/dashboard/analytics        # Get detailed analytics
```

#### 5.2.5 Profile Management
```
GET    /api/profile                    # Get user profile
PUT    /api/profile                    # Update user profile
POST   /api/profile/change-password    # Change user password
POST   /api/profile/upload-avatar      # Upload profile picture
```

#### 5.2.6 Settings Management
```
GET    /api/settings                   # Get all settings
PUT    /api/settings                   # Update settings
GET    /api/settings/{key}             # Get specific setting
PUT    /api/settings/{key}             # Update specific setting
```

#### 5.2.7 Permission Management
```
GET    /api/permissions                # List all permissions
POST   /api/roles/{id}/permissions     # Assign permissions to role
DELETE /api/roles/{id}/permissions/{permission_id} # Remove permission from role
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
php artisan make:controller Api/Tenant/SettingController --resource --api
php artisan make:controller Api/Tenant/ProfileController
php artisan make:controller Api/Tenant/PermissionController
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
php artisan make:request Tenant/SettingRequest
php artisan make:request Tenant/ProfileUpdateRequest
php artisan make:request Tenant/PasswordChangeRequest
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
php artisan make:resource Tenant/DashboardResource
php artisan make:resource Tenant/SettingResource
php artisan make:resource Tenant/ProfileResource
php artisan make:resource Tenant/PermissionResource
```

### 6.5 Middleware Generation
```bash
php artisan make:middleware SuperadminAuth
php artisan make:middleware TenantAuth
php artisan make:middleware TenantAuthMiddleware
php artisan make:middleware SetTenantContext
php artisan make:middleware RoleBasedAccessMiddleware
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
php artisan make:migration create_settings_table
php artisan make:migration add_last_login_at_to_users_table
php artisan make:migration create_default_admin_user
```

---

*Last Updated: June 9, 2025*
*Version: 1.1*
