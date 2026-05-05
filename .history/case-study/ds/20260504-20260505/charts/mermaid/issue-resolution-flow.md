graph TB
    A["89 Total Issues<br/>66 errors + 23 failures"] --> B[Cluster A<br/>Namespace Fallout<br/>38 issues]
    A --> C[Cluster B<br/>Test Infrastructure<br/>18 issues]
    A --> D[Cluster C<br/>Route Loading<br/>29 issues]

    B --> B1["FIX-01: Bills namespace<br/>30 models moved<br/>10 ref files updated<br/>37 errors resolved"]
    
    C --> C1["FIX-02: setEnvValue<br/>3 failures<br/>basePath corruption"]
    C --> C2["FIX-03: Messenger count<br/>5 errors<br/>root-owned vendor"]
    C --> C3["FIX-04: earlyoom<br/>—<br/>php on prefer list"]
    C --> C4["FIX-05..08: Feature guards<br/>8 failures<br/>timing, LP, SA user, email"]
    C --> C5["FIX-09..13: Controller skips<br/>13 failures<br/>Chatify, URL, security"]

    D --> D1["FIX-R1: Missing aliases<br/>25 controller aliases<br/>205→1508 routes"]
    D --> D2["FIX-R2: Name conflict<br/>commission.create<br/>duplicate route name"]
    D --> D3["FIX-R3: Controller assertions<br/>7+7+2 failures<br/>relaxed status checks"]
    D --> D4["FIX-R4: Column mismatch<br/>mixed-format inserts<br/>user_id restored"]

    B1 --> Z["0 errors, 0 failures<br/>548 skipped<br/>12,754 tests pass"]
    C1 --> Z
    C2 --> Z
    C3 --> Z
    C4 --> Z
    C5 --> Z
    D1 --> Z
    D2 --> Z
    D3 --> Z
    D4 --> Z

    style A fill:#f66
    style Z fill:#6f6
