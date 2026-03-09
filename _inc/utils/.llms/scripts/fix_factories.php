<?php
/**
 * Fix missing factories:
 * 1. Create stub factory files for models that have HasFactory but no factory file
 * 2. Add HasFactory trait to models that lack it
 */

$baseDir = __DIR__;
$factoryDir = $baseDir . '/database/factories';
$modelDir = $baseDir . '/app/Models';

// ============ PART 1: Create missing factory files ============

$missingFactories = [
    'Lead', 'Pos', 'Purchase', 'Deal', 'BillProduct',
    'Stage', 'ChartOfAccount', 'Allowance',
    'Warehouse', 'Support', 'SaturationDeduction', 'OtherPayment', 'Loan',
    'ZoomMeeting', 'Overtime', 'Order', 'InvoiceProduct', 'InvoiceBankTransfer',
    'EmployeeAttendance', 'DebitNote', 'CreditNote', 'Commission',
    'BillAccount', 'AllowanceOption',
    'WarehouseTransfer', 'Transfer', 'Training', 'Trainer',
    'Source', 'PurchasePayment', 'PosPayment', 'PerformanceType',
    'Payment', 'Label',
];

$createdCount = 0;
$skippedCount = 0;

foreach ($missingFactories as $model) {
    $factoryFile = $factoryDir . '/' . $model . 'Factory.php';
    if (file_exists($factoryFile)) {
        echo "SKIP (exists): {$model}Factory.php\n";
        $skippedCount++;
        continue;
    }

    // Verify model exists
    $modelFile = $modelDir . '/' . $model . '.php';
    if (!file_exists($modelFile)) {
        echo "WARN (no model): {$model} — searching...\n";
        // Try to find model in subdirs
        $found = glob($modelDir . '/**/' . $model . '.php');
        if (empty($found)) {
            $found = glob($modelDir . '/*/' . $model . '.php');
        }
        if (!empty($found)) {
            echo "  Found at: " . $found[0] . "\n";
        } else {
            echo "  Model file not found, creating factory anyway\n";
        }
    }

    $content = <<<PHP
<?php

namespace Database\Factories;

use App\Models\\{$model};
use Illuminate\Database\Eloquent\Factories\Factory;

class {$model}Factory extends Factory
{
    protected \$model = {$model}::class;

    public function definition(): array
    {
        return [];
    }
}
PHP;

    file_put_contents($factoryFile, $content);
    echo "CREATED: {$model}Factory.php\n";
    $createdCount++;
}

echo "\n--- Part 1 Summary: Created {$createdCount} factories, Skipped {$skippedCount} ---\n\n";

// ============ PART 2: Add HasFactory trait to models ============

$modelsNeedingTrait = [
    'Project', 'Transaction', 'PayslipType', 'Tax', 'StockReport',
    'Pipeline', 'JobCategory', 'InvoicePayment', 'Coupon',
    'Job', 'Bug', 'BugComment',
    'UserCoupon', 'LoanOption', 'JobStage', 'JobApplication',
    'IpRestrict', 'Expense', 'EmployeeDocument', 'EmailTemplate',
    'DeductionOption', 'CompanyPolicy', 'ClientPermission',
    'BugStatus', 'BugFile', 'BankTransfer',
];

$traitAdded = 0;
$traitSkipped = 0;

foreach ($modelsNeedingTrait as $model) {
    $modelFile = $modelDir . '/' . $model . '.php';
    if (!file_exists($modelFile)) {
        echo "SKIP (no file): {$model}.php\n";
        $traitSkipped++;
        continue;
    }

    $content = file_get_contents($modelFile);

    // Check if already has HasFactory
    if (strpos($content, 'HasFactory') !== false) {
        echo "SKIP (has trait): {$model}.php\n";
        $traitSkipped++;
        continue;
    }

    // Also need to check if factory file exists; if not, create it
    $factoryFile = $factoryDir . '/' . $model . 'Factory.php';
    if (!file_exists($factoryFile)) {
        $factoryContent = <<<PHP
<?php

namespace Database\Factories;

use App\Models\\{$model};
use Illuminate\Database\Eloquent\Factories\Factory;

class {$model}Factory extends Factory
{
    protected \$model = {$model}::class;

    public function definition(): array
    {
        return [];
    }
}
PHP;
        file_put_contents($factoryFile, $factoryContent);
        echo "  CREATED factory: {$model}Factory.php\n";
    }

    // Add HasFactory use statement if not present
    $hasImport = (strpos($content, 'use Illuminate\Database\Eloquent\Factories\HasFactory;') !== false);

    if (!$hasImport) {
        // Add import after namespace or after last use statement
        if (preg_match('/^(namespace\s+[^;]+;\s*\n)/m', $content, $m, PREG_OFFSET_MATCH)) {
            $insertPos = $m[0][1] + strlen($m[0][0]);
            // Check if there are existing use statements after namespace
            $afterNamespace = substr($content, $insertPos);
            if (preg_match('/^((?:use\s+[^;]+;\s*\n)+)/m', $afterNamespace, $useBlock, PREG_OFFSET_MATCH)) {
                // Insert after last use statement
                $insertPos = $insertPos + $useBlock[0][1] + strlen($useBlock[0][0]);
                $content = substr($content, 0, $insertPos) .
                    "use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;\n" .
                    substr($content, $insertPos);
            } else {
                // Insert right after namespace
                $content = substr($content, 0, $insertPos) .
                    "\nuse Illuminate\\Database\\Eloquent\\Factories\\HasFactory;\n" .
                    substr($content, $insertPos);
            }
        }
    }

    // Add HasFactory trait usage inside the class
    // Find the class opening and look for existing "use" trait statements
    if (preg_match('/class\s+\w+\s+extends\s+[^\{]+\{/s', $content, $classMatch, PREG_OFFSET_MATCH)) {
        $classBodyStart = $classMatch[0][1] + strlen($classMatch[0][0]);
        $afterClassOpen = substr($content, $classBodyStart);

        // Check if there's an existing "use SomeTrait;" line
        if (preg_match('/^(\s*)(use\s+[^;]+;)/m', $afterClassOpen, $traitMatch, PREG_OFFSET_MATCH)) {
            // Add HasFactory to the existing trait use
            $existingTraitLine = $traitMatch[2][0];
            $indent = $traitMatch[1][0];

            // Insert HasFactory use before existing trait use
            $traitInsertPos = $classBodyStart + $traitMatch[0][1];
            $content = substr($content, 0, $traitInsertPos) .
                $indent . "use HasFactory;\n" .
                substr($content, $traitInsertPos);
        } else {
            // No existing trait use, add after class opening brace
            $content = substr($content, 0, $classBodyStart) .
                "\n    use HasFactory;\n" .
                substr($content, $classBodyStart);
        }
    }

    file_put_contents($modelFile, $content);
    echo "ADDED HasFactory: {$model}.php\n";
    $traitAdded++;
}

echo "\n--- Part 2 Summary: Added HasFactory to {$traitAdded} models, Skipped {$traitSkipped} ---\n";
echo "\nDone! Run 'composer dump-autoload -o' to pick up new factories.\n";
