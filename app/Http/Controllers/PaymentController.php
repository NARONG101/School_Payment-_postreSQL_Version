<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $studentsQuery = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->with(['payments' => function ($query) {
            $query->latest('payment_date');
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('grade')) {
            $studentsQuery->where('year_level', $request->grade);
        }

        $students = $studentsQuery->orderBy('year_level')->orderBy('last_name')->get();

        $payments = collect();
        foreach ($students as $student) {
            foreach ($student->payments as $payment) {
                $payment->setRelation('student', $student);
                $payments->push($payment);
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'id');
        if ($sortBy === 'date') {
            $payments = $payments->sortByDesc('payment_date')->sortByDesc('id');
        } elseif ($sortBy === 'grade') {
            $payments = $payments->sortBy(function ($p) {
                return $p->student->year_level ?? 0;
            })->sortByDesc('id');
        } else {
            $payments = $payments->sortByDesc('id');
        }

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->orderBy('first_name')->get();
        
        $selectedStudentId = $request->query('student_id');
        return view('payments.create', compact('students', 'selectedStudentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'         => 'required|exists:students,id',
            'payment_date'       => 'required|date',
            'next_payment_date'  => 'nullable|date',
            'time_type'          => 'required',
            'payment_method'     => 'required|in:cash,bank_transfer',
            'photo'              => 'nullable|image|max:2048',
            'notes'              => 'nullable|max:500',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $validated['receipt_number'] = Payment::generateReceiptNumber();
        $validated['amount_due'] = $student->monthly_fee;
        $validated['admin_fee'] = 0;
        $validated['amount_paid'] = $student->monthly_fee;
        $validated['balance'] = 0;
        $validated['status'] = 'paid';
        $validated['created_by'] = Auth::id();
        $validated['due_date'] = $validated['payment_date'];
        $validated['deadline_date'] = $validated['payment_date'];

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('payments/photos', 'public');
        }

        $payment = Payment::create($validated);

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment recorded successfully! Receipt #' . $payment->receipt_number);
    }

    public function show(Payment $payment)
    {
        $payment->load(['student', 'paymentType', 'creator']);
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        $paymentTypes = PaymentType::where('is_active', true)->get();
        return view('payments.edit', compact('payment', 'students', 'paymentTypes'));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_date'     => 'nullable|date',
            'payment_method'   => 'required|in:cash,bank_transfer',
            'time_type'        => 'required',
            'photo'            => 'nullable|image|max:2048',
            'deadline_date'    => 'required|date',
            'next_payment_date' => 'nullable|date',
            'notes'            => 'nullable|max:500',
        ]);

        if ($request->hasFile('photo')) {
            if ($payment->photo) {
                Storage::disk('public')->delete($payment->photo);
            }
            $validated['photo'] = $request->file('photo')->store('payments/photos', 'public');
        }

        $payment->update($validated);

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment updated successfully!');
    }

    public function destroy(Payment $payment)
    {
        if ($payment->photo) {
            Storage::disk('public')->delete($payment->photo);
        }
        $payment->delete();
        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully!');
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['student', 'paymentType', 'creator']);
        $pdf = Pdf::loadView('receipts.payment', compact('payment'));
        $pdf->setPaper('a5', 'portrait');
        return $pdf->stream('receipt-' . $payment->receipt_number . '.pdf');
    }

    public function receiptDownload(Payment $payment)
    {
        $payment->load(['student', 'paymentType', 'creator']);
        $pdf = Pdf::loadView('receipts.payment', compact('payment'));
        $pdf->setPaper('a5', 'portrait');
        return $pdf->download('receipt-' . $payment->receipt_number . '.pdf');
    }

    public function deadlineAlerts()
    {
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();

        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->with(['payments' => function ($query) {
            $query->latest('payment_date');
        }])->get();

        $overdue = collect();
        $closely = collect();
        $upcoming = collect();
        $allStudentData = collect();

        foreach ($students as $student) {
            $lastPayment = $student->payments->first();
            $data = [
                'student' => $student,
                'lastPayment' => $lastPayment,
                'nextPaymentDate' => null,
                'daysUntilNextPayment' => null,
                'alertLevel' => 'normal',
            ];

            if ($lastPayment && $lastPayment->payment_date) {
                $lastPaymentDate = Carbon::parse($lastPayment->payment_date);
                $nextPaymentDate = $lastPaymentDate->copy()->addMonth();

                if ($student->monthly_payment_day) {
                    try {
                        $nextPaymentDate->day($student->monthly_payment_day);
                    } catch (\Exception $e) {
                    }
                }

                $data['nextPaymentDate'] = $nextPaymentDate;
                $data['daysUntilNextPayment'] = (int) $now->diffInDays($nextPaymentDate, false);

                if ($data['daysUntilNextPayment'] < 0) {
                    $data['alertLevel'] = 'overdue';
                    $overdue->push($data);
                } elseif ($data['daysUntilNextPayment'] <= 7) {
                    $data['alertLevel'] = 'closely';
                    $closely->push($data);
                } else {
                    $data['alertLevel'] = 'upcoming';
                    $upcoming->push($data);
                }
            } else {
                $data['alertLevel'] = 'overdue';
                $overdue->push($data);
            }

            $allStudentData->push($data);
        }

        return view('payments.alerts', compact('overdue', 'closely', 'upcoming', 'allStudentData'));
    }

    public function alertsOverdue()
    {
        $now = Carbon::now();
        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->with(['payments' => function ($query) {
            $query->latest('payment_date');
        }])->get();

        $grades = [];
        foreach ($students as $student) {
            $lastPayment = $student->payments->first();
            if ($lastPayment && $lastPayment->payment_date) {
                $nextPaymentDate = Carbon::parse($lastPayment->payment_date)->addMonth();
                if ($student->monthly_payment_day) {
                    try {
                        $nextPaymentDate->day($student->monthly_payment_day);
                    } catch (\Exception $e) {
                    }
                }
                $daysLeft = (int) $now->diffInDays($nextPaymentDate, false);
                if ($daysLeft < 0) {
                    $grades[] = $student->year_level;
                }
            } else {
                $grades[] = $student->year_level;
            }
        }

        $grades = collect($grades)->unique()->sort()->values();

        return view('payments.alerts-grades', ['grades' => $grades, 'title' => 'Overdue Payments', 'type' => 'overdue']);
    }

    public function alertsOverdueGrade(string|int $grade)
    {
        $now = Carbon::now();
        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->where('year_level', $grade)->with(['payments' => function ($query) {
            $query->latest('payment_date');
        }])->get();

        $studentData = [];
        foreach ($students as $student) {
            $lastPayment = $student->payments->first();
            $data = [
                'student' => $student,
                'lastPayment' => $lastPayment,
                'nextPaymentDate' => null,
                'daysUntilNextPayment' => null,
            ];

            if ($lastPayment && $lastPayment->payment_date) {
                $nextPaymentDate = Carbon::parse($lastPayment->payment_date)->addMonth();
                if ($student->monthly_payment_day) {
                    try {
                        $nextPaymentDate->day($student->monthly_payment_day);
                    } catch (\Exception $e) {
                    }
                }
                $data['nextPaymentDate'] = $nextPaymentDate;
                $data['daysUntilNextPayment'] = (int) $now->diffInDays($nextPaymentDate, false);
                if ($data['daysUntilNextPayment'] < 0) {
                    $studentData[] = $data;
                }
            } else {
                $studentData[] = $data;
            }
        }

        return view('payments.alerts-grades', ['studentData' => $studentData, 'title' => "Overdue - Grade $grade", 'type' => 'overdue']);
    }

    public function alertsClosely()
    {
        $now = Carbon::now();
        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->with(['payments' => function ($query) {
            $query->latest('payment_date');
        }])->get();

        $grades = [];
        foreach ($students as $student) {
            $lastPayment = $student->payments->first();
            if ($lastPayment && $lastPayment->payment_date) {
                $nextPaymentDate = Carbon::parse($lastPayment->payment_date)->addMonth();
                if ($student->monthly_payment_day) {
                    try {
                        $nextPaymentDate->day($student->monthly_payment_day);
                    } catch (\Exception $e) {
                    }
                }
                $daysLeft = (int) $now->diffInDays($nextPaymentDate, false);
                if ($daysLeft >= 0 && $daysLeft <= 7) {
                    $grades[] = $student->year_level;
                }
            }
        }

        $grades = collect($grades)->unique()->sort()->values();

        return view('payments.alerts-grades', ['grades' => $grades, 'title' => 'Closely Date Payments', 'type' => 'closely']);
    }

    public function alertsCloselyGrade(string|int $grade)
    {
        $now = Carbon::now();
        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->where('year_level', $grade)->with(['payments' => function ($query) {
            $query->latest('payment_date');
        }])->get();

        $studentData = [];
        foreach ($students as $student) {
            $lastPayment = $student->payments->first();
            $data = [
                'student' => $student,
                'lastPayment' => $lastPayment,
                'nextPaymentDate' => null,
                'daysUntilNextPayment' => null,
            ];

            if ($lastPayment && $lastPayment->payment_date) {
                $nextPaymentDate = Carbon::parse($lastPayment->payment_date)->addMonth();
                if ($student->monthly_payment_day) {
                    try {
                        $nextPaymentDate->day($student->monthly_payment_day);
                    } catch (\Exception $e) {
                    }
                }
                $data['nextPaymentDate'] = $nextPaymentDate;
                $data['daysUntilNextPayment'] = (int) $now->diffInDays($nextPaymentDate, false);
                if ($data['daysUntilNextPayment'] >= 0 && $data['daysUntilNextPayment'] <= 7) {
                    $studentData[] = $data;
                }
            }
        }

        return view('payments.alerts-grades', ['studentData' => $studentData, 'title' => "Closely Date - Grade $grade", 'type' => 'closely']);
    }

    public function alertsUpcoming()
    {
        $now = Carbon::now();
        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->with(['payments' => function ($query) {
            $query->latest('payment_date');
        }])->get();

        $grades = [];
        foreach ($students as $student) {
            $lastPayment = $student->payments->first();
            if ($lastPayment && $lastPayment->payment_date) {
                $nextPaymentDate = Carbon::parse($lastPayment->payment_date)->addMonth();
                if ($student->monthly_payment_day) {
                    try {
                        $nextPaymentDate->day($student->monthly_payment_day);
                    } catch (\Exception $e) {
                    }
                }
                $daysLeft = (int) $now->diffInDays($nextPaymentDate, false);
                if ($daysLeft > 7) {
                    $grades[] = $student->year_level;
                }
            }
        }

        $grades = collect($grades)->unique()->sort()->values();

        return view('payments.alerts-grades', ['grades' => $grades, 'title' => 'Upcoming Payments', 'type' => 'upcoming']);
    }

    public function alertsUpcomingGrade(string|int $grade)
    {
        $now = Carbon::now();
        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->where('year_level', $grade)->with(['payments' => function ($query) {
            $query->latest('payment_date');
        }])->get();

        $studentData = [];
        foreach ($students as $student) {
            $lastPayment = $student->payments->first();
            $data = [
                'student' => $student,
                'lastPayment' => $lastPayment,
                'nextPaymentDate' => null,
                'daysUntilNextPayment' => null,
            ];

            if ($lastPayment && $lastPayment->payment_date) {
                $nextPaymentDate = Carbon::parse($lastPayment->payment_date)->addMonth();
                if ($student->monthly_payment_day) {
                    try {
                        $nextPaymentDate->day($student->monthly_payment_day);
                    } catch (\Exception $e) {
                    }
                }
                $data['nextPaymentDate'] = $nextPaymentDate;
                $data['daysUntilNextPayment'] = (int) $now->diffInDays($nextPaymentDate, false);
                if ($data['daysUntilNextPayment'] > 7) {
                    $studentData[] = $data;
                }
            }
        }

        return view('payments.alerts-grades', ['studentData' => $studentData, 'title' => "Upcoming - Grade $grade", 'type' => 'upcoming']);
    }
}