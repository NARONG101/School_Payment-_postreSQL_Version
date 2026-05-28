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
        })->with(['payments' => function ($query) {
            $query->latest('payment_date');
        }])->get();

        $overdueCount = 0;
        $closelyCount = 0;
        $upcomingCount = 0;
        $alertData = [];
        $recentPayments = collect();
        $totalCollectedThisMonth = 0;
        $paidThisMonthCount = 0;
        $totalPending = 0;
        $monthlyData = [];

        // Initialize monthly data array
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyData[] = [
                'month'  => $month->format('M Y'),
                'amount' => 0,
                'year'   => $month->year,
                'month_num' => $month->month,
            ];
        }

        foreach ($students as $student) {
            foreach ($student->payments as $payment) {
                $payment->setRelation('student', $student);
                $recentPayments->push($payment);

                // Monthly data
                if ($payment->payment_date && $payment->status === 'paid') {
                    $paymentYear = $payment->payment_date->year;
                    $paymentMonth = $payment->payment_date->month;
                    foreach ($monthlyData as &$md) {
                        if ($md['year'] === $paymentYear && $md['month_num'] === $paymentMonth) {
                            $md['amount'] += $payment->amount_paid;
                        }
                    }
                    unset($md);
                }

                // This month stats
                if ($payment->payment_date && $payment->payment_date->month === now()->month && $payment->payment_date->year === now()->year) {
                    if ($payment->status === 'paid') {
                        $totalCollectedThisMonth += $payment->amount_paid;
                        $paidThisMonthCount++;
                    }
                    if (in_array($payment->status, ['pending', 'partial'])) {
                        $totalPending += $payment->balance;
                    }
                }
            }

            // Alert logic (from student model attributes)
            if ($student->last_payment && $student->last_payment->payment_date) {
                $daysLeft = $student->days_until_next_payment;
                $data = [
                    'student' => $student,
                    'nextPaymentDate' => $student->next_payment_date,
                    'daysUntilNextPayment' => $daysLeft,
                    'alertLevel' => $student->alert_level,
                ];

                if ($daysLeft < 0) {
                    $overdueCount++;
                    $alertData[] = $data;
                } elseif ($daysLeft <= 7) {
                    $closelyCount++;
                    $alertData[] = $data;
                } else {
                    $upcomingCount++;
                }
            }
        }

        // Sort alerts: overdue first, then critical
        usort($alertData, function ($a, $b) {
            if ($a['alertLevel'] === $b['alertLevel']) {
                return $a['daysUntilNextPayment'] <=> $b['daysUntilNextPayment'];
            }
            return $a['alertLevel'] === 'overdue' ? -1 : 1;
        });

        // Sort recent payments and take first 5
        $recentPayments = $recentPayments->where('status', 'paid')
            ->sortByDesc('payment_date')
            ->sortByDesc('id')
            ->take(5);

        $stats = [
            'total_students'   => $students->count(),
            'total_collected'  => $totalCollectedThisMonth,
            'total_pending'    => $totalPending,
            'overdue_count'    => $overdueCount,
            'paid_this_month'  => $paidThisMonthCount,
            'due_this_week'    => $closelyCount,
            'upcoming_count'   => $upcomingCount,
        ];

        // Clean up monthly data (remove year and month_num)
        $monthlyData = array_map(function ($md) {
            return [
                'month' => $md['month'],
                'amount' => $md['amount'],
            ];
        }, $monthlyData);

        return view('dashboard', compact('stats', 'recentPayments', 'alertData', 'monthlyData'));
    }
}