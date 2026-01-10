<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\StaffDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// PUBLIC ROUTES
// ==========================================

// Landing Page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Queue Display Screen (Public)
Route::get('/display', [HomeController::class, 'display'])->name('display');
Route::get('/display/data', [HomeController::class, 'displayData'])->name('display.data');

// Join Queue (Virtual Queue)
Route::get('/join-queue', [HomeController::class, 'joinQueue'])->name('queue.join');
Route::post('/join-queue', [PatientController::class, 'register'])->name('patient.register');

// Check Queue Status
Route::get('/check-status', [HomeController::class, 'checkStatus'])->name('queue.check');
Route::post('/check-status', [HomeController::class, 'lookupStatus'])->name('queue.lookup');
Route::get('/queue/{queue:queue_number}/status', [PatientController::class, 'ticket'])->name('queue.status');
Route::get('/queue/{queue:queue_number}/ticket', [PatientController::class, 'showTicket'])->name('queue.show-ticket');
Route::get('/queue/{queue}/cancel', [PatientController::class, 'cancelQueue'])->name('queue.cancel');
Route::post('/queue/{queue}/pay', [PatientController::class, 'processPayment'])->name('queue.pay');
Route::get('/queue/{queue}/receipt', [PatientController::class, 'printReceipt'])->name('queue.receipt');

// ==========================================
// AUTHENTICATION ROUTES
// ==========================================

