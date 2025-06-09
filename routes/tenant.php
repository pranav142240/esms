<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Interfaces\Api\V1\Tenant\AuthController;
use App\Interfaces\Api\V1\Tenant\UserController;
use App\Interfaces\Api\V1\Tenant\RoleController;
use App\Interfaces\Api\V1\Tenant\DashboardController;
use App\Interfaces\Api\V1\Tenant\SettingController;
use App\Interfaces\Api\V1\Tenant\ProfileController;
use App\Http\Controllers\Interfaces\Api\V1\Tenant\StudentController;
use App\Http\Controllers\Interfaces\Api\V1\Tenant\ClassController;
use App\Http\Controllers\Interfaces\Api\V1\Tenant\SubjectController;
use App\Http\Controllers\Interfaces\Api\V1\Tenant\TeacherController;
use App\Http\Controllers\Interfaces\Api\V1\Tenant\ExpenseController;
use App\Http\Controllers\Interfaces\Api\V1\Tenant\ExamController;
use App\Http\Controllers\Interfaces\Api\V1\Tenant\AttendanceController;
use App\Http\Controllers\Interfaces\Api\V1\Tenant\StudentFeeController;
use App\Http\Controllers\Interfaces\Api\V1\Tenant\BookController;
use App\Http\Controllers\Interfaces\Api\V1\Tenant\BookIssueController;
use App\Http\Controllers\Interfaces\Api\V1\Tenant\NoticeController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
});

// Tenant API Routes
Route::prefix('api/v1')->middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Public authentication routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    // Protected routes
    Route::middleware(['auth:sanctum', 'tenant.auth'])->group(function () {
        // Auth routes
        Route::prefix('auth')->group(function () {
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });
        
        // Profile management
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::post('/change-password', [ProfileController::class, 'changePassword']);
        });
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        
        // User management
        Route::apiResource('users', UserController::class);
        
        // Role management
        Route::apiResource('roles', RoleController::class);
        Route::get('/permissions', [RoleController::class, 'permissions']);
        
        // Settings management
        Route::get('/settings', [SettingController::class, 'index']);
        Route::post('/settings', [SettingController::class, 'update']);
        Route::get('/settings/groups', [SettingController::class, 'groups']);
        
        // Academic Management
        Route::apiResource('students', StudentController::class);
        Route::post('/students/bulk-create', [StudentController::class, 'bulkCreate']);
        Route::get('/students/{id}/id-card', [StudentController::class, 'generateIdCard']);
        
        Route::apiResource('classes', ClassController::class);
        Route::get('/classes/{class}/statistics', [ClassController::class, 'statistics']);
        Route::get('/classes/{class}/students', [ClassController::class, 'students']);
        Route::get('/classes/{class}/subjects', [ClassController::class, 'subjects']);
        
        Route::apiResource('subjects', SubjectController::class);
        Route::get('/subjects/class/{classId}', [SubjectController::class, 'byClass']);
        Route::get('/subjects/teacher/{teacherId}', [SubjectController::class, 'byTeacher']);
        Route::post('/subjects/{subject}/assign-teacher', [SubjectController::class, 'assignTeacher']);
        Route::get('/subjects/statistics', [SubjectController::class, 'statistics']);
        
        Route::apiResource('teachers', TeacherController::class);
        Route::post('/teachers/bulk-create', [TeacherController::class, 'bulkCreate']);
        Route::get('/teachers/department', [TeacherController::class, 'byDepartment']);
        Route::get('/teachers/available', [TeacherController::class, 'available']);
        Route::post('/teachers/{teacher}/assign-class', [TeacherController::class, 'assignToClass']);
        Route::get('/teachers/{teacher}/subjects', [TeacherController::class, 'subjects']);
        Route::get('/teachers/{teacher}/classes', [TeacherController::class, 'classes']);
        Route::get('/teachers/statistics', [TeacherController::class, 'statistics']);
        
        // Financial Management
        Route::apiResource('student-fees', StudentFeeController::class);
        Route::post('/student-fees/bulk-generate', [StudentFeeController::class, 'bulkGenerate']);
        Route::post('/student-fees/{id}/pay', [StudentFeeController::class, 'recordPayment']);
        Route::get('/student-fees/reports/export', [StudentFeeController::class, 'exportReport']);
        
        Route::apiResource('expenses', ExpenseController::class);
        Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve']);
        Route::post('/expenses/{expense}/reject', [ExpenseController::class, 'reject']);
        Route::post('/expenses/{expense}/mark-as-paid', [ExpenseController::class, 'markAsPaid']);
        Route::get('/expenses/statistics', [ExpenseController::class, 'statistics']);
        Route::get('/expenses/categories', [ExpenseController::class, 'categories']);
        
        // Examination Management
        Route::apiResource('exams', ExamController::class);
        Route::get('/exams/class/{classId}', [ExamController::class, 'byClass']);
        Route::get('/exams/subject/{subjectId}', [ExamController::class, 'bySubject']);
        Route::post('/exams/{exam}/start', [ExamController::class, 'start']);
        Route::post('/exams/{exam}/complete', [ExamController::class, 'complete']);
        Route::get('/exams/statistics', [ExamController::class, 'statistics']);
        
        // Attendance Management
        Route::apiResource('attendance', AttendanceController::class);
        Route::post('/attendance/bulk-mark', [AttendanceController::class, 'bulkMark']);
        Route::get('/attendance/class/{classId}', [AttendanceController::class, 'byClass']);
        Route::get('/attendance/student/{studentId}', [AttendanceController::class, 'byStudent']);
        Route::get('/attendance/reports', [AttendanceController::class, 'reports']);
        Route::get('/attendance/statistics', [AttendanceController::class, 'statistics']);
        
        // Library Management
        Route::apiResource('books', BookController::class);
        Route::post('/books/bulk-create', [BookController::class, 'bulkCreate']);
        Route::get('/books/available', [BookController::class, 'available']);
        Route::get('/books/categories', [BookController::class, 'categories']);
        Route::get('/books/statistics', [BookController::class, 'statistics']);
        
        Route::apiResource('book-issues', BookIssueController::class);
        Route::post('/book-issues/{bookIssue}/return', [BookIssueController::class, 'returnBook']);
        Route::post('/book-issues/{bookIssue}/renew', [BookIssueController::class, 'renewBook']);
        Route::post('/book-issues/{bookIssue}/mark-lost', [BookIssueController::class, 'markAsLost']);
        Route::get('/book-issues/overdue', [BookIssueController::class, 'overdue']);
        Route::get('/book-issues/student/{student}', [BookIssueController::class, 'byStudent']);
        Route::get('/book-issues/statistics', [BookIssueController::class, 'statistics']);
        
        // Notice Management
        Route::apiResource('notices', NoticeController::class);
        Route::get('/notices/published', [NoticeController::class, 'published']);
        Route::get('/notices/urgent', [NoticeController::class, 'urgent']);
        Route::post('/notices/{notice}/publish', [NoticeController::class, 'publish']);
        Route::post('/notices/{notice}/unpublish', [NoticeController::class, 'unpublish']);
        Route::post('/notices/{notice}/mark-urgent', [NoticeController::class, 'markUrgent']);
        Route::post('/notices/{notice}/remove-urgent', [NoticeController::class, 'removeUrgent']);
        Route::get('/notices/statistics', [NoticeController::class, 'statistics']);
    });
});
