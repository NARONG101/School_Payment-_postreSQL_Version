<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = (int) $request->get('year', now()->year);

        // ── Validate year range ───────────────────────────────
        $minYear = (int) (Payment::withTrashed()->min('payment_date') ?? now()->year);
        $minYear = min($minYear, now()->year);
        $maxYear = now()->year;
        $selectedYear = max($minYear, min($maxYear, $selectedYear));

        $availableYears = range($maxYear, $minYear);

        // ── Monthly breakdown for selected year ───────────────
        $monthlyRevenue = [];
        $yearTotal      = 0;
        $yearPaidCount  = 0;

        for ($m = 1; $m <= 12; $m++) {
            $payments = Payment::where('status', 'paid')
                ->whereYear('payment_date', $selectedYear)
                ->whereMonth('payment_date', $m)
                ->get();

            $amount = (float) $payments->sum('amount_paid');
            $count  = $payments->count();

            $monthlyRevenue[] = [
                'month'       => $m,
                'month_name'  => Carbon::create($selectedYear, $m, 1)->format('M'),
                'month_full'  => Carbon::create($selectedYear, $m, 1)->format('F Y'),
                'amount'      => $amount,
                'count'       => $count,
                'is_current'  => ($m === now()->month && $selectedYear === now()->year),
                'is_future'   => Carbon::create($selectedYear, $m, 1)->isFuture(),
            ];

            $yearTotal     += $amount;
            $yearPaidCount += $count;
        }

        // ── All-time totals ───────────────────────────────────
        $allTimeTotal = (float) Payment::where('status', 'paid')->sum('amount_paid');
        $allTimeCount = Payment::where('status', 'paid')->count();

        // ── This month stats ──────────────────────────────────
        $thisMonthAmount = (float) Payment::where('status', 'paid')
            ->whereYear('payment_date',  now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('amount_paid');

        $thisMonthCount = Payment::where('status', 'paid')
            ->whereYear('payment_date',  now()->year)
            ->whereMonth('payment_date', now()->month)
            ->count();

        // ── Expected monthly revenue (all active students) ────
        $expectedMonthly = (float) Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->sum('monthly_fee');

        $activeStudentCount = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->count();

        // ── Revenue by grade for selected year ────────────────
        $byGrade = Payment::where('status', 'paid')
            ->whereYear('payment_date', $selectedYear)
            ->with('student:id,year_level')
            ->get()
            ->groupBy(fn ($p) => $p->student?->year_level ?? 'Unknown')
            ->map(fn ($group) => [
                'amount' => (float) $group->sum('amount_paid'),
                'count'  => $group->count(),
            ])
            ->sortKeys();

        // ── Last 24 months trend ──────────────────────────────
        $trendData = [];
        for ($i = 23; $i >= 0; $i--) {
            $date   = now()->subMonths($i)->startOfMonth();
            $amount = (float) Payment::where('status', 'paid')
                ->whereYear('payment_date',  $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount_paid');

            $trendData[] = [
                'label'  => $date->format('M y'),
                'amount' => $amount,
                'year'   => $date->year,
                'month'  => $date->month,
            ];
        }

        return view('revenue.index', compact(
            'selectedYear',
            'availableYears',
            'monthlyRevenue',
            'yearTotal',
            'yearPaidCount',
            'allTimeTotal',
            'allTimeCount',
            'thisMonthAmount',
            'thisMonthCount',
            'expectedMonthly',
            'activeStudentCount',
            'byGrade',
            'trendData'
        ));
    }
}
