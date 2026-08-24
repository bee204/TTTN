<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccountController;

// Public routes for users (no authentication required)
Route::get('/', [WebController::class, 'dashboard'])->name('dashboard');
Route::get('/classes', [WebController::class, 'classes'])->name('classes');
Route::get('/classes/{id}', [WebController::class, 'classDetail'])->name('class.detail');
Route::get('/teachers', [WebController::class, 'teachers'])->name('teachers');
Route::get('/teachers/{id}', [WebController::class, 'teacherDetail'])->name('teacher.detail');
Route::get('/register', [WebController::class, 'register'])->name('register');
Route::post('/register', [WebController::class, 'registerSubmit'])->name('register.submit');
Route::get('/account/register', [WebController::class, 'registerAccount'])->name('account.register');
Route::post('/account/register', [WebController::class, 'registerAccountSubmit'])->name('account.register.submit');
Route::get('/account/login', [WebController::class, 'loginAccount'])->name('account.login');
Route::post('/account/login', [WebController::class, 'loginAccountSubmit'])->name('account.login.submit');
Route::get('/login', fn () => redirect()->route('account.login'))->name('login');
Route::get('/teacher/login', [WebController::class, 'teacherLogin'])->name('teacher.login');
Route::post('/teacher/login', [WebController::class, 'loginAccountSubmit'])->name('teacher.login.submit');
Route::post('/account/logout', [WebController::class, 'logoutAccount'])->middleware('auth')->name('account.logout');
Route::get('/account', [WebController::class, 'accountProfile'])->middleware('auth')->name('account.profile');
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('account.password.update');
});
// LEGACY / UNUSED BY CURRENT UI: không có GET /contact hoặc link điều hướng tới form cũ.
// Handler còn redirect tới route `contact` không tồn tại; xem docs/LEGACY_UNUSED.md.
Route::post('/contact', [WebController::class, 'contactSend'])->name('contact.send');
Route::get('/registered-classes', [WebController::class, 'registeredClasses'])->middleware('auth')->name('registered.classes');
Route::post('/registered-classes/{id}/cancel', [WebController::class, 'cancelRegistration'])->middleware('auth')->name('registered.class.cancel');
Route::get('/registered-classes/{id}', [WebController::class, 'registeredClassDetail'])->middleware('auth')->name('registered.class.detail');
Route::post('/registered-classes/{id}/review', [WebController::class, 'submitClassReview'])->middleware('auth')->name('registered.class.review');

Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/', [AdminController::class, 'teacherDashboard'])->name('dashboard');
    Route::get('/classes/{id}', [AdminController::class, 'classDetail'])->name('classes.detail');
    Route::get('/classes/{id}/attendance', [AdminController::class, 'attendancePage'])->name('classes.attendance');
    Route::get('/classes/{id}/reviews', [AdminController::class, 'reviewsPage'])->name('classes.reviews');
    Route::post('/registrations/{id}/attendance', [AdminController::class, 'storeAttendance'])->name('registrations.attendance.store');
});

// Admin routes with authentication
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin login routes (no auth required)
    Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.submit');
    
    // Redirect /admin to login if not authenticated
    Route::get('/', function () {
        return redirect()->route('admin.login');
    });
    
    // Protected admin routes
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/analytics', [AdminController::class, 'dashboardAnalytics'])->name('dashboard.analytics');
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        
        // Registration management
    Route::get('/registrations', [AdminController::class, 'registrations'])->name('registrations');
    Route::get('/registrations/create', [AdminController::class, 'showCreateRegistration'])->name('registrations.create');
    Route::post('/registrations/create', [AdminController::class, 'createRegistration']);
        Route::get('/registrations/{id}/edit', [AdminController::class, 'showEditRegistration'])->name('registrations.edit');
        Route::put('/registrations/{id}', [AdminController::class, 'updateRegistration'])->name('registrations.update');
        Route::delete('/registrations/{id}', [AdminController::class, 'destroyRegistration'])->name('registrations.destroy');
        Route::get('/registrations/{id}', [AdminController::class, 'registrationDetail'])->name('registrations.detail');
        Route::post('/registrations/{id}/approve', [AdminController::class, 'approveRegistration'])->name('registrations.approve');
        Route::post('/registrations/{id}/reject', [AdminController::class, 'rejectRegistration'])->name('registrations.reject');
        
        // Class management
        Route::get('/classes', [AdminController::class, 'classes'])->name('classes');
        Route::get('/classes/create', [AdminController::class, 'createClass'])->name('classes.create');
        Route::post('/classes', [AdminController::class, 'storeClass'])->name('classes.store');
        Route::get('/classes/{id}', [AdminController::class, 'classDetail'])->name('classes.detail');
        Route::get('/classes/{id}/attendance', [AdminController::class, 'attendancePage'])->name('classes.attendance');
        Route::post('/registrations/{id}/attendance', [AdminController::class, 'storeAttendance'])->name('registrations.attendance.store');
        Route::get('/classes/{id}/reviews', [AdminController::class, 'reviewsPage'])->name('classes.reviews');
        Route::get('/classes/{id}/edit', [AdminController::class, 'editClass'])->name('classes.edit');
        Route::put('/classes/{id}', [AdminController::class, 'updateClass'])->name('classes.update');
        Route::delete('/classes/{id}', [AdminController::class, 'deleteClass'])->name('classes.delete');
        
        // Customer management
        Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
        Route::get('/customers/create', [AdminController::class, 'createCustomer'])->name('customers.create');
        Route::post('/customers', [AdminController::class, 'storeCustomer'])->name('customers.store');
        Route::post('/customers/search', [AdminController::class, 'searchCustomer'])->name('customers.search');
        Route::get('/customers/{id}', [AdminController::class, 'customerDetail'])->name('customers.detail');
        Route::get('/customers/{id}/edit', [AdminController::class, 'editCustomer'])->name('customers.edit');
        Route::put('/customers/{id}', [AdminController::class, 'updateCustomer'])->name('customers.update');
        Route::delete('/customers/{id}', [AdminController::class, 'deleteCustomer'])->name('customers.delete');
        
        // Teacher management
        Route::get('/teachers', [AdminController::class, 'teachers'])->name('teachers');
        Route::get('/teachers/create', [AdminController::class, 'createTeacher'])->name('teachers.create');
        Route::post('/teachers', [AdminController::class, 'storeTeacher'])->name('teachers.store');
        Route::get('/teachers/{id}', [AdminController::class, 'teacherDetail'])->name('teachers.detail');
        Route::get('/teachers/{id}/edit', [AdminController::class, 'editTeacher'])->name('teachers.edit');
        Route::put('/teachers/{id}', [AdminController::class, 'updateTeacher'])->name('teachers.update');
        Route::delete('/teachers/{id}', [AdminController::class, 'deleteTeacher'])->name('teachers.delete');
    });
});
