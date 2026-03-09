<?php
/**
 * Adds 'id' => (string)\Illuminate\Support\Str::uuid() to all
 * DB::table('settings')->insertOrIgnore([ ... ]) calls that don't already have 'id'.
 */

$file = __DIR__ . '/tests/Unit/app/Models/utils/UtilityTest.php';
$lines = file($file);
$output = [];
$addIdToNextLine = false;
$count = 0;

foreach ($lines as $i => $line) {
    if ($addIdToNextLine) {
        // Get indentation from current line
        preg_match('/^(\s+)/', $line, $m);
        $indent = $m[1] ?? "\t\t\t";
        $output[] = $indent . "'id' => (string)\\Illuminate\\Support\\Str::uuid(),\n";
        $output[] = $line;
        $addIdToNextLine = false;
        $count++;
        continue;
    }

    // Match DB::table('settings')->insertOrIgnore([ or ->insert([
    if (preg_match("/DB::table\('settings'\)->insert(?:OrIgnore)?\(\[/", $line)) {
        // Check if this line or nearby already has 'id'
        $hasId = false;
        // Check current line for 'id'
        if (preg_match("/'id'\s*=>/", $line)) {
            $hasId = true;
        }
        // Check next few lines for 'id' (multiline array)
        if (!$hasId) {
            for ($j = $i + 1; $j < min($i + 8, count($lines)); $j++) {
                if (preg_match("/'id'\s*=>/", $lines[$j])) {
                    $hasId = true;
                    break;
                }
                if (preg_match('/\]\);/', $lines[$j])) {
                    break; // end of array
                }
            }
        }

        if (!$hasId) {
            // Check if single-line (has ]); on same line)
            if (preg_match('/\]\);/', $line)) {
                // Single-line insert: add id at start of array
                $line = preg_replace(
                    "/(->insert(?:OrIgnore)?\(\[)/",
                    "$1'id' => (string)\\Illuminate\\Support\\Str::uuid(), ",
                    $line
                );
                $count++;
            } else {
                // Multi-line: add id on next line
                $addIdToNextLine = true;
            }
        }
    }

    $output[] = $line;
}

file_put_contents($file, implode('', $output));
echo "Added 'id' to $count settings insert calls.\n";
