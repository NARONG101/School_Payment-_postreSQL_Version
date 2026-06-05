<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Squashed migration — replaces all incremental ALTER TABLE migrations
 * from 2026-05-26 and 2026-05-27.
 *
 * This migration is a no-op if the columns already exist (idempotent),
 * so it is safe to run on both fresh installs and existing databases.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Students table additions ──────────────────────────
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'monthly_payment_day')) {
                $table->unsignedTinyInteger('monthly_payment_day')->default(1);
            }
            if (! Schema::hasColumn('students', 'monthly_fee')) {
                $table->decimal('monthly_fee', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('students', 'come_from')) {
                $table->string('come_from')->nullable();
            }
            if (! Schema::hasColumn('students', 'subject')) {
                $table->string('subject')->nullable();
            }
            if (! Schema::hasColumn('students', 'time_type')) {
                $table->string('time_type')->nullable();
            }
        });

        // ── Payments table additions ──────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'next_payment_date')) {
                $table->date('next_payment_date')->nullable();
            }
            if (! Schema::hasColumn('payments', 'time_type')) {
                $table->string('time_type')->nullable()->default('weekday');
            }
            if (! Schema::hasColumn('payments', 'admin_fee')) {
                $table->decimal('admin_fee', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('payments', 'photo')) {
                $table->string('photo')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['monthly_payment_day', 'monthly_fee', 'come_from', 'subject', 'time_type'],
                fn ($col) => Schema::hasColumn('students', $col)
            ));
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['next_payment_date', 'time_type', 'admin_fee', 'photo'],
                fn ($col) => Schema::hasColumn('payments', $col)
            ));
        });
    }
};
