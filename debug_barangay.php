<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Barangay;

$barangays = Barangay::where('name', 'LIKE', '%Santo N%')->orWhere('name', 'LIKE', '%Santo Cr%')->get(['name']);
foreach ($barangays as $b) {
    echo "DB: " . $b->name . " (hex: " . bin2hex($b->name) . ")\n";
}

$allBarangays = Barangay::pluck('name')->toArray();
echo "Total barangays in DB: " . count($allBarangays) . "\n";

$testName = 'Santo Niño I';
echo "Test name hex: " . bin2hex($testName) . "\n";
echo "Test name in DB: " . (in_array($testName, $allBarangays) ? "YES" : "NO") . "\n";

$testName2 = 'Santo Niño I';
echo "Test name 2 hex: " . bin2hex($testName2) . "\n";
echo "Test name 2 in DB: " . (in_array($testName2, $allBarangays) ? "YES" : "NO") . "\n";

// Find the matching one
foreach ($allBarangays as $b) {
    if (str_contains($b, 'Santo N')) {
        echo "Found: '$b' hex: " . bin2hex($b) . "\n";
    }
}
