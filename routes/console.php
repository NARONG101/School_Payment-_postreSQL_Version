<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily database backup
Schedule::command('db:backup')->dailyAt('02:00')->timezone('Asia/Phnom_Penh')->withoutOverlapping();

// Schedule daily auto-pay for 100% discount students
Schedule::command('students:auto-pay-100-discount')->dailyAt('02:30')->timezone('Asia/Phnom_Penh')->withoutOverlapping();
