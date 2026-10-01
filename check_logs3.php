<?php
$lines = file('storage/logs/laravel.log');
foreach ($lines as $line) {
    if (strpos($line, '2026-07-31 19:0') !== false || strpos($line, '2026-07-31 18:1') !== false || strpos($line, '2026-07-31 18:') !== false || strpos($line, '2026-07-31 19:') !== false) {
        echo $line;
    }
}
