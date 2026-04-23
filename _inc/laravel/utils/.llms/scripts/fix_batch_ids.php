<?php
/**
 * Fix: Remove incorrectly placed 'id' lines from batch inserts and add 'id' to each sub-array.
 * A "bad" id line is one where the NEXT line starts with '[' (indicating a sub-array).
 * In those cases, we remove the bad id line and add 'id' => uuid to each sub-array row.
 */

$file = __DIR__ . '/tests/Unit/app/Models/utils/UtilityTest.php';
$lines = file($file);
$output = [];
$removedCount = 0;
$addedCount = 0;

// First pass: identify bad id lines (where next non-empty line starts with '[')
$badLines = [];
for ($i = 0; $i < count($lines); $i++) {
    if (preg_match("/^\s+'id'\s*=>\s*\(string\)\\\\Illuminate\\\\Support\\\\Str::uuid\(\),/", $lines[$i])) {
        // Check if next line starts a sub-array
        if (isset($lines[$i + 1]) && preg_match('/^\s*\[/', $lines[$i + 1])) {
            $badLines[$i] = true;
        }
    }
}

// Second pass: rebuild file
for ($i = 0; $i < count($lines); $i++) {
    // Skip bad id lines
    if (isset($badLines[$i])) {
        $removedCount++;
        continue;
    }

    // For sub-array lines within batch inserts to settings table, add 'id' if missing
    // Check if this line is a sub-array row in a settings batch insert: starts with [ and has 'created_by' or 'name'
    if (preg_match('/^\s+\[.*\'(created_by|name)\'\s*=>/', $lines[$i]) && !preg_match("/'id'\s*=>/", $lines[$i])) {
        // Check if we're inside a settings insertOrIgnore by looking back
        $inSettingsInsert = false;
        for ($j = $i - 1; $j >= max(0, $i - 10); $j--) {
            $prevLine = $lines[$j] ?? '';
            if (isset($badLines[$j])) continue; // skip removed lines
            if (preg_match("/DB::table\('settings'\)->insert/", $prevLine)) {
                $inSettingsInsert = true;
                break;
            }
            if (preg_match('/\]\);/', $prevLine) && !preg_match('/insertOrIgnore/', $prevLine)) {
                break; // end of previous statement
            }
        }

        if ($inSettingsInsert) {
            // Add 'id' => uuid at the start of this sub-array
            $lines[$i] = preg_replace(
                "/^(\s+)\[/",
                "$1['id' => (string)\\Illuminate\\Support\\Str::uuid(), ",
                $lines[$i]
            );
            $addedCount++;
        }
    }

    $output[] = $lines[$i];
}

file_put_contents($file, implode('', $output));
echo "Removed $removedCount bad id lines, added $addedCount ids to sub-arrays.\n";
