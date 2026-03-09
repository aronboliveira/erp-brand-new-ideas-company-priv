<?php
require __DIR__ . '/vendor/autoload.php';

$content = file_get_contents(__DIR__ . '/routes/web.php');
$lines = explode("\n", $content);

$aliasMap = [];
$currentPrefix = '';

foreach ($lines as $line) {
	$trimmed = trim($line);

	// Match standalone: use Full\Namespace\SomeController as ALIAS;
	if (preg_match('/^use\s+([\w\\\\]+Controller)\s+as\s+(\w+)\s*;/', $trimmed, $m)) {
		$aliasMap[$m[2]] = $m[1];
		continue;
	}

	// Match group start: use App\Http\Controllers\Activity\{
	if (preg_match('/^use\s+([\w\\\\]+)\\\\\{/', $trimmed, $m)) {
		$currentPrefix = $m[1];
		// Also check inline members on same line
		if (preg_match_all('/(\w+Controller)\s+as\s+(\w+)/', $trimmed, $members)) {
			for ($i = 0; $i < count($members[1]); $i++) {
				$aliasMap[$members[2][$i]] = $currentPrefix . '\\' . $members[1][$i];
			}
		}
		if (str_contains($trimmed, '};') || str_contains($trimmed, '}')) {
			if (substr_count($trimmed, '{') <= substr_count($trimmed, '}')) {
				$currentPrefix = '';
			}
		}
		continue;
	}

	// Match members inside group
	if ($currentPrefix && preg_match('/(\w+Controller)\s+as\s+(\w+)/', $trimmed, $m)) {
		$aliasMap[$m[2]] = $currentPrefix . '\\' . $m[1];
	}

	// End of group
	if ($currentPrefix && (str_contains($trimmed, '};') || $trimmed === '}')) {
		$currentPrefix = '';
	}
}

// Extract all Alias::CONST references
preg_match_all('/\b([A-Z][A-Z0-9_]*C)::([A-Z][A-Z0-9_]+)\b/', $content, $refs);

$missing = [];
$checked = [];
for ($i = 0; $i < count($refs[0]); $i++) {
	$alias = $refs[1][$i];
	$const = $refs[2][$i];
	$key = $alias . '::' . $const;
	if (isset($checked[$key])) continue;
	$checked[$key] = true;

	if (!isset($aliasMap[$alias])) continue;
	$fqcn = $aliasMap[$alias];

	try {
		if (!class_exists($fqcn)) {
			$missing[] = "CLASS NOT FOUND: $fqcn (alias $alias)";
			continue;
		}
		$ref = new ReflectionClass($fqcn);
		if (!$ref->hasConstant($const)) {
			$missing[] = "$alias::$const  =>  $fqcn::$const";
		}
	} catch (\Throwable $e) {
		$missing[] = "ERROR loading $fqcn: " . $e->getMessage();
	}
}

if (empty($missing)) {
	echo "All controller constants resolved!\n";
} else {
	echo count($missing) . " MISSING constants:\n";
	foreach ($missing as $m) echo "  $m\n";
}
