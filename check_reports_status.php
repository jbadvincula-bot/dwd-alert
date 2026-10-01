<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$today = now()->toDateString();

print("=== DATABASE STATUS CHECK ===\n\n");

// Check all reports
print("All Reports in Database:\n");
$allReports = App\Models\WaterInterruptionReport::with(['users:id,email,role'])
    ->orderByDesc('created_at')
    ->limit(20)
    ->get();

foreach ($allReports as $report) {
    $todayFlag = $report->created_at->toDateString() === $today ? " (TODAY)" : "";
    print("#{$report->id}: {$report->title}{$todayFlag}\n");
    print("  Created: {$report->created_at}\n");
    print("  Users attached: {$report->users->count()}\n");
    print("\n");
}

print("=== PIVOT TABLE STATUS ===\n\n");

$pivotRecords = DB::table('water_interruption_report_user')
    ->orderByDesc('created_at')
    ->limit(20)
    ->get();

foreach ($pivotRecords as $pivot) {
    $report = DB::table('water_interruption_reports')->find($pivot->report_id);
    $user = DB::table('users')->find($pivot->user_id);
    
    print("Report ID: " . $pivot->report_id);
    if ($report) print(" - " . $report->title);
    print(" - User: " . $pivot->user_id . " (" . ($user->email ?? 'N/A') . ")");
    print(" - Created: " . $pivot->created_at . "\n");
}

print("\n=== TESTING RESIDENT QUERY ===\n\n");

// Simulate exact resident dashboard query
$residentUser = App\Models\User::find(133);
print("Resident User ID: " . $residentUser->id . "\n");
print("Resident User Email: " . $residentUser->email . "\n\n");

$residentReports = App\Models\WaterInterruptionReport::query()
    ->whereHas('users', fn ($q) => $q->where('users.id', $residentUser->id))
    ->orderByDesc('created_at')
    ->get();

print("Reports for resident user (via pivot):\n");
foreach ($residentReports as $report) {
    $todayFlag = $report->created_at->toDateString() === $today ? " (TODAY)" : "";
    print("#{$report->id}: {$report->title}{$todayFlag}\n");
    print("  Created: {$report->created_at}\n");
    print("  Users attached: {$report->users->count()}\n");
    print("\n");
}

print("\n=== TESTING ADMIN QUERY ===\n\n");

$adminReports = App\Models\WaterInterruptionReport::query()
    ->with(['users', 'comments.user', 'assignee'])
    ->where('status', '!=', 'resolved')
    ->where(function ($q) {
        $q->whereBetween('latitude', [14.2675, 14.3937])
            ->whereBetween('longitude', [120.8683, 121.0167])
            ->orWhere(function ($q) {
                $q->whereNull('latitude')
                    ->whereNull('longitude');
            });
    })
    ->latest()
    ->get();

print("Admin dashboard reports:\n");
foreach ($adminReports as $report) {
    $todayFlag = $report->created_at->toDateString() === $today ? " (TODAY)" : "";
    print("#{$report->id}: {$report->title}{$todayFlag}\n");
    print("  Created: {$report->created_at}\n");
    print("  Users attached: {$report->users->count()}\n");
    print("\n");
}
