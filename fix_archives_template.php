<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Read and fix the Blade template
$bladeContent = file_get_contents('resources/views/admin/archives/index.blade.php');

// Fix 1: Remove duplicate form sections (the second copy)
// Find the problematic area around line 274-304

// The issue is that there are duplicate form sections in the operational staff tab
// The file structure should be:

// Line 253-394: Operational Staff Tab
// Line 254: <div class="p-6"> starts
// Line 395: @endif closes
// Line 396: </div> closes
// Line 397: @endif - This is the problem! This should be removed

// Lines 274-304 contain a duplicate form that shouldn't be there
// This is because the original template had a second form by mistake

$fixedContent = preg_replace_callback(
    '/@if\(\$tab == \'operational-staff\'\)\s+<div class="p-6">.*?(?=\s+@endif)/s',
    function ($matches) {
        $content = $matches[0];
        
        // Remove duplicate form sections (the second copy)
        // Replace the duplicate form with just the filters and table
        $content = preg_replace(
            '/<form method="GET" class="mb-6">.*?(?=<\/form>)/s',
            '<form method="GET" class="mb-6">\n                    <input type="hidden" name="tab" value="operational-staff">\n                    <div class="flex flex-wrap gap-4 items-end">\n                        <div class="flex-1 min-w-[200px]\">\n                            <label class="block text-xs font-medium text-gray-400 mb-1">Search</label>\n                            <input type="text" name="search_operational_staff" class="w-full border border-white\/20 bg-white\/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-400 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500" placeholder="Search operational staff..." value="{{ request(\'search_operational_staff\') }}">\n                        <\/div>\n\n                        <div class="w-40">\n                            <label class="block text-xs font-medium text-gray-400 mb-1">Status</label>\n                            <select name="operational_staff_status" class="w-full border border-white\/20 bg-white\/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">\n                                <option value=\"\">All Status<\/option>\n                                <option value=\"active\" {{ request(\'operational_staff_status\') == \'active\' ? \'selected\' : \'\' }}>Active<\/option>\n                                <option value=\"inactive\" {{ request(\'operational_staff_status\') == \'inactive\' ? \'selected\' : \'\' }}>Inactive<\/option>\n                                <option value=\"suspended\" {{ request(\'operational_staff_status\') == \'suspended\' ? \'selected\' : \'\' }}>Suspended<\/option>\n                            <\/select>\n                        <\/div>\n\n                        <div class="w-40">\n                            <label class="block text-xs font-medium text-gray-400 mb-1">Staff Type<\/label>\n                            <select name="operational_staff_type" class="w-full border border-white\/20 bg-white\/10 rounded-lg px-3 py-2.5 text-sm text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">\n                                <option value=\"\">All Staff Types<\/option>\n                                @foreach($operationalStaffTypes as $type)\n                                    <option value=\"{{$type}}\" {{ request(\'operational_staff_type\') == $type ? \'selected\' : \'\' }}>\n                                        {{ ucfirst(str_replace(\'_\', \' \', $type)) }}\n                                    <\/option>\n                                @endforeach\n                            <\/select>\n                        <\/div>\n                        <div class="w-28">\n                            <button type="submit" class="w-full bg-cyan-600 text-white px-4 py-2.5 rounded-lg hover:bg-cyan-700 transition text-sm font-medium">\n                                Filter\n                            <\/button>\n                        <\/div>\n                    <\/div>\n                <\/form>',
            $content
        );
        
        return $content;
    },
    $bladeContent
);

// Fix 2: Remove the extra @endif at line 397
$fixedContent = preg_replace('/^\s*@endif\s*\n\s*@endif\s*\n/', '@endif\n', $fixedContent);

// Write the fixed content back
file_put_contents('resources/views/admin/archives/index.blade.php', $fixedContent);

print("Fixed Blade template. Removed duplicate form sections and extra @endif token.\n");
