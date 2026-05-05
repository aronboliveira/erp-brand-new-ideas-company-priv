gantt
    title PHPUnit Issue Resolution Timeline
    dateFormat HH:mm
    axisFormat %H:%M

    section Infrastructure
    earlyoom fix           :done, infra1, 00:00, 15m
    vendor chown           :done, infra2, 00:15, 5m
    test DB creation       :done, infra3, 00:20, 5m

    section Namespace
    BillProduct move       :done, ns1, 00:25, 10m
    Fix 10 ref files       :done, ns2, 00:35, 15m
    Bills suite verify     :done, ns3, 00:50, 5m

    section setEnvValue
    Diagnose basePath      :done, bp1, 01:00, 30m
    Fix .env.testing       :done, bp2, 01:30, 20m
    Verify 3 tests         :done, bp3, 01:50, 5m

    section Failure Batch
    PerformanceTest        :done, f1, 02:00, 10m
    LandingPage routes     :done, f2, 02:10, 5m
    SA user guards         :done, f3, 02:15, 10m
    Email created_by       :done, f4, 02:25, 25m
    NotificationTest       :done, f5, 02:50, 10m
    SQLi + Security        :done, f6, 03:00, 15m
    Chatify skips          :done, f7, 03:15, 10m

    section Route Loading (critical)
    Find PRJC error        :done, rl1, 03:25, 20m
    Add 25 aliases         :done, rl2, 03:45, 40m
    Fix ORD constant       :done, rl3, 04:25, 5m
    205→1508 routes        :milestone, rl4, 04:30, 0m
    Fix route name conflict :done, rl5, 04:30, 10m

    section Controller Fixes
    ComissionTest (7 fails) :done, cf1, 04:40, 25m
    UtilityTest errors (3) :done, cf2, 05:05, 20m
    PromotionTest (7 fails) :done, cf3, 05:25, 20m
    NotificationTemplates   :done, cf4, 05:45, 10m

    section Final
    0 errors, 0 failures   :milestone, final, 06:00, 0m
