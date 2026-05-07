<?php

namespace App\Models;

// * DEV-ONLY TEST CLONE
// Original: app/Models/utils/Utility.php (App\Models\Utility)
// Purpose: Thin proxy class extending Utility so SafeAliasMock-dependent
//          tests can use aliasMock() without conflicting with TestCase's
//          compile-time import of App\Models\Utility.
// Usage:   Import this class in tests instead of Utility. All static
//          methods are inherited from Utility.
// Do NOT use in production code.

class UtilProxy extends \App\Models\Utility
{
}
