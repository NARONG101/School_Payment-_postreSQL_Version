<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /** Allowed time slots — must match PaymentController::TIME_SLOTS */
    private const TIME_SLOTS = [
        'mon-fri 7:00-9:00',
        'mon-fri 9:00-11:00',
        'mon-fri 1:00-3:00',
        'mon-fri 3:00-5:00',
        'mon-fri 5:30-7:30',
        'sat-sun 7:00-11:00',
        'sat-sun 1:00-5:00',
    ];

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // All students for the full table (with sort)
        $sortBy    = $request->get('sort', 'oldest');
        $classType = $request->get('class_type', ''); // 'weekday', 'weekend', or ''

        $allQuery = Student::withCount('payments');

        // Filter by class type (weekday = mon-fri, weekend = sat-sun)
        if ($classType === 'weekday') {
            $allQuery->where('time_type', 'like', 'mon-fri%');
        } elseif ($classType === 'weekend') {
            $allQuery->where('time_type', 'like', 'sat-sun%');
        }

        $allStudents = match ($sortBy) {
            'oldest'  => $allQuery->orderBy('id')->get(),
            'az'      => $allQuery->orderBy('last_name')->orderBy('first_name')->get(),
            'za'      => $allQuery->orderByDesc('last_name')->orderByDesc('first_name')->get(),
            'enroll'  => $allQuery->orderByDesc('enrollment_date')->get(),
            'grade'   => $allQuery->orderByDesc('year_level')->orderBy('last_name')->get(),
            default   => $allQuery->orderByDesc('id')->get(), // newest
        };

        // Grade cards — always grouped by grade
        $grades  = $allStudents->pluck('year_level')->unique()->sort()->values();
        $byGrade = $allStudents->groupBy('year_level');

        return view('students.index', compact('allStudents', 'grades', 'byGrade', 'sortBy', 'classType'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create()
    {
        return view('students.create');
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'          => 'nullable|unique:students,student_id|max:20|alpha_dash',
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:255',
            'come_from'           => 'nullable|string|max:255',
            'subject'             => 'required|string|max:100',
            'date_of_birth'       => 'nullable|date|before:today',
            'gender'              => 'nullable|in:male,female,other',
            'year_level'          => 'required|integer|min:1|max:12',
            'enrollment_date'     => 'required|date',
            'photo'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'monthly_payment_day' => 'required|integer|min:1|max:31',
            'monthly_fee'         => 'required|numeric|min:0|max:99999',
            'discount'            => 'nullable|numeric|min:0|max:100',
            'time_types'          => 'required|array|min:1',
            'time_types.*'        => 'in:' . implode(',', self::TIME_SLOTS),
            'payment_method'      => 'required|in:cash,bank_transfer',
            'payment_photo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'study_status'        => 'nullable|in:studying,stopped',
        ]);

        // Auto-generate student ID if blank
        if (empty($validated['student_id'])) {
            $validated['student_id'] = Student::generateStudentId();
        }

        // Store profile photo
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $validated['status'] = 'active';

        // Extract fields not in the students table
        $timeTypes      = $validated['time_types'];
        $paymentMethod = $validated['payment_method'];
        $paymentPhoto  = $request->file('payment_photo');
        $discount      = $validated['discount'] ?? 0;

        unset($validated['payment_method'], $validated['payment_photo'], $validated['time_type']);

        $student = Student::create($validated);

        // Auto-create first payment (enrollment date, with $20 admin fee — one time only)
        $enrollDate = Carbon::parse($validated['enrollment_date']);
        $paymentDay = (int) $validated['monthly_payment_day'];

        // Next payment = next occurrence of payment_day AFTER enrollment date
        $nextPaymentDate = Student::nextPaymentDateFrom($enrollDate, $paymentDay);

        $adminFee = 20; // $20 admin fee on enrollment only
        
        // Calculate total with discount
        $subtotal = $validated['monthly_fee'] + $adminFee;
        $discountAmount = $subtotal * ($discount / 100);
        $totalAmount = $subtotal - $discountAmount;

        $paymentData = [
            'receipt_number'    => Payment::generateReceiptNumber(),
            'student_id'        => $student->id,
            'amount_due'        => $validated['monthly_fee'],
            'admin_fee'         => $adminFee,
            'discount'          => $discount,
            'amount_paid'        => $totalAmount,
            'balance'           => 0,
            'payment_date'      => $enrollDate,
            'due_date'          => $enrollDate,
            'deadline_date'     => $enrollDate,
            'next_payment_date' => $nextPaymentDate,
            'status'            => 'paid',
            'payment_method'    => $paymentMethod,
            'time_types'         => $timeTypes,
            'created_by'        => Auth::id(),
        ];

        if ($paymentPhoto) {
            $paymentData['photo'] = $paymentPhoto->store('payments/photos', 'public');
        }

        Payment::create($paymentData);

        return redirect()->back()
            ->with('success', 'Student enrolled successfully! First payment created.');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Student $student)
    {
        $payments = $student->payments()->latest()->paginate(10);
        $stats = [
            'total_paid'    => $student->payments()->where('status', 'paid')->sum('amount_paid'),
            'total_due'     => $student->payments()->whereIn('status', ['pending', 'partial', 'overdue'])->sum('balance'),
            'overdue_count' => $student->payments()->where('status', 'overdue')->count(),
            'paid_count'    => $student->payments()->where('status', 'paid')->count(),
        ];
        return view('students.show', compact('student', 'payments', 'stats'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_id'          => 'required|string|max:20|unique:students,student_id,' . $student->id,
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:255',
            'come_from'           => 'nullable|string|max:255',
            'subject'             => 'required|string|max:100',
            'date_of_birth'       => 'nullable|date|before:today',
            'gender'              => 'nullable|in:male,female,other',
            'year_level'          => 'required|integer|min:1|max:12',
            'enrollment_date'     => 'required|date',
            'photo'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'monthly_payment_day' => 'required|integer|min:1|max:31',
            'monthly_fee'         => 'required|numeric|min:0|max:99999',
            'discount'            => 'nullable|numeric|min:0|max:100',
            'time_types'          => 'required|array|min:1',
            'time_types.*'        => 'in:' . implode(',', self::TIME_SLOTS),
            'study_status'        => 'nullable|in:studying,stopped',
        ]);

        if ($request->hasFile('photo')) {
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $validated['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $student->update($validated);

        return redirect()->route('students.show', $student)
            ->with('success', 'Student updated successfully!');
    }

    // ── CSV Export ────────────────────────────────────────────────────────────

    public function exportCsv(Request $request)
    {
        $sortBy    = $request->get('sort', 'oldest');
        $classType = $request->get('class_type', '');

        $allQuery = Student::withCount('payments');

        // Apply class_type filter
        if ($classType === 'weekday') {
            $allQuery->where('time_type', 'like', 'mon-fri%');
        } elseif ($classType === 'weekend') {
            $allQuery->where('time_type', 'like', 'sat-sun%');
        }

        $students = match ($sortBy) {
            'oldest' => $allQuery->orderBy('id')->get(),
            'az'     => $allQuery->orderBy('last_name')->orderBy('first_name')->get(),
            'za'     => $allQuery->orderByDesc('last_name')->orderByDesc('first_name')->get(),
            'enroll' => $allQuery->orderByDesc('enrollment_date')->get(),
            'grade'  => $allQuery->orderByDesc('year_level')->orderBy('last_name')->get(),
            default  => $allQuery->orderByDesc('id')->get(),
        };

        $classLabel = match($classType) {
            'weekday' => '_weekday',
            'weekend' => '_weekend',
            default   => '',
        };

        $filename = 'students' . $classLabel . '_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($students, $classType) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel Khmer support
            fwrite($handle, "\xEF\xBB\xBF");

            // Title row
            $classLabel = match($classType) {
                'weekday' => ' (' . __('app.weekday_class') . ')',
                'weekend' => ' (' . __('app.weekend_class') . ')',
                default   => '',
            };
            fputcsv($handle, [__('app.all_students') . $classLabel . ' — ' . now()->format('d/m/Y')]);
            fputcsv($handle, []); // blank row

            // Headers — added Class Type column after Grade
            fputcsv($handle, [
                __('app.student_id'), __('app.first_name'), __('app.last_name'), __('app.gender'),
                __('app.grade'), __('app.weekday') . '/' . __('app.weekend'),
                __('app.subject'), __('app.phone'), __('app.address'), __('app.come_from'),
                __('app.date_of_birth'), __('app.enrollment_date'), __('app.monthly_fee'),
                'Discount', __('app.payment_day'), __('app.time_type'), __('app.status'), 'Total Payments',
            ]);

            // Group by grade
            $byGrade = $students->groupBy('year_level')->sortKeys();
            foreach ($byGrade as $grade => $gradeStudents) {
                fputcsv($handle, []); // blank separator
                fputcsv($handle, [__('app.grade') . ' ' . $grade]); // grade heading
                foreach ($gradeStudents as $s) {
                    // Derive class type from time_type
                    $ct = str_starts_with($s->time_type ?? '', 'sat-sun')
                        ? __('app.weekend')
                        : __('app.weekday');
                    fputcsv($handle, [
                        $s->student_id,
                        $s->first_name,
                        $s->last_name,
                        $s->gender ? __('app.' . $s->gender) : '',
                        $s->year_level,
                        $ct,
                        $s->subject ?? '',
                        $s->phone ?? '',
                        $s->address ?? '',
                        $s->come_from ?? '',
                        $s->date_of_birth?->format('Y-m-d') ?? '',
                        $s->enrollment_date?->format('Y-m-d') ?? '',
                        $s->monthly_fee ?? '',
                        $s->discount ?? 0,
                        $s->monthly_payment_day ?? '',
                        $s->time_type ?? '',
                        $s->study_status ? __('app.' . $s->study_status) : __('app.studying'),
                        $s->payments_count,
                    ]);
                }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function destroy(Student $student)
    {
        // Delete associated photos from storage
        foreach ($student->payments as $payment) {
            if ($payment->photo) {
                Storage::disk('public')->delete($payment->photo);
            }
        }
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->payments()->delete();
        $student->delete();

        return redirect()->back()
            ->with('success', 'Student removed successfully!');
    }
}
