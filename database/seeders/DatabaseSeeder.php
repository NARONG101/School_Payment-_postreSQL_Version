<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PaymentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Admin credentials are read from environment variables so they can be
     * set securely in Render's environment settings without hardcoding them.
     */
    public function run(): void
    {
        // ── Admin user ────────────────────────────────────────
        $adminEmail    = env('ADMIN_EMAIL',    'admin@school.ck');
        $adminPassword = env('ADMIN_PASSWORD', 'CK_admin168!');
        $adminName     = env('ADMIN_NAME',     'Admin User');

        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name'     => $adminName,
                'password' => Hash::make($adminPassword),
                'role'     => 'admin',
            ]
        );
        
        // ── Receipts user ─────────────────────────────────────
        $receiptsEmail    = env('Receipts_Email',    'receipts@school.ck');
        $receiptsPassword = env('Receipts_Password', 'ReceiptsCK123!');
        $receiptsName     = env('Receipts_User',     'Receipts User');

        $receiptsUser = User::firstOrNew(['email' => $receiptsEmail]);
        $receiptsUser->name = $receiptsName;
        $receiptsUser->password = Hash::make($receiptsPassword);
        $receiptsUser->role = 'receipts';
        $receiptsUser->save();

        // ── Default payment types ─────────────────────────────
        $paymentTypes = [
            [
                'name'        => 'Monthly Tuition',
                'description' => 'Regular monthly tuition fee',
                'amount'      => 0,
                'is_active'   => true,
            ],
            [
                'name'        => 'Registration Fee',
                'description' => 'One-time registration fee',
                'amount'      => 50,
                'is_active'   => true,
            ],
            [
                'name'        => 'Books & Materials',
                'description' => 'Books and learning materials',
                'amount'      => 100,
                'is_active'   => true,
            ],
        ];

        foreach ($paymentTypes as $type) {
            PaymentType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