Route::group([], function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Admin Login (outside guest middleware to avoid conflicts)
Route::get('/administrator/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/administrator/login', [AuthController::class, 'adminLogin'])->name('admin.login.submit');

// Redirect /admin/login to /administrator/login for convenience
Route::redirect('/admin/login', '/administrator/login');

// Patient Login (separate from staff)
Route::get('/patient/login', [AuthController::class, 'showPatientLogin'])->name('patient.login');
Route::post('/patient/login', [AuthController::class, 'patientLogin'])->name('patient.login.submit');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ==========================================
// PATIENT ROUTES
// ==========================================

Route::prefix('patient')->name('patient.')->middleware(['auth', 'role:patient'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\PatientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/appointments', [App\Http\Controllers\PatientDashboardController::class, 'appointments'])->name('appointments');
    Route::get('/profile', [App\Http\Controllers\PatientDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [App\Http\Controllers\PatientDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/queue-status', [App\Http\Controllers\PatientDashboardController::class, 'getQueueStatus'])->name('queue-status');
    Route::get('/check-queue', [App\Http\Controllers\PatientDashboardController::class, 'checkQueue'])->name('queue-check');
    Route::get('/join-queue', [App\Http\Controllers\PatientDashboardController::class, 'joinQueueView'])->name('queue.join');
    Route::post('/join-queue', [App\Http\Controllers\PatientDashboardController::class, 'joinQueueSubmit'])->name('queue.join.submit');
    Route::get('/live-display', [App\Http\Controllers\PatientDashboardController::class, 'liveDisplay'])->name('live-display');
    Route::get('/api/queue-data', [App\Http\Controllers\PatientDashboardController::class, 'getQueueData'])->name('api.queue-data');
    Route::get('/api/dashboard-stats', [App\Http\Controllers\PatientDashboardController::class, 'getDashboardStats'])->name('api.dashboard-stats');
    
    // Appointment Request
    Route::get('/request-appointment', [App\Http\Controllers\PatientDashboardController::class, 'requestAppointment'])->name('request-appointment');
    Route::post('/request-appointment', [App\Http\Controllers\PatientDashboardController::class, 'submitAppointmentRequest'])->name('request-appointment.submit');
    Route::get('/my-requests', [App\Http\Controllers\PatientDashboardController::class, 'myRequests'])->name('my-requests');
    Route::post('/cancel-request/{id}', [App\Http\Controllers\PatientDashboardController::class, 'cancelRequest'])->name('cancel-request');
});

// ==========================================
// ADMIN ROUTES
// ==========================================

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/service/{service}', [AdminDashboardController::class, 'serviceQueue'])->name('service.queue');
    Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('analytics');
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    Route::get('/patients', [AdminDashboardController::class, 'patients'])->name('patients');
    Route::get('/patients/add', [AdminDashboardController::class, 'addPatient'])->name('patients.add');
    Route::get('/notifications', [AdminDashboardController::class, 'notifications'])->name('notifications');
    Route::get('/patients/{patient}/edit', [AdminDashboardController::class, 'editPatient'])->name('patients.edit');
    Route::put('/patients/{patient}', [AdminDashboardController::class, 'updatePatient'])->name('patients.update');
    Route::delete('/patients/{patient}', [AdminDashboardController::class, 'deletePatient'])->name('patients.delete');
    Route::get('/payments', [AdminDashboardController::class, 'payments'])->name('payments');
    Route::get('/audit-logs', [AdminDashboardController::class, 'auditLogs'])->name('audit-logs');
    Route::get('/queue-settings', [AdminDashboardController::class, 'queueSettings'])->name('queue-settings');
    Route::post('/queue-settings', [AdminDashboardController::class, 'updateQueueSettings'])->name('queue-settings.update');
    Route::post('/settings/queue', [AdminDashboardController::class, 'saveQueueSettings'])->name('settings.queue');
    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');
    Route::get('/reports/generate/{type}', [AdminDashboardController::class, 'generateReport'])->name('reports.generate');
    Route::get('/transactions', [AdminDashboardController::class, 'transactions'])->name('transactions');
    Route::get('/receipts', [AdminDashboardController::class, 'receipts'])->name('receipts');

    // Queue Management
    Route::get('/queue/live', [AdminDashboardController::class, 'liveQueue'])->name('queue.live');
    Route::get('/queue/priority', [AdminDashboardController::class, 'priorityQueue'])->name('queue.priority');
    Route::get('/queue/virtual', [AdminDashboardController::class, 'virtualQueue'])->name('queue.virtual');
    Route::get('/api/live-queue', [AdminDashboardController::class, 'getLiveQueueData'])->name('api.live-queue');

    // Service Management
    Route::post('/services', [AdminDashboardController::class, 'storeService'])->name('services.store');
    Route::put('/services/{service}', [AdminDashboardController::class, 'updateService'])->name('services.update');
    Route::post('/services/{service}/toggle', [AdminDashboardController::class, 'toggleService'])->name('services.toggle');

    // Counter Management
    Route::post('/counters', [AdminDashboardController::class, 'storeCounter'])->name('counters.store');
    Route::put('/counters/{counter}', [AdminDashboardController::class, 'updateCounter'])->name('counters.update');
    Route::post('/counters/{counter}/status/{status}', [AdminDashboardController::class, 'toggleCounterStatus'])->name('counters.status');

    // Staff Management
    Route::resource('staff', \App\Http\Controllers\AdminStaffController::class);
});

// ==========================================
// STAFF ROUTES
// ==========================================

Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/queue/waiting', [StaffDashboardController::class, 'getWaitingList'])->name('queue.waiting');
    Route::get('/queue/completed', [StaffDashboardController::class, 'getCompleted'])->name('queue.completed');
    Route::get('/called', [StaffDashboardController::class, 'getCalledQueues'])->name('called');
    Route::get('/serving', [StaffDashboardController::class, 'getServingQueues'])->name('serving');
    Route::get('/queue/current', [StaffDashboardController::class, 'getCurrentServing'])->name('queue.current');
    Route::get('/queue/stats', [StaffDashboardController::class, 'quickStats'])->name('queue.stats');
    Route::get('/live-display', [StaffDashboardController::class, 'liveDisplayPage'])->name('live-display');
    Route::get('/api/live-queue', [StaffDashboardController::class, 'getLiveQueueData'])->name('api.live-queue');

    // Patient Management
    Route::post('/patients/register', [PatientController::class, 'walkInRegister'])->name('patients.register');
    Route::get('/patients/search', [PatientController::class, 'search'])->name('patients.search');
    Route::get('/patients', [StaffDashboardController::class, 'patients'])->name('patients');
    Route::get('/patients/add', [StaffDashboardController::class, 'addPatient'])->name('patients.add');

    // Queue Management
    Route::get('/queue/priority', [StaffDashboardController::class, 'priorityQueue'])->name('queue.priority');
    Route::post('/queue/service/{service}/call-next', [QueueController::class, 'callNext'])->name('queue.call-next');
    Route::post('/queue/call-next', [QueueController::class, 'callNextGeneral'])->name('queue.call-next-general');
    Route::post('/queue/{queue}/call', [QueueController::class, 'callSpecific'])->name('queue.call-specific');
    Route::post('/queue/{queue}/recall', [QueueController::class, 'recall'])->name('queue.recall');
    Route::post('/queue/{queue}/start', [QueueController::class, 'startServing'])->name('queue.start');
    Route::post('/queue/{queue}/complete', [QueueController::class, 'complete'])->name('queue.complete');
    Route::post('/queue/{queue}/skip', [QueueController::class, 'skip'])->name('queue.skip');
    Route::post('/queue/{queue}/cancel', [QueueController::class, 'cancel'])->name('queue.cancel');
    Route::put('/queue/{queue}', [QueueController::class, 'update'])->name('queue.update');

    // Service Queues
    Route::get('/service/{service}', [StaffDashboardController::class, 'serviceQueue'])->name('service.queue');

    // System
    Route::get('/notifications', [StaffDashboardController::class, 'notifications'])->name('notifications');
    Route::get('/notifications/fetch', [StaffDashboardController::class, 'fetchNotifications'])->name('notifications.fetch');
    Route::get('/reports', [StaffDashboardController::class, 'reports'])->name('reports');

    // Appointment Requests
    Route::get('/appointment-requests', [StaffDashboardController::class, 'appointmentRequests'])->name('appointment-requests');
    Route::post('/appointment-requests/{id}/approve', [StaffDashboardController::class, 'approveAppointment'])->name('appointment-requests.approve');
    Route::post('/appointment-requests/{id}/reject', [StaffDashboardController::class, 'rejectAppointment'])->name('appointment-requests.reject');

    // Settings
    Route::get('/settings/staff', [StaffDashboardController::class, 'settingsStaff'])->name('settings.staff');
    Route::get('/settings/queue', [StaffDashboardController::class, 'settingsQueue'])->name('settings.queue');
    Route::get('/settings/sms', [StaffDashboardController::class, 'settingsSms'])->name('settings.sms');
});

// ==========================================
// API-LIKE ROUTES (for AJAX calls)
// ==========================================

Route::prefix('api')->name('api.')->group(function () {
    // Public
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
    Route::get('/queue/{queue}/status', [PatientController::class, 'getStatus'])->name('queue.status');
    Route::get('/queue/current-serving', [QueueController::class, 'currentServing'])->name('queue.current-serving');

    // Protected
    Route::middleware(['auth', 'role:admin,staff'])->group(function () {
        Route::get('/queue/service/{service}/waiting', [QueueController::class, 'waitingList'])->name('queue.waiting');
        Route::get('/queue/service/{service}/stats', [QueueController::class, 'serviceStats'])->name('queue.service-stats');
        Route::get('/queue/stats', [QueueController::class, 'overallStats'])->name('queue.overall-stats');
    });
});
Route::get('/debug-rad', function() { return \App\Models\Queue::where('queue_number', 'RAD-002')->first() ?? 'Not Found'; });
