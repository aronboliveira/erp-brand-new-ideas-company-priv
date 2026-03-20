# Grep Commands — 2026-03-19

## Blade i18n audit
- `grep -rn 'ac-alert' resources/views/ Modules/ --include='*.blade.php' | head -20`: find custom alert components
- `grep -rn 'aria-label="Close"' resources/views/ Modules/ --include='*.blade.php' | grep -v '{{ __' | grep -v '{!! __'`: untranslated Close labels
- `grep -rn 'aria-label="Toggle navigation"' resources/views/ Modules/ --include='*.blade.php' | grep -v '{{ __' | wc -l`: untranslated Toggle navigation count
- `grep -rn 'aria-label="Close"' resources/views/ Modules/ --include='*.blade.php' | grep -v '{{ __' | wc -l`: Close label count after fix
- `grep -rn '"me-auto">Notice<' resources/views/ Modules/ --include='*.blade.php' | grep -v '{{ __' | wc -l`: untranslated Notice count
- `grep -rn 'me-auto">Notice<' resources/views/ Modules/ --include='*.blade.php' | grep -v '{{ __' | wc -l`: Notice remaining
- `grep -rn "me-auto\">{{ __('Notice') }}" resources/views/ Modules/ --include='*.blade.php' | wc -l`: Notice translated count
- `grep -rn 'aria-label="Toggle navigation"' resources/views/ Modules/ --include='*.blade.php' | grep -v '{{ __' | wc -l`: Toggle nav after fix

## ViewsConstants + LangsConstants analysis
- `grep -oP "const\s+\K[A-Z_]+" app/Config/Constants/ViewsConstants.php | sort -u | wc -l`: count ViewsConstants
- `grep -oP 'ViewsConstants::\K[A-Z_]+' app/Config/Constants/LangsConstants.php | sort -u | wc -l`: count LangsConstants refs
- `grep -oP "const \K[A-Z_]+" app/Config/Constants/ViewsConstants.php | sort -u > /tmp/views_modules.txt`: export views modules
- `grep -n 'NFT_TMP\|NTF_TMP' app/Config/Constants/LangsConstants.php | head -5`: find NTF_TMP in Langs
- `grep -n 'NFT_TMP\|NTF_TMP' app/Config/Constants/ViewsConstants.php | head -5`: find NTF_TMP in Views
- `grep -n 'NFT_TMP' app/Config/Constants/ViewsConstants.php`: exact NTF_TMP match
- `grep -n 'DELEGATION_\|LINK_MESSAGES\|const ' app/Config/Constants/LangsConstants.php | head -40`: delegation and link messages
- `grep -oP "'[a-z_]+_route_unavailable'" app/Config/Constants/LangsConstants.php | sort -u | head -30`: route unavailable keys
- `grep -oP "'(index|store|update|create|edit|destroy|delete|show|generate|export|import|csv|apply|reset|settings|fallback|setup)_" app/Config/Constants/LangsConstants.php | sort -u | head -30`: CRUD action keys
- `grep -A1 "'en' =>" app/Config/Constants/LangsConstants.php | grep "unavailable" | head -30`: English unavailable messages
- `grep -rn 'ViewsConstants::' app/Http/ routes/ --include='*.php' | grep -oP 'ViewsConstants::\K[A-Z_]+' | sort -u > /tmp/route_used_modules.txt`: route-used modules
- `grep -rn 'ViewsConstants::' resources/views/ Modules/ --include='*.blade.php' 2>/dev/null | grep -oP 'ViewsConstants::\K[A-Z_]+' | sort -u > /tmp/blade_used_modules.txt`: blade-used modules
- `grep -rl 'ViewsConstants::' app/ config/ --include='*.php' 2>/dev/null | head -20`: files using ViewsConstants
- `grep -n '^\s*\];' app/Config/Constants/LangsConstants.php | tail -5`: ending brackets
- `grep -n 'NTF_TMP' app/Config/Constants/LangsConstants.php`: NTF_TMP exact
- `grep -oP "ViewsConstants::\K[A-Z_]+" app/Config/Constants/LangsConstants.php | sort -u > /tmp/langs_modules.txt`: export langs modules
- `grep -n 'ViewsConstants::AWD ' app/Config/Constants/LangsConstants.php | head -2`: AWD constant
- `grep -n 'DELEGATION_' app/Config/Constants/LangsConstants.php | head -20`: delegation constants
- `grep -n 'DEFAULT_CLIENT' app/Config/Constants/LangsConstants.php | head -3`: default client
- `grep -n "LINK_MESSAGES\|DEFAULT_CLIENT_MESSAGES" app/Config/Constants/LangsConstants.php | head -5`: link+client messages
- `grep -n "ViewsConstants::" app/Config/Constants/LangsConstants.php | tail -3`: last ViewsConstants refs
