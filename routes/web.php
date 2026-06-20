<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\Auth\LoginController;

// ── Auth routes ────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware(['throttle:login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Protected routes ───────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard (accessible by both admin and receipts)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('role:admin,receipts');

    // Revenue Report (admin only)
    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index')->middleware('role:admin');

    // Students
    Route::resource('students', StudentController::class)->middleware('role:admin,receipts');
    Route::get('students-export-csv', [StudentController::class, 'exportCsv'])->name('students.export.csv')->middleware('role:admin,receipts');

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('alerts', [PaymentController::class, 'deadlineAlerts'])->name('alerts')->middleware('role:admin,receipts');
        Route::get('alerts/export-csv', [PaymentController::class, 'exportAlertsCsv'])->name('alerts.export.csv')->middleware('role:admin,receipts');
        Route::get('export-csv', [PaymentController::class, 'exportCsv'])->name('export.csv')->middleware('role:admin,receipts');
        Route::get('alerts/overdue', [PaymentController::class, 'alertsOverdue'])->name('alerts.overdue')->middleware('role:admin,receipts');
        Route::get('alerts/overdue/grade/{grade}', [PaymentController::class, 'alertsOverdueGrade'])->name('alerts.overdue.grade')->middleware('role:admin,receipts');
        Route::get('alerts/closely', [PaymentController::class, 'alertsClosely'])->name('alerts.closely')->middleware('role:admin,receipts');
        Route::get('alerts/closely/grade/{grade}', [PaymentController::class, 'alertsCloselyGrade'])->name('alerts.closely.grade')->middleware('role:admin,receipts');
        Route::get('alerts/upcoming', [PaymentController::class, 'alertsUpcoming'])->name('alerts.upcoming')->middleware('role:admin,receipts');
        Route::get('alerts/upcoming/grade/{grade}', [PaymentController::class, 'alertsUpcomingGrade'])->name('alerts.upcoming.grade')->middleware('role:admin,receipts');
        Route::get('{payment}/receipt', [PaymentController::class, 'receipt'])->name('receipt')->middleware('role:admin,receipts');
        Route::get('{payment}/receipt/download', [PaymentController::class, 'receiptDownload'])->name('receipt.download')->middleware('role:admin,receipts');
    });

    Route::resource('payments', PaymentController::class)->middleware('role:admin,receipts');

    // Payment Types (admin only)
    Route::resource('payment-types', PaymentTypeController::class)->except(['show'])->middleware('role:admin');

    // Monthly History (accessible by both admin and receipts)
    Route::prefix('monthly-history')->name('history.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MonthlyHistoryController::class, 'index'])->name('monthly')->middleware('role:admin,receipts');
        Route::get('{yearMonth}', [\App\Http\Controllers\MonthlyHistoryController::class, 'show'])->name('month')->middleware('role:admin,receipts');
        Route::get('{yearMonth}/export-csv', [\App\Http\Controllers\MonthlyHistoryController::class, 'exportCsv'])->name('month.export.csv')->middleware('role:admin,receipts');
        Route::get('{yearMonth}/grade/{grade}', [\App\Http\Controllers\MonthlyHistoryController::class, 'students'])->name('students')->middleware('role:admin,receipts');
    });
});
