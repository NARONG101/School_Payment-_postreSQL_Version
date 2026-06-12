<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add time_types to students
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'time_types')) {
                $table->json('time_types')->nullable();
            }
        });

        // Migrate existing time_type to time_types (as single-element array)
        DB::table('students')
            ->whereNotNull('time_type')
            ->whereNull('time_types')
            ->update(['time_types' => DB::raw("json_build_array(time_type)")]);

        // Add time_types and months_covered to payments
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'time_types')) {
                $table->json('time_types')->nullable();
            }
            if (! Schema::hasColumn('payments', 'months_covered')) {
                $table->unsignedTinyInteger('months_covered')->default(1);
            }
        });

        // Migrate existing time_type to time_types (as single-element array)
        DB::table('payments')
            ->whereNotNull('time_type')
            ->whereNull('time_types')
            ->update(['time_types' => DB::raw("json_build_array(time_type)")]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'time_types')) {
                $table->dropColumn('time_types');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'time_types')) {
                $table->dropColumn('time_types');
            }
            if (Schema::hasColumn('payments', 'months_covered')) {
                $table->dropColumn('months_covered');
            }
        });
    }
};
