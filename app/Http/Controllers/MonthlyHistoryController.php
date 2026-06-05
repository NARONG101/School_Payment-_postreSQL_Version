<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class MonthlyHistoryController extends Controller
{
    // ── Index: list all months that have payments ─────────────────────────────

    public function index()
    {
        $months = collect();

        Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->with(['payments' => fn ($q) => $q->select('id', 'student_id', 'due_date', 'payment_date', 'status')
            ->where('status', 'paid')
            ->whereNotNull('due_date')])->get()
        ->each(function ($student) use (&$months) {
            foreach ($student->payments as $payment) {
                // Use due_date (covering month) instead of payment_date
                $d     = $payment->due_date ?? $payment->payment_date;
                if (!$d) continue;
                $year  = $d->year;
                $month = $d->month;
                $key   = "{$year}-{$month}";
                if (! $months->has($key)) {
                    $date = Carbon::create($year, $month, 1);
                    $months->put($key, [
                        'year'  => $year,
                        'month' => $month,
                        'label' => $date->format('F Y'),
                        'slug'  => $date->format('Y-m'),
                        'count' => 0,
                    ]);
                }
                // Count payments per month
                $entry          = $months->get($key);
                $entry['count'] = $entry['count'] + 1;
                $months->put($key, $entry);
            }
        });

        // Sort newest first
        $months = $months->sortByDesc('year')->sortByDesc('month')->values();

        return view('history.monthly', compact('months'));
    }

    // ── Show: skip grade picker — show ALL paid students for this month directly ─

    public function show(string $yearMonth)
    {
        $date = $this->parseYearMonth($yearMonth);

        // Get all paid students for this month, sorted by payment id DESC (most recent first)
        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })
        ->with(['payments' => function ($q) use ($date) {
            $q->whereYear('due_date',  $date->year)
              ->whereMonth('due_date', $date->month)
              ->where('status', 'paid')
              ->orderByDesc('id');
        }])
        ->orderBy('year_level')
        ->orderBy('last_name')
        ->get()
        ->filter(fn ($s) => $s->payments->isNotEmpty());

        $gradeLevels = $students->pluck('year_level')->unique()->sort()->values();

        return view('history.month-students', compact('yearMonth', 'date', 'students', 'gradeLevels'));
    }

    // ── Students: list paid students for a month + grade ─────────────────────

    public function students(string $yearMonth, string|int $grade)
    {
        $date  = $this->parseYearMonth($yearMonth);
        $grade = (int) $grade;

        abort_if($grade < 1 || $grade > 12, 404);

        $students = Student::where('year_level', $grade)
            ->where(function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })
            ->with(['payments' => function ($q) use ($date) {
                $q->whereYear('payment_date',  $date->year)
                  ->whereMonth('payment_date', $date->month)
                  ->where('status', 'paid');
            }])
            ->orderBy('last_name')
            ->get()
            ->filter(fn ($s) => $s->payments->isNotEmpty());

        return view('history.students', compact('yearMonth', 'grade', 'date', 'students'));
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function parseYearMonth(string $yearMonth): Carbon
    {
        // Validate format YYYY-MM to prevent injection
        if (! preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            abort(404);
        }

        try {
            [$year, $month] = explode('-', $yearMonth);
            return Carbon::create((int) $year, (int) $month, 1);
        } catch (\Exception $e) {
            abort(404);
        }
    }
}
