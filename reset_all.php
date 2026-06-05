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
DB::statement('PRAGMA foreign_keys = OFF');

Payment::withTrashed()->forceDelete();
Student::withTrashed()->forceDelete();
PaymentType::query()->delete();
User::query()->delete();

// Also clear sessions, cache, jobs tables
DB::table('sessions')->delete();
DB::table('cache')->delete();
DB::table('jobs')->delete();

DB::statement('PRAGMA foreign_keys = ON');

// Reset all auto-increment sequences
DB::statement("DELETE FROM sqlite_sequence");

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
