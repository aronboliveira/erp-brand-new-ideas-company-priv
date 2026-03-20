# Grep Commands — 2026-03-10

## Controller method discovery
- `grep -n "function create\|function store\|function index" app/Http/Controllers/PayslipController.php | head -10`: payslip controller methods
- `grep -n "stock_export\|stockExport\|function.*export" app/Http/Controllers/ReportController.php | head -10`: report export methods
- `grep -n "function webhook\b\|function webhookCreate\|Route.*settings" app/Http/Controllers/SystemController.php | head -10`: system controller webhooks
- `grep -n "function " app/Http/Controllers/Bills/PayslipController.php | head -20`: payslip controller all methods
- `grep -n "function " app/Http/Controllers/Activity/ReportController.php | head -20`: activity report methods
- `grep -n "function " app/Http/Controllers/Configs/SystemController.php | head -30`: system config methods
- `grep -n "function.*webhook\|function.*settings" app/Http/Controllers/Configs/SystemController.php | head -20`: system webhook+settings
- `grep -n "function " app/Http/Controllers/Activity/PerformanceTypeController.php | head -20`: performance type methods
- `grep -n "return\|view(" app/Http/Controllers/Configs/SystemController.php | grep -A2 "1025\|1030\|1035\|1040\|1045" | head -10`: system ctrl returns

## Class discovery
- `grep -rn "class ViewsConstants" app/Config/Constants/ViewsConstants.php | head -2`: ViewsConstants class location
