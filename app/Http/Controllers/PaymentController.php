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
    /** Allowed time slots — validated server-side */
    private const TIME_SLOTS = [
        'mon-fri 7:00-9:00',
        'mon-fri 9:00-11:00',
        'mon-fri 1:00-3:00',
        'mon-fri 3:00-5:00',
        'mon-fri 5:30-7:30',
        'sat-sun 7:00-11:00',
        'sat-sun 1:00-5:00',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // NEXT PAYMENT DATE — single source of truth is Student::nextPaymentDateFrom
    // ─────────────────────────────────────────────────────────────────────────
    public static function calcNextPaymentDate(Carbon $paidDate, int $paymentDay): Carbon
    {
        return Student::nextPaymentDateFrom($paidDate, $paymentDay);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function activeStudentsWithLastPayment(): \Illuminate\Database\Eloquent\Builder
    {
        // Order by next_payment_date DESC so the payment that covers
        // the furthest month forward is always first.
        // This handles the case where someone pays back-months on the same day.
        return Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->with(['payments' => fn ($q) => $q->orderByDesc('next_payment_date')
                                             ->orderByDesc('id')]);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // Query payments directly — much simpler and correct sorting
        $query = \App\Models\Payment::with('student')
            ->whereHas('student', function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            });

        // Filter by class type (weekday = mon-fri, weekend = sat-sun)
        $classType = $request->get('class_type', '');
        if ($classType === 'weekday') {
            $query->where('time_type', 'like', 'mon-fri%');
        } elseif ($classType === 'weekend') {
            $query->where('time_type', 'like', 'sat-sun%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'id');
        $payments = match ($sortBy) {
            'date'  => $query->orderByDesc('due_date')->orderByDesc('id')->get(),
            'grade' => $query->get()
                             ->sortByDesc(fn ($p) => $p->student?->year_level ?? 0)
                             ->values(),
            default => $query->orderByDesc('id')->get(), // newest first
        };

        return view('payments.index', compact('payments', 'classType'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $students = Student::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->with(['payments' => fn ($q) => $q->orderByDesc('next_payment_date')
                                             ->orderByDesc('id')])
          ->orderBy('first_name')
          ->get();

        $selectedStudentId = $request->query('student_id');
        return view('payments.create', compact('students', 'selectedStudentId'));
    }

    // ── Store (handles both AJAX and normal POST) ─────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'        => 'required|exists:students,id',
            'payment_date'      => 'required|date',
            'covering_month'    => 'required|date',
            'next_payment_date' => 'nullable|date',
            'time_type'         => 'required|in:' . implode(',', self::TIME_SLOTS),
            'payment_method'    => 'required|in:cash,bank_transfer',
            'photo'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'notes'             => 'nullable|string|max:500',
        ]);

        $student       = Student::findOrFail($validated['student_id']);
        $paidDate      = Carbon::parse($validated['payment_date']);
        $coveringMonth = Carbon::parse($validated['covering_month'])->startOfMonth();
        $paymentDay    = (int) ($student->monthly_payment_day ?? $paidDate->day);

        // Next due = payment_day of month AFTER the covering month
        if (!empty($validated['next_payment_date'])) {
            $nextDate = Carbon::parse($validated['next_payment_date']);
        } else {
            $nextDate = Student::nextPaymentDateFrom($coveringMonth, $paymentDay);
        }

        // due_date = payment_day within the covering month (safe clamp)
        $lastDayCovering = (int) $coveringMonth->copy()->endOfMonth()->day;
        $dueDate         = $coveringMonth->copy()->day(min($paymentDay, $lastDayCovering));

        $validated['receipt_number']    = Payment::generateReceiptNumber();
        $validated['amount_due']        = (float) $student->monthly_fee;
        $validated['admin_fee']         = 0;
        $validated['amount_paid']       = (float) $student->monthly_fee;
        $validated['balance']           = 0;
        $validated['status']            = 'paid';
        $validated['created_by']        = Auth::id();
        $validated['due_date']          = $dueDate;
        $validated['deadline_date']     = $dueDate;
        $validated['next_payment_date'] = $nextDate;
        $validated['payment_date']      = $paidDate;

        if (empty($validated['notes'])) {
            $validated['notes'] = 'Payment for ' . $coveringMonth->format('F Y');
        }

        unset($validated['covering_month']);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('payments/photos', 'public');
        } elseif ($request->filled('captured_photo_data')) {
            // Save base64 camera capture as JPEG file
            $data     = $request->input('captured_photo_data');
            $data     = preg_replace('/^data:image\/\w+;base64,/', '', $data);
            $decoded  = base64_decode($data);
            $filename = 'payments/photos/cam_' . uniqid() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decoded);
            $validated['photo'] = $filename;
        }

        $payment = Payment::create($validated);

        // Always redirect to payment detail page so user can see receipt, print, or continue
        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Payment recorded for ' . $coveringMonth->format('F Y') . '! Receipt #' . $payment->receipt_number);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Payment $payment)
    {
        $payment->load(['student', 'paymentType', 'creator']);
        return view('payments.show', compact('payment'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(Payment $payment)
    {
        $paymentTypes = PaymentType::where('is_active', true)->get();
        return view('payments.edit', compact('payment', 'paymentTypes'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_date'      => 'nullable|date',
            'payment_method'    => 'required|in:cash,bank_transfer',
            'time_type'         => 'required|in:' . implode(',', self::TIME_SLOTS),
            'photo'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'deadline_date'     => 'required|date',
            'next_payment_date' => 'nullable|date',
            'notes'             => 'nullable|string|max:500',
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

    // ── CSV Export — All Payments ─────────────────────────────────────────────

    public function exportCsv(Request $request)
    {
        $query = Payment::with('student')
            ->whereHas('student', function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            });

        // Filter by class type
        $classType = $request->get('class_type', '');
        if ($classType === 'weekday') {
            $query->where('time_type', 'like', 'mon-fri%');
        } elseif ($classType === 'weekend') {
            $query->where('time_type', 'like', 'sat-sun%');
        }

        $sortBy = $request->get('sort_by', 'id');
        $payments = match ($sortBy) {
            'date'  => $query->orderByDesc('due_date')->orderByDesc('id')->get(),
            'grade' => $query->get()->sortByDesc(fn ($p) => $p->student?->year_level ?? 0)->values(),
            default => $query->orderByDesc('id')->get(),
        };

        $classLabel = match($classType) {
            'weekday' => '_weekday',
            'weekend' => '_weekend',
            default   => '',
        };

        $filename = 'payments' . $classLabel . '_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payments, $classType) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            $classLabel = match($classType) {
                'weekday' => ' (' . __('app.weekday_class') . ')',
                'weekend' => ' (' . __('app.weekend_class') . ')',
                default   => '',
            };

            fputcsv($handle, [__('app.all_payments') . $classLabel . ' — ' . now()->format('d/m/Y')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                __('app.receipt'), __('app.student_id'), __('app.first_name') . ' ' . __('app.last_name'),
                __('app.grade'), __('app.weekday') . '/' . __('app.weekend'),
                __('app.amount'), 'Admin Fee', __('app.paid'),
                'Balance', 'Payment Date', 'Due Date', 'Next Payment',
                __('app.status'), __('app.payment_method'), __('app.time_type'), __('app.notes'),
            ]);

            // Group by grade
            $byGrade = $payments->groupBy(fn($p) => $p->student?->year_level ?? '?');
            ksort($byGrade);
            foreach ($byGrade as $grade => $gradePayments) {
                fputcsv($handle, []);
                fputcsv($handle, [__('app.grade') . ' ' . $grade]);
                foreach ($gradePayments as $p) {
                    $ct = str_starts_with($p->time_type ?? '', 'sat-sun')
                        ? __('app.weekend')
                        : __('app.weekday');
                    fputcsv($handle, [
                        $p->receipt_number,
                        $p->student?->student_id ?? '',
                        $p->student?->full_name ?? '',
                        $grade,
                        $ct,
                        $p->amount_due,
                        $p->admin_fee ?? 0,
                        $p->amount_paid,
                        $p->balance ?? 0,
                        $p->payment_date?->format('Y-m-d') ?? '',
                        $p->due_date?->format('Y-m-d') ?? '',
                        $p->next_payment_date?->format('Y-m-d') ?? '',
                        $p->status,
                        $p->payment_method ?? '',
                        $p->time_type ?? '',
                        $p->notes ?? '',
                    ]);
                }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── CSV Export — Deadline Alerts ──────────────────────────────────────────

    public function exportAlertsCsv(Request $request)
    {
        $now      = Carbon::now();
        $students = $this->activeStudentsWithLastPayment()->get();
        $filter    = $request->get('filter', 'all');
        $classType = $request->get('class_type', '');

        // Apply class type filter before building alert data
        if ($classType === 'weekday') {
            $students = $students->filter(fn ($s) => str_starts_with($s->time_type ?? '', 'mon-fri'));
        } elseif ($classType === 'weekend') {
            $students = $students->filter(fn ($s) => str_starts_with($s->time_type ?? '', 'sat-sun'));
        }

        $rows = $students->map(fn ($s) => $this->buildStudentAlertData($s, $now))
            ->when($filter !== 'all', fn ($c) => $c->filter(fn ($d) => $d['alertLevel'] === $filter))
            ->sortBy(fn ($d) => $d['daysUntilNextPayment'] ?? 0)
            ->values();

        $classLabel = match($classType) {
            'weekday' => '_weekday',
            'weekend' => '_weekend',
            default   => '',
        };

        $filename = 'deadline_alerts' . $classLabel . '_' . now()->format('Y-m-d') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows, $classType) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            $classLabel = match($classType) {
                'weekday' => ' (' . __('app.weekday_class') . ')',
                'weekend' => ' (' . __('app.weekend_class') . ')',
                default   => '',
            };

            fputcsv($handle, [__('app.deadline_alerts') . $classLabel . ' — ' . now()->format('d/m/Y')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                __('app.student_id'), __('app.first_name') . ' ' . __('app.last_name'),
                __('app.grade'), __('app.weekday') . '/' . __('app.weekend'),
                __('app.subject'), __('app.next_payment'), 'Days Until Due',
                __('app.status'), 'Last Payment Date', __('app.monthly_fee'),
            ]);

            $byGrade = $rows->groupBy(fn($d) => $d['student']->year_level ?? '?');
            ksort($byGrade);
            foreach ($byGrade as $grade => $gradeRows) {
                fputcsv($handle, []);
                fputcsv($handle, [__('app.grade') . ' ' . $grade]);
                foreach ($gradeRows as $d) {
                    $s = $d['student'];
                    $ct = str_starts_with($s->time_type ?? '', 'sat-sun')
                        ? __('app.weekend')
                        : __('app.weekday');
                    fputcsv($handle, [
                        $s->student_id,
                        $s->full_name ?? '',
                        $s->year_level ?? '',
                        $ct,
                        $s->subject ?? '',
                        $d['nextPaymentDate']?->format('Y-m-d') ?? 'N/A',
                        $d['daysUntilNextPayment'] ?? 'N/A',
                        __('app.' . $d['alertLevel']) ?? $d['alertLevel'],
                        $d['lastPayment']?->payment_date?->format('Y-m-d') ?? 'N/A',
                        $s->monthly_fee ?? '',
                    ]);
                }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Payment $payment)
    {
        if ($payment->photo) {
            Storage::disk('public')->delete($payment->photo);
        }
        $payment->delete();
        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully!');
    }

    // ── PDF Receipt ───────────────────────────────────────────────────────────

    private function buildReceiptMpdf(Payment $payment): \Mpdf\Mpdf
    {
        $fontDir  = storage_path('fonts');
        $tmpDir   = storage_path('framework/cache/mpdf');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $config     = new \Mpdf\Config\ConfigVariables();
        $fontConfig = new \Mpdf\Config\FontVariables();

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A5',
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_left'   => 0,
            'margin_right'  => 0,
            'tempDir'       => $tmpDir,
            'fontDir'       => array_merge(
                $config->getDefaults()['fontDir'],
                [$fontDir]
            ),
            'fontdata' => $fontConfig->getDefaults()['fontdata'] + [
                'kantumruypro' => [
                    'R'  => 'KantumruyPro.ttf',
                    'B'  => 'KantumruyPro.ttf',
                    'I'  => 'KantumruyPro.ttf',
                    'BI' => 'KantumruyPro.ttf',
                ],
            ],
            'default_font'    => 'kantumruypro',
            'allowCJKOrphans' => false,
        ]);

        // ── Native mPDF watermark — perfectly centered, angle, alpha ──
        if ($payment->status === 'paid') {
            $mpdf->SetWatermarkText('PAID');
            $mpdf->watermark_font      = 'dejavusans'; // bold latin font for watermark
            $mpdf->watermarkTextAlpha  = 0.06;         // very subtle (0 = invisible, 1 = solid)
            $mpdf->showWatermarkText   = true;
        }

        $html = view('receipts.payment', compact('payment'))->render();
        $mpdf->WriteHTML($html);
        return $mpdf;
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['student', 'paymentType', 'creator']);
        $mpdf = $this->buildReceiptMpdf($payment);
        $filename = 'receipt-' . $payment->receipt_number . '.pdf';
        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function receiptDownload(Payment $payment)
    {
        $payment->load(['student', 'paymentType', 'creator']);
        $mpdf = $this->buildReceiptMpdf($payment);
        $filename = 'receipt-' . $payment->receipt_number . '.pdf';
        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ── Alert helpers ─────────────────────────────────────────────────────────

    private function buildStudentAlertData(Student $student, Carbon $now): array
    {
        $lastPayment = $student->payments->first();
        $data = [
            'student'              => $student,
            'lastPayment'          => $lastPayment,
            'nextPaymentDate'      => null,
            'daysUntilNextPayment' => null,
            'alertLevel'           => 'overdue',
        ];

        if ($lastPayment && $lastPayment->payment_date) {
            // Use the stored next_payment_date if available (most accurate)
            // Otherwise recalculate using the anchored logic
            if ($lastPayment->next_payment_date) {
                $next = Carbon::parse($lastPayment->next_payment_date);
            } else {
                $paymentDay = (int) ($student->monthly_payment_day ?? $lastPayment->payment_date->day);
                $next = Student::nextPaymentDateFrom(
                    Carbon::parse($lastPayment->payment_date),
                    $paymentDay
                );
            }

            $days = (int) $now->diffInDays($next, false);

            $data['nextPaymentDate']      = $next;
            $data['daysUntilNextPayment'] = $days;
            $data['alertLevel']           = match (true) {
                $days < 0  => 'overdue',
                $days <= 7 => 'closely',
                default    => 'upcoming',
            };
        }

        return $data;
    }

    // ── Deadline Alerts ───────────────────────────────────────────────────────

    public function deadlineAlerts(Request $request)
    {
        $now       = Carbon::now();
        $classType = $request->get('class_type', '');
        $students  = $this->activeStudentsWithLastPayment()->get();

        // Apply class type filter
        if ($classType === 'weekday') {
            $students = $students->filter(fn ($s) => str_starts_with($s->time_type ?? '', 'mon-fri'));
        } elseif ($classType === 'weekend') {
            $students = $students->filter(fn ($s) => str_starts_with($s->time_type ?? '', 'sat-sun'));
        }

        $overdue  = collect();
        $closely  = collect();
        $upcoming = collect();
        $all      = collect();

        foreach ($students as $student) {
            $data = $this->buildStudentAlertData($student, $now);
            $all->push($data);
            match ($data['alertLevel']) {
                'overdue' => $overdue->push($data),
                'closely' => $closely->push($data),
                default   => $upcoming->push($data),
            };
        }

        // Sort: overdue (most days late first) → closely (soonest first) → upcoming (soonest first)
        $all = $all->sortBy(function ($d) {
            $days  = $d['daysUntilNextPayment'] ?? 0;
            $level = $d['alertLevel'];
            if ($level === 'overdue')  return $days;
            if ($level === 'closely')  return 1000 + $days;
            return 2000 + $days;
        })->values();

        // ── Filter / search for the All Students table ────────────
        $filterLevel = $request->get('filter', 'all');   // all | overdue | closely | upcoming
        $search      = trim($request->get('search', ''));
        $filterGrade = $request->get('grade', '');

        $filtered = $all->filter(function ($d) use ($filterLevel, $search, $filterGrade) {
            if ($filterLevel !== 'all' && $d['alertLevel'] !== $filterLevel) return false;
            if ($filterGrade !== '' && (string)($d['student']->year_level ?? '') !== $filterGrade) return false;
            if ($search !== '') {
                $hay  = strtolower(
                    ($d['student']->full_name   ?? '') . ' ' .
                    ($d['student']->student_id  ?? '') . ' ' .
                    ($d['student']->subject     ?? '')
                );
                if (strpos($hay, strtolower($search)) === false) return false;
            }
            return true;
        })->values();

        $availableGrades = $all->pluck('student.year_level')->unique()->filter()->sort()->values();

        return view('payments.alerts', [
            'overdue'         => $overdue,
            'closely'         => $closely,
            'upcoming'        => $upcoming,
            'allStudentData'  => $filtered,
            'totalCount'      => $all->count(),
            'filterLevel'     => $filterLevel,
            'search'          => $search,
            'filterGrade'     => $filterGrade,
            'availableGrades' => $availableGrades,
            'classType'       => $classType,
        ]);
    }

    public function alertsOverdue()
    {
        $now    = Carbon::now();
        $grades = $this->activeStudentsWithLastPayment()->get()
            ->filter(fn ($s) => $this->buildStudentAlertData($s, $now)['alertLevel'] === 'overdue')
            ->pluck('year_level')->unique()->sort()->values();

        return view('payments.alerts-grades', ['grades' => $grades, 'title' => 'Overdue Payments', 'type' => 'overdue']);
    }

    public function alertsOverdueGrade(string|int $grade)
    {
        $now  = Carbon::now();
        $data = $this->activeStudentsWithLastPayment()->where('year_level', $grade)->get()
            ->map(fn ($s) => $this->buildStudentAlertData($s, $now))
            ->filter(fn ($d) => $d['alertLevel'] === 'overdue')->values();

        return view('payments.alerts-grades', ['studentData' => $data, 'title' => "Overdue — Grade {$grade}", 'type' => 'overdue']);
    }

    public function alertsClosely()
    {
        $now    = Carbon::now();
        $grades = $this->activeStudentsWithLastPayment()->get()
            ->filter(fn ($s) => $this->buildStudentAlertData($s, $now)['alertLevel'] === 'closely')
            ->pluck('year_level')->unique()->sort()->values();

        return view('payments.alerts-grades', ['grades' => $grades, 'title' => 'Closely Date Payments', 'type' => 'closely']);
    }

    public function alertsCloselyGrade(string|int $grade)
    {
        $now  = Carbon::now();
        $data = $this->activeStudentsWithLastPayment()->where('year_level', $grade)->get()
            ->map(fn ($s) => $this->buildStudentAlertData($s, $now))
            ->filter(fn ($d) => $d['alertLevel'] === 'closely')->values();

        return view('payments.alerts-grades', ['studentData' => $data, 'title' => "Closely Date — Grade {$grade}", 'type' => 'closely']);
    }

    public function alertsUpcoming()
    {
        $now    = Carbon::now();
        $grades = $this->activeStudentsWithLastPayment()->get()
            ->filter(fn ($s) => $this->buildStudentAlertData($s, $now)['alertLevel'] === 'upcoming')
            ->pluck('year_level')->unique()->sort()->values();

        return view('payments.alerts-grades', ['grades' => $grades, 'title' => 'Upcoming Payments', 'type' => 'upcoming']);
    }

    public function alertsUpcomingGrade(string|int $grade)
    {
        $now  = Carbon::now();
        $data = $this->activeStudentsWithLastPayment()->where('year_level', $grade)->get()
            ->map(fn ($s) => $this->buildStudentAlertData($s, $now))
            ->filter(fn ($d) => $d['alertLevel'] === 'upcoming')->values();

        return view('payments.alerts-grades', ['studentData' => $data, 'title' => "Upcoming — Grade {$grade}", 'type' => 'upcoming']);
    }
}
