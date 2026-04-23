# DEVE SER EXECUTADO NA ROOT

# Move done models
lsrf _inc/django/erp_prestech_backend/app/models 1 "*.py" | % { $php = $_.Name -replace "\.py$", ".php"; if (test-path "_old/app/Models/$php") { mv "_old/app/Models/$php" "_old/app/Models/done/$php" -Force } };
# Check models
lsrf _inc/django/erp_prestech_backend/app/models 1 "*.py" | % { echo $($_.Name -replace ".py", ".php" ) };
# Move done controllers
lsrf _inc/django/erp_prestech_backend/app/Http/Controllers 1 "*.py" | % { $php = $_.Name -replace "\.py$", ".php"; if (test-path "_old/app/Http/_Controllers/$php") { mv "_old/app/Http/_Controllers/$php" "_old/app/Http/_Controllers/done/$php" -Force } };
# Output for tree
lsrf . 1 | select -ExpandProperty FullName | foreach { $_ -replace "[###REL PATH###]\\_inc\\django\\", "" } 