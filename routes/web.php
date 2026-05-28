<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\Auth\LoginController;

// Auth
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::resource('students', StudentController::class);

    // Payments
    Route::get('payments/alerts', [PaymentController::class, 'deadlineAlerts'])->name('payments.alerts');
    Route::get('payments/alerts/overdue', [PaymentController::class, 'alertsOverdue'])->name('payments.alerts.overdue');
    Route::get('payments/alerts/overdue/grade/{grade}', [PaymentController::class, 'alertsOverdueGrade'])->name('payments.alerts.overdue.grade');
    Route::get('payments/alerts/closely', [PaymentController::class, 'alertsClosely'])->name('payments.alerts.closely');
    Route::get('payments/alerts/closely/grade/{grade}', [PaymentController::class, 'alertsCloselyGrade'])->name('payments.alerts.closely.grade');
    Route::get('payments/alerts/upcoming', [PaymentController::class, 'alertsUpcoming'])->name('payments.alerts.upcoming');
    Route::get('payments/alerts/upcoming/grade/{grade}', [PaymentController::class, 'alertsUpcomingGrade'])->name('payments.alerts.upcoming.grade');
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('payments/{payment}/receipt/download', [PaymentController::class, 'receiptDownload'])->name('payments.receipt.download');
    Route::resource('payments', PaymentController::class);

    // Payment Types
    Route::resource('payment-types', PaymentTypeController::class)->except(['show']);

    // Monthly History
    Route::get('/monthly-history', [\App\Http\Controllers\MonthlyHistoryController::class, 'index'])->name('history.monthly');
    Route::get('/monthly-history/{yearMonth}', [\App\Http\Controllers\MonthlyHistoryController::class, 'show'])->name('history.month');
    Route::get('/monthly-history/{yearMonth}/grade/{grade}', [\App\Http\Controllers\MonthlyHistoryController::class, 'students'])->name('history.students');
});