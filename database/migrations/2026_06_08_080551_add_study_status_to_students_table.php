<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Add study_status: 'studying' (default) or 'stopped'
            // This is separate from the soft-delete 'status' field
            if (! Schema::hasColumn('students', 'study_status')) {
                $table->enum('study_status', ['studying', 'stopped'])
                      ->default('studying')
                      ->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('study_status');
        });
    }
};
