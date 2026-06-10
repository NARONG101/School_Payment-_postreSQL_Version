<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\User;

// ── Delete all uploaded photos ────────────────────────────────
foreach (Payment::withTrashed()->whereNotNull('photo')->pluck('photo') as $p) {
    Storage::disk('public')->delete($p);
}
foreach (Student::withTrashed()->whereNotNull('photo')->pluck('photo') as $p) {
    Storage::disk('public')->delete($p);
}
echo "✓ Storage photos cleaned.\n";

// ── Wipe all tables ───────────────────────────────────────────
// Disable foreign key checks for PostgreSQL
DB::statement('SET CONSTRAINTS ALL DEFERRED');

Payment::withTrashed()->forceDelete();
Student::withTrashed()->forceDelete();
PaymentType::query()->delete();
User::query()->delete();

// Also clear sessions, cache, jobs tables (if they exist)
$optionalTables = ['sessions', 'cache', 'jobs', 'job_batches', 'failed_jobs'];
foreach ($optionalTables as $table) {
    try {
        DB::table($table)->delete();
    } catch (\Exception $e) {
        // Ignore if table doesn't exist
    }
}

// Reset sequences (PostgreSQL specific)
$sequences = ['payments_id_seq', 'students_id_seq', 'payment_types_id_seq', 'users_id_seq'];
foreach ($sequences as $seq) {
    try {
        DB::statement("ALTER SEQUENCE {$seq} RESTART WITH 1");
    } catch (\Exception $e) {
        // Ignore if sequence doesn't exist
    }
}

echo "✓ All tables wiped (payments, students, payment_types, users, sessions, cache, jobs).\n";

// ── Re-seed admin user ────────────────────────────────────────
$adminEmail    = env('ADMIN_EMAIL',    'admin@school.edu');
$adminPassword = env('ADMIN_PASSWORD', 'ChangeMe123!');
$adminName     = env('ADMIN_NAME',     'Admin User');

User::create([
    'name'     => $adminName,
    'email'    => $adminEmail,
    'password' => Hash::make($adminPassword),
    'role'     => 'admin',
]);
echo "✓ Admin user created: {$adminEmail}\n";

// ── Re-seed default payment types ────────────────────────────
$types = [
    ['name' => 'Monthly Tuition',   'description' => 'Regular monthly tuition fee', 'amount' => 0,   'is_active' => true],
    ['name' => 'Registration Fee',  'description' => 'One-time registration fee',   'amount' => 50,  'is_active' => true],
    ['name' => 'Books & Materials', 'description' => 'Books and learning materials','amount' => 100, 'is_active' => true],
];
foreach ($types as $t) {
    PaymentType::create($t);
}
echo "✓ Re-seeded " . count($types) . " default payment types.\n";

echo "\n✅ Full reset complete. Database is clean and ready.\n";
echo "   Login: {$adminEmail} / {$adminPassword}\n";
