<?php
/**
 * Scan PHPStan result files for "Access to an undefined property App\Models\X::$y"
 * errors, then add @property annotations to model files that are missing them.
 *
 * Usage: php utils/add_missing_properties.php [--dry-run]
 */

$dryRun = in_array('--dry-run', $argv ?? []);
$resultsDir = __DIR__ . '/../storage/phpstan_results';
$appDir = __DIR__ . '/../app';

// 1. Parse all error files to collect Model::$property pairs
$missing = []; // ['ModelClass' => ['prop1' => 'mixed', 'prop2' => 'mixed', ...]]

foreach (glob("{$resultsDir}/*.txt") as $file) {
    $content = file_get_contents($file);
    // Match patterns like: App\Models\Coupon::$limit
    if (preg_match_all('/App\\\\Models\\\\([A-Za-z]+)::\$([a-z_]+)/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $class = $m[1];
            $prop = $m[2];
            if (!isset($missing[$class])) {
                $missing[$class] = [];
            }
            $missing[$class][$prop] = true;
        }
    }
}

echo "Found " . count($missing) . " models with missing properties\n";

// 2. For each model, find the PHP file and check existing @property annotations
$modelFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator("{$appDir}/Models", RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $fileInfo) {
    if ($fileInfo->getExtension() !== 'php') continue;
    $content = file_get_contents($fileInfo->getPathname());
    // Extract class name
    if (preg_match('/class\s+(\w+)\s/', $content, $cm)) {
        $modelFiles[$cm[1]] = $fileInfo->getPathname();
    }
}

// Common column types based on naming conventions
function guessType(string $prop): string {
    $intProps = ['id', 'user_id', 'employee_id', 'created_by', 'branch_id', 'department_id',
        'designation_id', 'project_id', 'pipeline_id', 'stage_id', 'category_id',
        'customer_id', 'vendor_id', 'product_id', 'warehouse_id', 'order_id',
        'invoice_id', 'bill_id', 'payment_id', 'plan_id', 'coupon_id', 'tax_id',
        'lead_id', 'deal_id', 'account_id', 'goal_type_id', 'unit_id',
        'support_id', 'contract_id', 'trainer_id', 'training_type_id',
        'purchase_id', 'proposal_id', 'budget_id', 'document_id',
        'rating', 'is_active', 'is_required', 'status', 'type', 'quantity',
        'number_of_days', 'parent_id', 'award_type_id', 'complaint_id',
        'transfer_id', 'from_warehouse_id', 'to_warehouse_id',
        'job_id', 'job_stage_id', 'loan_id', 'appraisal_id',
        'assigned_to', 'assign_to', 'permission_id', 'role_id'];
    $floatProps = ['amount', 'price', 'salary', 'rate', 'discount', 'total',
        'subtotal', 'tax_amount', 'opening_balance', 'gross_salary', 'net_salary',
        'basic_salary', 'total_amount', 'due_amount', 'paid_amount', 'balance',
        'allowance_amount', 'deduction_amount', 'limit', 'commission_amount'];
    $dateProps = ['date', 'start_date', 'end_date', 'due_date', 'created_at',
        'updated_at', 'deleted_at', 'clock_in', 'clock_out', 'joining_date',
        'expiry_date', 'issue_date', 'send_date', 'from', 'to',
        'last_login_at', 'email_verified_at'];
    $stringDefault = ['name', 'title', 'email', 'phone', 'address', 'city',
        'state', 'country', 'zip', 'description', 'notes', 'password',
        'slug', 'code', 'color', 'logo', 'image', 'avatar', 'url',
        'subject', 'body', 'content', 'message', 'label', 'lang'];

    if (in_array($prop, $intProps) || str_ends_with($prop, '_id')) {
        return 'int|null';
    }
    if (in_array($prop, $floatProps)) {
        return 'float|null';
    }
    if (in_array($prop, $dateProps)) {
        return 'string|null';
    }
    // Shipping/billing fields are strings
    if (str_starts_with($prop, 'shipping_') || str_starts_with($prop, 'billing_')) {
        return 'string|null';
    }
    if (in_array($prop, $stringDefault)) {
        return 'string|null';
    }
    // Log type etc.
    if (str_ends_with($prop, '_type') || str_ends_with($prop, '_status')) {
        return 'string|null';
    }
    return 'mixed';
}

$totalAdded = 0;
$filesModified = 0;

foreach ($missing as $class => $props) {
    if (!isset($modelFiles[$class])) {
        echo "  SKIP: {$class} — file not found\n";
        continue;
    }

    $filePath = $modelFiles[$class];
    $content = file_get_contents($filePath);

    // Extract existing @property annotations
    $existingProps = [];
    if (preg_match_all('/@property\s+\S+\s+\$(\w+)/', $content, $em)) {
        foreach ($em[1] as $ep) {
            $existingProps[$ep] = true;
        }
    }

    // Also check $fillable array for properties that exist as columns
    $fillableProps = [];
    if (preg_match('/(protected|public)\s+\$fillable\s*=\s*\[(.*?)\]/s', $content, $fm)) {
        if (preg_match_all("/['\"](\w+)['\"]/", $fm[2], $fpm)) {
            foreach ($fpm[1] as $fp) {
                $fillableProps[$fp] = true;
            }
        }
    }

    // Determine which properties need to be added
    $toAdd = [];
    foreach (array_keys($props) as $prop) {
        if (!isset($existingProps[$prop])) {
            $type = guessType($prop);
            $toAdd[$prop] = $type;
        }
    }

    if (empty($toAdd)) {
        continue;
    }

    // Build the @property block
    $annotations = [];
    ksort($toAdd);
    foreach ($toAdd as $prop => $type) {
        $annotations[] = " * @property {$type} \${$prop}";
    }
    $annotationBlock = implode("\n", $annotations);

    // Insert into existing doc block or create new one
    if (preg_match('/^(\/\*\*.*?\*\/)\s*\n(\s*(final\s+|abstract\s+)?class\s+' . preg_quote($class) . '\b)/sm', $content, $classMatch, PREG_OFFSET_CAPTURE)) {
        // Has existing doc block — insert before closing */
        $docBlock = $classMatch[1][0];
        $docEnd = strrpos($docBlock, ' */');
        if ($docEnd !== false) {
            $newDocBlock = substr($docBlock, 0, $docEnd) . "\n" . $annotationBlock . "\n" . substr($docBlock, $docEnd);
            $content = substr_replace($content, $newDocBlock, $classMatch[1][1], strlen($docBlock));
        }
    } else {
        // No doc block — create one before class declaration
        if (preg_match('/^(\s*(final\s+|abstract\s+)?class\s+' . preg_quote($class) . '\b)/m', $content, $classDecl, PREG_OFFSET_CAPTURE)) {
            $newDoc = "/**\n" . $annotationBlock . "\n */\n";
            $content = substr_replace($content, $newDoc, $classDecl[0][1], 0);
        }
    }

    if ($dryRun) {
        echo "  DRY-RUN: {$class} would add " . count($toAdd) . " props: " . implode(', ', array_keys($toAdd)) . "\n";
    } else {
        file_put_contents($filePath, $content);
        echo "  UPDATED: {$class} — added " . count($toAdd) . " @property annotations\n";
    }

    $totalAdded += count($toAdd);
    $filesModified++;
}

echo "\n{$filesModified} files " . ($dryRun ? "would be " : "") . "modified, {$totalAdded} @property annotations " . ($dryRun ? "would be " : "") . "added\n";
