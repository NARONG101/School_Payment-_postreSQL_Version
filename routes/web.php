<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\Auth\LoginController;

// ── Auth routes ───────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware(['throttle:login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Protected routes ──────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Revenue Report
    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');

    // Students
    Route::resource('students', StudentController::class);
    Route::get('students-export-csv', [StudentController::class, 'exportCsv'])->name('students.export.csv');

    // Payments — specific routes before resource to avoid conflicts
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('alerts',                                    [PaymentController::class, 'deadlineAlerts'])->name('alerts');
        Route::get('alerts/export-csv',                         [PaymentController::class, 'exportAlertsCsv'])->name('alerts.export.csv');
        Route::get('export-csv',                                [PaymentController::class, 'exportCsv'])->name('export.csv');
        Route::get('alerts/overdue',                            [PaymentController::class, 'alertsOverdue'])->name('alerts.overdue');
        Route::get('alerts/overdue/grade/{grade}',              [PaymentController::class, 'alertsOverdueGrade'])->name('alerts.overdue.grade');
        Route::get('alerts/closely',                            [PaymentController::class, 'alertsClosely'])->name('alerts.closely');
        Route::get('alerts/closely/grade/{grade}',              [PaymentController::class, 'alertsCloselyGrade'])->name('alerts.closely.grade');
        Route::get('alerts/upcoming',                           [PaymentController::class, 'alertsUpcoming'])->name('alerts.upcoming');
        Route::get('alerts/upcoming/grade/{grade}',             [PaymentController::class, 'alertsUpcomingGrade'])->name('alerts.upcoming.grade');
        Route::get('{payment}/receipt',                         [PaymentController::class, 'receipt'])->name('receipt');
        Route::get('{payment}/receipt/download',                [PaymentController::class, 'receiptDownload'])->name('receipt.download');
    });

    Route::resource('payments', PaymentController::class);

    // Payment Types
    Route::resource('payment-types', PaymentTypeController::class)->except(['show']);

    // Monthly History
    Route::prefix('monthly-history')->name('history.')->group(function () {
        Route::get('/',                         [\App\Http\Controllers\MonthlyHistoryController::class, 'index'])->name('monthly');
        Route::get('{yearMonth}',               [\App\Http\Controllers\MonthlyHistoryController::class, 'show'])->name('month');
        Route::get('{yearMonth}/export-csv',    [\App\Http\Controllers\MonthlyHistoryController::class, 'exportCsv'])->name('month.export.csv');
        Route::get('{yearMonth}/grade/{grade}', [\App\Http\Controllers\MonthlyHistoryController::class, 'students'])->name('students');
    });
});
