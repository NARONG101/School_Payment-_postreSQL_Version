<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonthlyHistoryController extends Controller
{
    public function index()
    {
        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->with(['payments' => function ($query) {
            $query->latest('payment_date');
        }])->get();

        $months = collect();
        foreach ($students as $student) {
            foreach ($student->payments as $payment) {
                if ($payment->payment_date) {
                    $year = $payment->payment_date->year;
                    $month = $payment->payment_date->month;
                    $key = "{$year}-{$month}";
                    if (!$months->has($key)) {
                        $date = Carbon::create($year, $month, 1);
                        $months->put($key, [
                            'year' => $year,
                            'month' => $month,
                            'label' => $date->format('F Y'),
                            'slug' => $date->format('Y-m')
                        ]);
                    }
                }
            }
        }

        $months = $months->sortByDesc('year')->sortByDesc('month')->values();

        return view('history.monthly', compact('months'));
    }

    public function show(string $yearMonth)
    {
        [$year, $month] = explode('-', $yearMonth);
        $date = Carbon::create($year, $month, 1);

        $gradeLevels = Student::select('year_level')
            ->distinct()
            ->orderBy('year_level')
            ->pluck('year_level');

        return view('history.grades', compact('yearMonth', 'date', 'gradeLevels'));
    }

    public function students(string $yearMonth, string|int $grade)
    {
        [$year, $month] = explode('-', $yearMonth);
        $date = Carbon::create($year, $month, 1);

        $students = Student::where('year_level', $grade)
            ->where(function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })
            ->with(['payments' => function ($q) use ($year, $month) {
                $q->whereYear('payment_date', $year)
                  ->whereMonth('payment_date', $month)
                  ->where('status', 'paid');
            }])
            ->orderBy('last_name')
            ->get()
            ->filter(function ($student) {
                return $student->payments->count() > 0;
            });

        return view('history.students', compact('yearMonth', 'grade', 'date', 'students'));
    }
}
