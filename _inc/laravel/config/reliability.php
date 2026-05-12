<?php

return [
    'dispatch_orchestration' => [
        'enabled' => env('RELIABILITY_DISPATCH_ORCHESTRATION_ENABLED', false),
        'limit' => (int) env('RELIABILITY_DISPATCH_ORCHESTRATION_LIMIT', 50),
        'compensation_limit' => (int) env('RELIABILITY_DISPATCH_ORCHESTRATION_COMPENSATION_LIMIT', 25),
        'include_compensation' => env('RELIABILITY_DISPATCH_ORCHESTRATION_INCLUDE_COMPENSATION', true),
        'without_overlapping_minutes' => (int) env('RELIABILITY_DISPATCH_ORCHESTRATION_OVERLAP_MINUTES', 10),
        'on_one_server' => env('RELIABILITY_DISPATCH_ORCHESTRATION_ON_ONE_SERVER', false),
        'run_in_background' => env('RELIABILITY_DISPATCH_ORCHESTRATION_BACKGROUND', false),
    ],
];
