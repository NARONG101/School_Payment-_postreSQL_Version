<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id', 'first_name', 'last_name', 'phone',
        'address', 'come_from', 'subject', 'date_of_birth', 'gender',
        'year_level', 'enrollment_date', 'photo', 'monthly_payment_day',
        'monthly_fee', 'time_type', 'status', 'study_status',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'date_of_birth'   => 'date',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->last_name} {$this->first_name}";
    }

    /**
     * Always store time_type as lowercase to prevent case-mismatch bugs.
     */
    public function setTimeTypeAttribute(?string $value): void
    {
        $this->attributes['time_type'] = $value ? strtolower(trim($value)) : null;
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->where('status', 'paid')->sum('amount_paid');
    }

    public function getTotalDueAttribute(): float
    {
        return $this->payments()->whereIn('status', ['pending', 'partial', 'overdue'])->sum('balance');
    }

    public function getOverduePaymentsAttribute()
    {
        return $this->payments()->where('status', 'overdue')->get();
    }

    public function getUpcomingDeadlinesAttribute()
    {
        return $this->payments()
            ->whereIn('status', ['pending', 'partial'])
            ->where('deadline_date', '>=', now())
            ->where('deadline_date', '<=', now()->addDays(7))
            ->get();
    }

    public function getLastPaymentAttribute()
    {
        // Return the payment covering the furthest month
        // (highest next_payment_date = most recent month covered)
        return $this->payments()
            ->orderByDesc('next_payment_date')
            ->orderByDesc('id')
            ->first();
    }

    public function getNextPaymentDateAttribute()
    {
        $lastPayment = $this->last_payment;
        if (!$lastPayment || !$lastPayment->payment_date) {
            return null;
        }

        // Use stored next_payment_date if available (most accurate)
        if ($lastPayment->next_payment_date) {
            return $lastPayment->next_payment_date;
        }

        // Recalculate using the anchored logic
        $paymentDay = (int) ($this->monthly_payment_day ?? $lastPayment->payment_date->day);
        return self::nextPaymentDateFrom(
            \Carbon\Carbon::parse($lastPayment->payment_date),
            $paymentDay
        );
    }

    /**
     * Calculate the next payment date anchored to a fixed day-of-month.
     *
     * Rule: find the next occurrence of $paymentDay AFTER $paidDate.
     *   - If $paymentDay is still in the future this month → use it.
     *   - Otherwise → use it next month.
     *
     * Example: paid 01 Jun, day=27 → next = 27 Jun
     *          paid 28 Jun, day=27 → next = 27 Jul
     */
    public static function nextPaymentDateFrom(\Carbon\Carbon $paidDate, int $paymentDay): \Carbon\Carbon
    {
        $paymentDay = max(1, min(31, $paymentDay));

        // Try same month — clamp to last day of that month
        $lastDaySame   = (int) $paidDate->copy()->endOfMonth()->day;
        $daySame       = min($paymentDay, $lastDaySame);
        $candidateSame = $paidDate->copy()->startOfMonth()->day($daySame);

        // Use same-month candidate ONLY if it is strictly AFTER the paid date
        // (if paid on the exact day, that month is done → go to next month)
        if ($candidateSame->gt($paidDate)) {
            return $candidateSame;
        }

        // Next month — clamp to last day of that month
        $nextMonthStart = $paidDate->copy()->addMonthNoOverflow()->startOfMonth();
        $lastDayNext    = (int) $nextMonthStart->copy()->endOfMonth()->day;
        $dayNext        = min($paymentDay, $lastDayNext);
        return $nextMonthStart->copy()->day($dayNext);
    }

    public function getDaysUntilNextPaymentAttribute()
    {
        $nextPaymentDate = $this->next_payment_date;
        if (!$nextPaymentDate) {
            return null;
        }
        return (int) \Carbon\Carbon::now()->diffInDays($nextPaymentDate, false);
    }

    public function getAlertLevelAttribute()
    {
        $daysLeft = $this->days_until_next_payment;
        if ($daysLeft === null) {
            return 'overdue';
        }
        if ($daysLeft < 0) {
            return 'overdue';
        }
        if ($daysLeft <= 7) {
            return 'critical';
        }
        return 'normal';
    }

    public static function generateStudentId(): string
    {
        $prefix = 'STU-' . date('Y') . '-';
        $last = static::withTrashed()
            ->where('student_id', 'like', $prefix . '%')
            ->latest()
            ->first();

        $number = $last
            ? str_pad((int) substr($last->student_id, -5) + 1, 5, '0', STR_PAD_LEFT)
            : '00001';

        return $prefix . $number;
    }
}