<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class AutoPay100DiscountStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:auto-pay-100-discount {--student= : The ID of a specific student to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate payments for students with 100% discount';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-pay for 100% discount students...');

        $studentId = $this->option('student');

        // Get active students with 100% discount
        $query = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->where('discount', 100)
          ->with(['payments' => fn ($q) => $q->orderByDesc('next_payment_date')->orderByDesc('id')]);

        if ($studentId) {
            $students = $query->where('id', $studentId)->get();
        } else {
            $students = $query->get();
        }

        $count = 0;

        foreach ($students as $student) {
            $lastPayment = $student->payments->first();
            $now = Carbon::now();
            $paymentDay = (int) ($student->monthly_payment_day ?? 1);

            // Determine the first month to cover
            if ($lastPayment && $lastPayment->next_payment_date) {
                $firstMonth = $lastPayment->next_payment_date->startOfMonth();
            } elseif ($lastPayment && $lastPayment->payment_date) {
                $firstMonth = Student::nextPaymentDateFrom(
                    Carbon::parse($lastPayment->payment_date),
                    $paymentDay
                )->startOfMonth();
            } else {
                $firstMonth = $student->enrollment_date->startOfMonth();
            }

            // Generate payments from firstMonth up to current month
            $currentMonth = $now->startOfMonth();
            $month = $firstMonth->copy();

            while ($month->lte($currentMonth)) {
                // Check if payment already exists for this month
                $existingPayment = $student->payments->first(function ($payment) use ($month) {
                    return $payment->due_date && $payment->due_date->startOfMonth()->eq($month);
                });

                if (!$existingPayment) {
                    $this->createAutoPayment($student, $month, $paymentDay);
                    $count++;
                }

                $month->addMonth();
            }
        }

        $this->info("Auto-pay completed! Generated {$count} payments.");
        return 0;
    }

    /**
     * Create an auto-payment record for a student.
     */
    private function createAutoPayment(Student $student, Carbon $coveringMonth, int $paymentDay)
    {
        $paidDate = $coveringMonth->copy();
        $lastDayCovering = (int) $paidDate->copy()->endOfMonth()->day;
        $dueDate = $paidDate->copy()->day(min($paymentDay, $lastDayCovering));
        $nextDate = Student::nextPaymentDateFrom($paidDate, $paymentDay);

        $baseMonthlyFee = (float)$student->monthly_fee;
        $amountDue = $baseMonthlyFee * 1;
        $adminFee = 0;
        $discount = 100;
        $discountAmount = $amountDue * ($discount / 100);
        $totalDue = ($amountDue - $discountAmount) + $adminFee;
        $amountPaid = $totalDue;
        $balance = max(0, $totalDue - $amountPaid);

        Payment::create([
            'receipt_number' => Payment::generateReceiptNumber(),
            'student_id' => $student->id,
            'payment_date' => $paidDate,
            'due_date' => $dueDate,
            'deadline_date' => $dueDate,
            'next_payment_date' => $nextDate,
            'time_types' => $student->time_types,
            'months_covered' => 1,
            'amount_due' => $amountDue,
            'admin_fee' => $adminFee,
            'discount' => $discount,
            'amount_paid' => $amountPaid,
            'balance' => $balance,
            'status' => 'paid',
            'payment_method' => 'auto',
            'created_by' => null,
            'notes' => 'Auto-generated payment for ' . $coveringMonth->format('F Y') . ' (100% discount)',
        ]);

        $this->line("Created payment for {$student->full_name} - {$coveringMonth->format('F Y')}");
    }
}
