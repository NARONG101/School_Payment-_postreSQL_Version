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
        'year_level', 'enrollment_date', 'photo', 'monthly_payment_day', 'monthly_fee', 'time_type', 'status'
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
        return "{$this->first_name} {$this->last_name}";
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
        return $this->payments()->latest('payment_date')->first();
    }

    public function getNextPaymentDateAttribute()
    {
        $lastPayment = $this->last_payment;
        if (!$lastPayment || !$lastPayment->payment_date) {
            return null;
        }
        $nextPaymentDate = \Carbon\Carbon::parse($lastPayment->payment_date)->addMonth();
        if ($this->monthly_payment_day) {
            try {
                $nextPaymentDate->day($this->monthly_payment_day);
            } catch (\Exception $e) {
            }
        }
        return $nextPaymentDate;
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