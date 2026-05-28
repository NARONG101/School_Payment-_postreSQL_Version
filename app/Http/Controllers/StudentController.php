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
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('grade')) {
            $query->where('year_level', $request->grade);
        }

        $students = $query->withCount('payments')->orderBy('year_level')->orderBy('last_name')->get()->groupBy('year_level');
        $grades = Student::distinct()->orderBy('year_level')->pluck('year_level');

        return view('students.index', compact('students', 'grades'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'           => 'nullable|unique:students,student_id|max:20',
            'first_name'           => 'required|max:100',
            'last_name'            => 'required|max:100',
            'phone'                => 'nullable|max:20',
            'address'              => 'nullable|max:255',
            'come_from'            => 'nullable|max:255',
            'subject'              => 'required|max:100',
            'date_of_birth'        => 'nullable|date|before:today',
            'gender'               => 'nullable|in:male,female,other',
            'year_level'           => 'required|integer|min:1|max:12',
            'enrollment_date'      => 'required|date',
            'photo'                => 'nullable|image|max:2048',
            'monthly_payment_day'  => 'required|integer|min:1|max:31',
            'monthly_fee'          => 'required|numeric|min:0',
            'time_type'            => 'required',
            'payment_method'       => 'required|in:cash,bank_transfer',
            'payment_photo'        => 'nullable|image|max:2048',
        ]);

        if (empty($validated['student_id'])) {
            $validated['student_id'] = Student::generateStudentId();
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $validated['status'] = 'active';
        
        $timeType = $validated['time_type'];
        $paymentMethod = $validated['payment_method'];
        $paymentPhoto = $request->file('payment_photo');
        
        unset($validated['payment_method'], $validated['payment_photo']);
        
        $student = Student::create($validated);

        // Create first payment automatically based on enrollment date (paid, with admin fee)
        $enrollmentDate = Carbon::parse($validated['enrollment_date']);
        $firstPaymentDate = $enrollmentDate->copy();
        $nextPaymentDate = $firstPaymentDate->copy()->addMonth();
        $adminFee = 20;
        $totalAmount = $validated['monthly_fee'] + $adminFee;

        $paymentData = [
            'receipt_number' => Payment::generateReceiptNumber(),
            'student_id' => $student->id,
            'amount_due' => $validated['monthly_fee'],
            'admin_fee' => $adminFee,
            'amount_paid' => $totalAmount,
            'balance' => 0,
            'payment_date' => $firstPaymentDate,
            'due_date' => $firstPaymentDate,
            'deadline_date' => $firstPaymentDate,
            'next_payment_date' => $nextPaymentDate,
            'status' => 'paid',
            'payment_method' => $paymentMethod,
            'time_type' => $timeType,
            'created_by' => Auth::id(),
        ];

        if ($paymentPhoto) {
            $paymentData['photo'] = $paymentPhoto->store('payments/photos', 'public');
        }

        Payment::create($paymentData);

        return redirect()->route('students.index')
            ->with('success', 'Student enrolled successfully! First payment created.');
    }

    public function show(Student $student)
    {
        $student->load(['payments']);
        $payments = $student->payments()->latest()->paginate(10);
        $stats = [
            'total_paid'    => $student->payments()->where('status', 'paid')->sum('amount_paid'),
            'total_due'     => $student->payments()->whereIn('status', ['pending', 'partial', 'overdue'])->sum('balance'),
            'overdue_count' => $student->payments()->where('status', 'overdue')->count(),
            'paid_count'    => $student->payments()->where('status', 'paid')->count(),
        ];
        return view('students.show', compact('student', 'payments', 'stats'));
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_id'           => 'required|max:20|unique:students,student_id,' . $student->id,
            'first_name'           => 'required|max:100',
            'last_name'            => 'required|max:100',
            'phone'                => 'nullable|max:20',
            'address'              => 'nullable|max:255',
            'come_from'            => 'nullable|max:255',
            'subject'              => 'required|max:100',
            'date_of_birth'        => 'nullable|date|before:today',
            'gender'               => 'nullable|in:male,female,other',
            'year_level'           => 'required|integer|min:1|max:12',
            'enrollment_date'      => 'required|date',
            'photo'                => 'nullable|image|max:2048',
            'monthly_payment_day'  => 'required|integer|min:1|max:31',
            'monthly_fee'          => 'required|numeric|min:0',
            'time_type'            => 'required',
        ]);

        if ($request->hasFile('photo')) {
            if ($student->photo) Storage::disk('public')->delete($student->photo);
            $validated['photo'] = $request->file('photo')->store('students/photos', 'public');
        }
        $student->update($validated);
        return redirect()->route('students.show', $student)
            ->with('success', 'Student updated successfully!');
    }

    public function destroy(Student $student)
    {
        $student->payments()->delete();
        $student->delete();
        return redirect()->route('students.index')
            ->with('success', 'Student removed successfully!');
    }
}
