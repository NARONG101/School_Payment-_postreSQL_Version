<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            $payment->calculateStatus();
            self::updateOverduePayments();
        });

        static::updating(function ($payment) {
            $payment->calculateStatus();
            self::updateOverduePayments();
        });

        static::deleting(function () {
            self::updateOverduePayments();
        });
    }

    protected $fillable = [
        'receipt_number', 'student_id', 'payment_type_id', 'amount_due',
        'admin_fee', 'discount', 'amount_paid', 'balance', 'payment_date', 'deadline_date', 'due_date',
        'status', 'payment_method', 'reference_number', 'photo', 'notes',
        'semester', 'school_year', 'created_by', 'next_payment_date', 'time_type', 'time_types', 'months_covered',
    ];

    protected $casts = [
        'payment_date'       => 'date',
        'deadline_date'      => 'date',
        'due_date'           => 'date',
        'next_payment_date'  => 'date',
        'amount_due'         => 'decimal:2',
        'admin_fee'          => 'decimal:2',
        'discount'           => 'decimal:2',
        'amount_paid'        => 'decimal:2',
        'balance'            => 'decimal:2',
        'time_types'         => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Always store time_type as lowercase, and sync with time_types.
     */
    public function setTimeTypeAttribute(?string $value): void
    {
        $this->attributes['time_type'] = $value ? strtolower(trim($value)) : null;
        if ($value) {
            $this->attributes['time_types'] = json_encode([strtolower(trim($value))]);
        } else {
            $this->attributes['time_types'] = null;
        }
    }

    /**
     * Get time_type from time_types if available (backward compatibility).
     */
    public function getTimeTypeAttribute(?string $value): ?string
    {
        if ($this->time_types && is_array($this->time_types) && !empty($this->time_types)) {
            return $this->time_types[0];
        }
        return $value;
    }

    /**
     * Sync time_type when time_types is set.
     *
     * @param mixed $value
     */
    public function setTimeTypesAttribute(mixed $value): void
    {
        if (is_array($value)) {
            $this->attributes['time_types'] = json_encode(array_map('strtolower', array_map('trim', $value)));
            if (!empty($value)) {
                $this->attributes['time_type'] = strtolower(trim($value[0]));
            } else {
                $this->attributes['time_type'] = null;
            }
        } elseif (is_string($value)) {
            $this->attributes['time_types'] = $value;
            $decoded = json_decode($value, true);
            if (is_array($decoded) && !empty($decoded)) {
                $this->attributes['time_type'] = strtolower(trim($decoded[0]));
            } else {
                $this->attributes['time_type'] = null;
            }
        } else {
            $this->attributes['time_types'] = null;
            $this->attributes['time_type'] = null;
        }
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->deadline_date < now() && $this->status !== 'paid';
    }

    public function getDaysUntilDeadlineAttribute(): int
    {
        return (int) now()->diffInDays($this->deadline_date, false);
    }

    public function getDeadlineAlertLevelAttribute(): string
    {
        $days = $this->days_until_deadline;
        if ($this->status === 'paid') return 'none';
        if ($days < 0) return 'overdue';
        if ($days <= 3) return 'critical';
        if ($days <= 7) return 'warning';
        return 'normal';
    }

    public static function generateReceiptNumber(): string
    {
        $prefix = 'RCP-' . date('Y') . '-';
        $last = static::withTrashed()
            ->where('receipt_number', 'like', $prefix . '%')
            ->latest('id')
            ->first();

        $number = $last
            ? str_pad((int) substr($last->receipt_number, -5) + 1, 5, '0', STR_PAD_LEFT)
            : '00001';

        $receiptNumber = $prefix . $number;

        // Check if this receipt number already exists, if so increment until we find a unique one
        $attempts = 0;
        while (static::withTrashed()->where('receipt_number', $receiptNumber)->exists() && $attempts < 100) {
            $number = str_pad((int) $number + 1, 5, '0', STR_PAD_LEFT);
            $receiptNumber = $prefix . $number;
            $attempts++;
        }

        return $receiptNumber;
    }

    public function updateStatus(): void
    {
        $this->calculateStatus();
        $this->save();
    }

    public function calculateStatus(): void
    {
        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = $this->deadline_date && $this->deadline_date < now() ? 'overdue' : 'partial';
        } else {
            $this->status = $this->deadline_date && $this->deadline_date < now() ? 'overdue' : 'pending';
        }
    }

    public static function updateOverduePayments(): void
    {
        static::whereIn('status', ['pending', 'partial'])
            ->where('deadline_date', '<', now())
            ->update(['status' => 'overdue']);
    }
}