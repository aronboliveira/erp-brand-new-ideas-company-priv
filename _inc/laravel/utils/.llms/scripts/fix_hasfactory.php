<?php
$models = [
    'Project'          => 'app/Models/Planning/Project.php',
    'Transaction'      => 'app/Models/Bills/Transaction.php',
    'PayslipType'      => 'app/Models/Bills/PayslipType.php',
    'Tax'              => 'app/Models/Bills/Tax.php',
    'StockReport'      => 'app/Models/Bills/StockReport.php',
    'Pipeline'         => 'app/Models/Configs/Pipeline.php',
    'JobCategory'      => 'app/Models/Companies/JobCategory.php',
    'InvoicePayment'   => 'app/Models/Bills/InvoicePayment.php',
    'Coupon'           => 'app/Models/Bills/Coupon.php',
    'Job'              => 'app/Models/Companies/Job.php',
    'Bug'              => 'app/Models/Bugs/Bug.php',
    'BugComment'       => 'app/Models/Bugs/BugComment.php',
    'UserCoupon'       => 'app/Models/Bills/UserCoupon.php',
    'LoanOption'       => 'app/Models/Bills/LoanOption.php',
    'JobStage'         => 'app/Models/Individuals/JobStage.php',
    'JobApplication'   => 'app/Models/Individuals/JobApplication.php',
    'IpRestrict'       => 'app/Models/Configs/IpRestrict.php',
    'Expense'          => 'app/Models/Bills/Expense.php',
    'EmployeeDocument' => 'app/Models/Shapes/EmployeeDocument.php',
    'EmailTemplate'    => 'app/Models/Contact/EmailTemplate.php',
    'DeductionOption'  => 'app/Models/Bills/DeductionOption.php',
    'CompanyPolicy'    => 'app/Models/Companies/CompanyPolicy.php',
    'ClientPermission' => 'app/Models/Configs/ClientPermission.php',
    'BugStatus'        => 'app/Models/Bugs/BugStatus.php',
    'BugFile'          => 'app/Models/Bugs/BugFile.php',
    'BankTransfer'     => 'app/Models/Bills/BankTransfer.php',
];

$factoryDir = 'database/factories';
$done = 0;

foreach ($models as $name => $path) {
    $c = file_get_contents($path);
    if (strpos($c, 'HasFactory') !== false) {
        echo "SKIP $name (already has HasFactory)\n";
        continue;
    }

    // Add import after last existing use statement
    if (preg_match('/(namespace\s+[^;]+;\s*\n)((?:use\s+[^;]+;\s*\n)*)/', $c, $m, PREG_OFFSET_CAPTURE)) {
        $afterUses = $m[0][1] + strlen($m[0][0]);
        $c = substr($c, 0, $afterUses)
           . "use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;\n"
           . substr($c, $afterUses);
    }

    // Add trait inside class body
    if (preg_match('/class\s+\w+\s+extends\s+[^\{]+\{/s', $c, $cm, PREG_OFFSET_CAPTURE)) {
        $pos = $cm[0][1] + strlen($cm[0][0]);
        $after = substr($c, $pos);
        if (preg_match('/^(\s*)(use\s+)/m', $after, $tm, PREG_OFFSET_CAPTURE)) {
            $insertAt = $pos + $tm[0][1];
            $indent = $tm[1][0];
            $c = substr($c, 0, $insertAt) . $indent . "use HasFactory;\n" . substr($c, $insertAt);
        } else {
            $c = substr($c, 0, $pos) . "\n    use HasFactory;\n" . substr($c, $pos);
        }
    }

    file_put_contents($path, $c);

    // Create factory if missing
    $ff = "$factoryDir/{$name}Factory.php";
    if (!file_exists($ff)) {
        $fc = "<?php\n\nnamespace Database\\Factories;\n\nuse App\\Models\\{$name};\nuse Illuminate\\Database\\Eloquent\\Factories\\Factory;\n\nclass {$name}Factory extends Factory\n{\n    protected \$model = {$name}::class;\n\n    public function definition(): array\n    {\n        return [];\n    }\n}\n";
        file_put_contents($ff, $fc);
        echo "  +factory: {$name}Factory.php\n";
    }

    echo "DONE: $name\n";
    $done++;
}

echo "\nAdded HasFactory to $done models\n";
