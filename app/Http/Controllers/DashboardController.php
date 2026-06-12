<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->with(['payments' => fn ($q) => $q->orderByDesc('next_payment_date')
                                             ->orderByDesc('id')])->get();

        // ── Monthly revenue chart (last 6 months) ────────────
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $monthlyData[] = [
                'month'     => $m->format('M Y'),
                'amount'    => 0,
                'year'      => $m->year,
                'month_num' => $m->month,
            ];
        }

        // ── Counters ──────────────────────────────────────────
        $overdueCount            = 0;
        $closelyCount            = 0;
        $upcomingCount           = 0;
        $alertData               = [];
        $recentPayments          = collect();
        $totalCollectedThisMonth = 0;
        $totalPending            = 0;

        foreach ($students as $student) {
            foreach ($student->payments as $payment) {
                $payment->setRelation('student', $student);
                $recentPayments->push($payment);

                // Monthly chart data
                if ($payment->payment_date && $payment->status === 'paid') {
                    $py = $payment->payment_date->year;
                    $pm = $payment->payment_date->month;
                    foreach ($monthlyData as &$md) {
                        if ($md['year'] === $py && $md['month_num'] === $pm) {
                            $md['amount'] += (float) $payment->amount_paid;
                        }
                    }
                    unset($md);
                }

                // This-month totals
                if ($payment->payment_date
                    && $payment->payment_date->month === $now->month
                    && $payment->payment_date->year  === $now->year) {
                    if ($payment->status === 'paid') {
                        $totalCollectedThisMonth += (float) $payment->amount_paid;
                    }
                    if (in_array($payment->status, ['pending', 'partial'])) {
                        $totalPending += (float) $payment->balance;
                    }
                }
            }

            // ── Alert logic using the correct anchored next-date ──
            $lastPayment = $student->payments->first();
            if (!$lastPayment || !$lastPayment->payment_date) {
                // No payment at all → calculate from enrollment date
                $paymentDay = (int) ($student->monthly_payment_day ?? $student->enrollment_date->day);
                $nextDate = Student::nextPaymentDateFrom($student->enrollment_date, $paymentDay);
                $daysLeft = (int) $now->diffInDays($nextDate, false);
                
                $overdueCount++;
                $alertData[] = [
                    'student'              => $student,
                    'nextPaymentDate'      => $nextDate,
                    'daysUntilNextPayment' => $daysLeft,
                    'alertLevel'           => 'overdue',
                ];
                continue;
            }

            // Use stored next_payment_date if available, otherwise recalculate
            if ($lastPayment->next_payment_date) {
                $nextDate = Carbon::parse($lastPayment->next_payment_date);
            } else {
                $paymentDay = (int) ($student->monthly_payment_day ?? $lastPayment->payment_date->day);
                $nextDate   = Student::nextPaymentDateFrom(
                    Carbon::parse($lastPayment->payment_date),
                    $paymentDay
                );
            }

            // If next payment date is before June 2026, treat as normal
            $juneStart = Carbon::parse('2026-06-01');
            if ($nextDate->lt($juneStart)) {
                $upcomingCount++;
                continue;
            }

            $daysLeft = (int) $now->diffInDays($nextDate, false);

            $alertLevel = match (true) {
                $daysLeft < 0  => 'overdue',
                $daysLeft <= 7 => 'closely',
                default        => 'upcoming',
            };

            $entry = [
                'student'              => $student,
                'nextPaymentDate'      => $nextDate,
                'daysUntilNextPayment' => $daysLeft,
                'alertLevel'           => $alertLevel,
            ];

            if ($daysLeft < 0) {
                $overdueCount++;
                $alertData[] = $entry;
            } elseif ($daysLeft <= 7) {
                $closelyCount++;
                $alertData[] = $entry;
            } else {
                $upcomingCount++;
            }
        }

        // Sort alerts: most overdue first, then soonest deadline
        usort($alertData, function ($a, $b) {
            if ($a['alertLevel'] !== $b['alertLevel']) {
                return $a['alertLevel'] === 'overdue' ? -1 : 1;
            }
            return ($a['daysUntilNextPayment'] ?? 0) <=> ($b['daysUntilNextPayment'] ?? 0);
        });

        // Recent paid payments (last 5)
        $recentPayments = $recentPayments
            ->where('status', 'paid')
            ->sortByDesc('payment_date')
            ->sortByDesc('id')
            ->take(5);

        $stats = [
            'total_students'  => $students->count(),
            'total_collected' => $totalCollectedThisMonth,
            'total_pending'   => $totalPending,
            'overdue_count'   => $overdueCount,
            'due_this_week'   => $closelyCount,
            'upcoming_count'  => $upcomingCount,
        ];

        // Strip internal keys from chart data
        $monthlyData = array_map(fn ($md) => [
            'month'  => $md['month'],
            'amount' => $md['amount'],
        ], $monthlyData);

        return view('dashboard', compact('stats', 'recentPayments', 'alertData', 'monthlyData'));
    }
}
