<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;

$pass = 0; $fail = 0;
function check($label, $cond, $info = '') {
    global $pass, $fail;
    if ($cond) { echo "  ✓ {$label}\n"; $pass++; }
    else        { echo "  ✗ {$label}" . ($info ? " [{$info}]" : '') . "\n"; $fail++; }
}

echo "=== FULL SYSTEM VERIFICATION ===\n\n";

// ── 1. Show all students and their current payment status ─────
echo "1. Current payment state per student\n";
$students = Student::where(function($q){ $q->where('status','active')->orWhereNull('status'); })
    ->with(['payments' => fn($q) => $q->orderByDesc('next_payment_date')->orderByDesc('id')])
    ->get();

$now = Carbon::now();
foreach ($students as $s) {
    $last = $s->payments->first();
    if ($last && $last->next_payment_date) {
        $next = Carbon::parse($last->next_payment_date);
        $days = (int)$now->diffInDays($next, false);
        $status = $days < 0 ? "OVERDUE {$days}d" : ($days <= 7 ? "CLOSELY {$days}d" : "UPCOMING {$days}d");
    } else {
        $next   = null;
        $status = 'NO PAYMENT';
    }

    $nextFmt = $next ? $next->format('Y-m-d') : 'NULL';
    check(
        "{$s->full_name} | last_payment_id:" . ($last?->id ?? 'none') .
        " | next_payment_date:{$nextFmt} | {$status}",
        true
    );
}

// ── 2. Test the first-owed calculation for create form ────────
echo "\n2. data-first-owed for New Payment form\n";
foreach ($students as $s) {
    $lastPay = $s->payments->first();
    $payDay  = (int)($s->monthly_payment_day ?? 1);

    if ($lastPay && $lastPay->next_payment_date) {
        $firstOwed = Carbon::parse($lastPay->next_payment_date)->format('Y-m-d');
        $source    = 'next_payment_date';
    } elseif ($lastPay && $lastPay->payment_date) {
        $firstOwed = Student::nextPaymentDateFrom(Carbon::parse($lastPay->payment_date), $payDay)->format('Y-m-d');
        $source    = 'calculated';
    } else {
        $firstOwed = $s->enrollment_date->format('Y-m-d');
        $source    = 'enrollment_date';
    }

    check("{$s->full_name} first_owed={$firstOwed} ({$source})", strlen($firstOwed) === 10);
}

// ── 3. Simulate recording a new payment and verify state ──────
echo "\n3. Record a payment and verify it updates correctly\n";
$testStudent = $students->first();
$payDay      = (int)($testStudent->monthly_payment_day ?? 1);
$user        = User::first();

// Get current first-owed
$lastPay  = $testStudent->payments->first();
$firstOwed = $lastPay?->next_payment_date
    ? Carbon::parse($lastPay->next_payment_date)
    : Carbon::parse($testStudent->enrollment_date);

$coverMonth      = $firstOwed->copy()->startOfMonth();
$lastDayCovering = (int)$coverMonth->copy()->endOfMonth()->day;
$dueDate         = $coverMonth->copy()->day(min($payDay, $lastDayCovering));
$nextDate        = Student::nextPaymentDateFrom($coverMonth, $payDay);

echo "  Student: {$testStudent->full_name}\n";
echo "  Covering: {$coverMonth->format('F Y')}\n";
echo "  Due date: {$dueDate->format('Y-m-d')}\n";
echo "  Next date: {$nextDate->format('Y-m-d')}\n";

$p = Payment::create([
    'receipt_number'    => Payment::generateReceiptNumber(),
    'student_id'        => $testStudent->id,
    'amount_due'        => (float)$testStudent->monthly_fee,
    'admin_fee'         => 0,
    'amount_paid'       => (float)$testStudent->monthly_fee,
    'balance'           => 0,
    'payment_date'      => Carbon::today(),
    'due_date'          => $dueDate,
    'deadline_date'     => $dueDate,
    'next_payment_date' => $nextDate,
    'status'            => 'paid',
    'payment_method'    => 'cash',
    'time_type'         => $testStudent->time_type ?? 'mon-fri 1:00-3:00',
    'created_by'        => $user?->id,
    'notes'             => 'Payment for ' . $coverMonth->format('F Y'),
]);

check("Payment saved: #{$p->id}", $p->id > 0);
check("Status is paid", $p->status === 'paid');
check("next_payment_date = {$nextDate->format('Y-m-d')}", $p->next_payment_date->format('Y-m-d') === $nextDate->format('Y-m-d'));

// Re-load and verify the form would now show next month
$testStudent->load(['payments' => fn($q) => $q->orderByDesc('next_payment_date')->orderByDesc('id')]);
$newLast = $testStudent->payments->first();

check("New last payment is #{$p->id}", $newLast->id === $p->id);
check("New first_owed = {$nextDate->format('Y-m-d')}", $newLast->next_payment_date->format('Y-m-d') === $nextDate->format('Y-m-d'));

// Check still-overdue logic
$stillOverdue = $nextDate->startOfMonth()->lte(now()->startOfMonth());
echo "  Still overdue after paying {$coverMonth->format('M Y')}: " . ($stillOverdue ? 'YES' : 'NO') . "\n";

// Clean up
$p->forceDelete();
check("Cleanup successful", Payment::find($p->id) === null);

// ── 4. Duplicate prevention check ────────────────────────────
echo "\n4. Duplicate payment check\n";
$dupes = Payment::select('student_id', 'due_date')
    ->selectRaw('COUNT(*) as cnt')
    ->groupBy('student_id', 'due_date')
    ->havingRaw('COUNT(*) > 1')
    ->get();

check("No duplicate payments in DB", $dupes->count() === 0,
    $dupes->count() > 0 ? $dupes->count() . ' duplicates found' : '');

// ── Summary ───────────────────────────────────────────────────
echo "\n" . str_repeat('─', 45) . "\n";
echo "PASSED: {$pass}  FAILED: {$fail}\n";
if ($fail === 0) echo "✅ ALL TESTS PASSED\n";
else             echo "❌ {$fail} FAILED\n";
