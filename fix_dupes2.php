<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;

// Find the duplicate
$dupes = Payment::select('student_id', 'due_date')
    ->selectRaw('COUNT(*) as cnt, MIN(id) as min_id, MAX(id) as max_id')
    ->groupBy('student_id', 'due_date')
    ->havingRaw('COUNT(*) > 1')
    ->get();

foreach ($dupes as $d) {
    echo "Duplicate: student_id={$d->student_id} due_date={$d->due_date} count={$d->cnt}\n";
    $payments = Payment::where('student_id', $d->student_id)
        ->where('due_date', $d->due_date)
        ->orderByDesc('next_payment_date')
        ->orderByDesc('id')
        ->get();
    foreach ($payments as $p) {
        echo "  #{$p->id} next={$p->next_payment_date} receipt={$p->receipt_number} notes={$p->notes}\n";
    }
    // Keep the one with the highest next_payment_date (highest id as tiebreaker), delete the rest
    $keep = $payments->first();
    foreach ($payments->skip(1) as $p) {
        echo "  Deleting #{$p->id}\n";
        $p->forceDelete();
    }
    echo "  Kept #{$keep->id}\n";
}

echo "\nDone. Remaining duplicates: ";
echo Payment::select('student_id','due_date')->selectRaw('COUNT(*) as cnt')
    ->groupBy('student_id','due_date')->havingRaw('COUNT(*)>1')->count();
echo "\n";
