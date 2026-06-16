<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class MonthlyHistoryController extends Controller
{
    // ── Index: list all months that have payments + current month if unpaid students exist ─────────────────────────────

    public function index()
    {
        $months = collect();

        // Get active students with their paid payments
        $activeStudents = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->with(['payments' => fn ($q) => $q->select('id', 'student_id', 'due_date', 'payment_date', 'status')
            ->where('status', 'paid')
            ->whereNotNull('due_date')])->get();

        // Process paid payments to build months
        $activeStudents->each(function ($student) use (&$months) {
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

        // Check if there are any active students who haven't paid for current month
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();
        $hasUnpaidStudentsForCurrentMonth = false;

        foreach ($activeStudents as $student) {
            // Check if student has any paid payment for current month
            $hasPaidForCurrentMonth = $student->payments->contains(function ($payment) use ($currentMonthStart, $currentMonthEnd) {
                $paymentDate = $payment->due_date ?? $payment->payment_date;
                if (!$paymentDate) return false;
                return $paymentDate->between($currentMonthStart, $currentMonthEnd);
            });

            if (!$hasPaidForCurrentMonth) {
                $hasUnpaidStudentsForCurrentMonth = true;
                break;
            }
        }

        // Add current month only if there are unpaid students
        $currentKey = "{$now->year}-{$now->month}";
        if ($hasUnpaidStudentsForCurrentMonth && !$months->has($currentKey)) {
            $months->put($currentKey, [
                'year'  => $now->year,
                'month' => $now->month,
                'label' => $now->format('F Y'),
                'slug'  => $now->format('Y-m'),
                'count' => 0,
            ]);
        }

        // Sort newest first
        $months = $months->sortByDesc('year')->sortByDesc('month')->values();

        return view('history.monthly', compact('months'));
    }

    // ── Show: skip grade picker — show ALL students for current month, paid for others ─

    public function show(string $yearMonth)
    {
        $date = $this->parseYearMonth($yearMonth);
        $now = Carbon::now();
        $isCurrentMonth = ($date->year === $now->year && $date->month === $now->month);

        // Get students: if current month, show all active students; otherwise show only paid students
        $studentsQuery = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })
        ->with(['payments' => function ($q) use ($date) {
            $q->whereYear('due_date',  $date->year)
              ->whereMonth('due_date', $date->month)
              ->orderByDesc('id');
        }])
        ->orderBy('year_level')
        ->orderBy('last_name')
        ->get();

        if (!$isCurrentMonth) {
            $students = $studentsQuery->filter(fn ($s) => $s->payments->where('status', 'paid')->isNotEmpty());
        } else {
            $students = $studentsQuery;
        }

        $gradeLevels = $students->pluck('year_level')->unique()->sort()->values();

        return view('history.month-students', compact('yearMonth', 'date', 'students', 'gradeLevels', 'isCurrentMonth'));
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

    // ── CSV Export — Monthly Report ───────────────────────────────────────────

    public function exportCsv(string $yearMonth)
    {
        $date = $this->parseYearMonth($yearMonth);

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

        $filename = 'monthly_report_' . $yearMonth . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($students, $date, $yearMonth) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            // Title
            fputcsv($handle, [__('app.monthly_history') . ': ' . $date->format('F Y')]);
            fputcsv($handle, ['Generated: ' . now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                __('app.receipt'), __('app.student_id'),
                __('app.first_name') . ' ' . __('app.last_name'),
                __('app.grade'), __('app.weekday') . '/' . __('app.weekend'),
                __('app.subject'), __('app.amount'),
                'Payment Date', __('app.time_type'),
                __('app.payment_method'), __('app.next_payment'),
            ]);

            // Group by grade
            $byGrade = $students->groupBy('year_level')->sortKeys();
            $gradeTotal = [];
            foreach ($byGrade as $grade => $gradeStudents) {
                fputcsv($handle, []);
                fputcsv($handle, [__('app.grade') . ' ' . $grade]);
                $subtotal = 0;
                foreach ($gradeStudents as $s) {
                    foreach ($s->payments as $p) {
                        $ct = str_starts_with($p->time_type ?? '', 'sat-sun')
                            ? __('app.weekend')
                            : __('app.weekday');
                        fputcsv($handle, [
                            $p->receipt_number,
                            $s->student_id,
                            $s->full_name ?? '',
                            $grade,
                            $ct,
                            $s->subject ?? '',
                            $p->amount_paid,
                            $p->payment_date?->format('Y-m-d') ?? '',
                            $p->time_type ?? '',
                            $p->payment_method ?? '',
                            $p->next_payment_date?->format('Y-m-d') ?? '',
                        ]);
                        $subtotal += (float) $p->amount_paid;
                    }
                }
                fputcsv($handle, ['', '', 'Subtotal Grade ' . $grade, '', '', '', '$' . number_format($subtotal, 2)]);
                $gradeTotal[$grade] = $subtotal;
            }

            // Grand total
            fputcsv($handle, []);
            fputcsv($handle, ['', '', 'TOTAL', '', '', '$' . number_format(array_sum($gradeTotal), 2)]);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
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
