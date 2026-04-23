# Super Admin Full Module Access — Shell Commands

## Date: 2025-06-09

```bash
# Find test files related to modified controllers
find tests -name "*Deal*" -o -name "*Project*" -o -name "*Task*" -o -name "*Permission*" -o -name "*SuperAdmin*" -o -name "*Admin*" -o -name "*Auth*"

# List test files
find tests -name "*Test.php" | head -30
```
