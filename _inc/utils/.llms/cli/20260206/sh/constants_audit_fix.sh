#!/usr/bin/env bash
# ============================================================
# Constants Audit Fix — CLI commands used for alias + duplicate
# constant standardization (2025-06-06 session continuation)
# ============================================================
# Run from: _inc/laravel/
# ============================================================

set -euo pipefail
cd "$(dirname "$0")/../../../../../laravel" || exit 1
ROOT=$(pwd)

# ============================================================
# 1. CRITICAL MIGRATIONS — Alias collisions (committed 11d5b1b3)
# ============================================================

# BLC → BC (bank_accounts)
sed -i 's/\bas BLC\b/as BC/g; s/\bBLC::/BC::/g' \
  database/migrations/2025_06_03_233247_create_bank_accounts_table.php

# PC → PJC (ProjectsConstants)
sed -i 's/ProjectsConstants as PC\b/ProjectsConstants as PJC/g; s/\bPC::/PJC::/g' \
  database/migrations/2025_06_03_233310_create_contract_comments_table.php \
  database/migrations/2025_06_03_233315_create_user_email_templates_table.php

# SC → SPC (SupportsConstants)
sed -i 's/SupportsConstants as SC\b/SupportsConstants as SPC/g; s/\bSC::/SPC::/g' \
  database/migrations/2025_06_03_233323_create_supports_table.php \
  database/migrations/2025_06_03_233549_create_support_replies_table.php

# CC → CPC (CompaniesConstants) in 15 migration files
sed -i 's/CompaniesConstants as CC\b/CompaniesConstants as CPC/g; s/\bCC::/CPC::/g' \
  database/migrations/2025_06_03_233239_create_locations_table.php \
  database/migrations/2025_06_03_233301_create_warnings_table.php \
  database/migrations/2025_06_03_233302_create_complaints_table.php \
  database/migrations/2025_06_03_233258_create_leaves_table.php \
  database/migrations/2025_06_03_233305_create_meetings_table.php \
  database/migrations/2025_06_03_233600_create_zoom_meetings_table.php \
  database/migrations/2025_06_03_233306_create_meeting_employees_table.php \
  database/migrations/2025_06_03_233259_create_events_table.php \
  database/migrations/2025_06_03_233605_create_warehouses_table.php \
  database/migrations/2025_06_03_233311_create_pos_table.php \
  database/migrations/2025_06_03_233541_create_coupons_table.php \
  database/migrations/2025_06_03_233300_create_assets_table.php \
  database/migrations/2025_06_03_233303_create_trainings_table.php \
  database/migrations/2025_06_03_233316_create_time_trackers_table.php \
  database/migrations/2025_06_03_233261_create_announcements_table.php

# ============================================================
# 2. HIGH MIGRATIONS — Deprecated duplicate refs (committed 0b19d411)
# ============================================================

sed -i 's/\bAC::COL_TSK\b/AC::COL_TSK_ID/g; s/\bAC::COL_LT\b/AC::COL_LOG_TP/g' \
  database/migrations/2025_06_03_233248_create_activities_table.php

sed -i 's/\bUC::COL_MSG_CL\b/UC::COL_MC/g; s/\bUC::COL_DEL_STT\b/UC::COL_D_ST/g' \
  database/migrations/2025_06_03_233243_create_clients_table.php

sed -i 's/\bUC::COL_RQ_PLN\b/UC::COL_RP/g' \
  database/migrations/2025_06_03_233548_create_plan_requests_table.php

sed -i 's/\bDC::TABLE_WHS\b/DC::TABLE_WRH/g' \
  database/migrations/2025_06_03_233308_create_purchases_table.php \
  database/migrations/2025_06_03_233607_create_warehouse_transfers_table.php \
  database/migrations/2025_06_03_233606_create_warehouse_products_table.php

sed -i 's/\bDC::TABLE_PRODUCTS\b/DC::TABLE_PRD/g' \
  database/migrations/2025_06_03_233312_create_proposal_products_table.php \
  database/migrations/2025_06_03_233308_create_purchases_table.php \
  database/migrations/2025_06_03_233609_create_stock_reports_table.php \
  database/migrations/2025_06_03_233606_create_warehouse_products_table.php

# ============================================================
# 3. MEDIUM MIGRATIONS — Raw-string TABLE constants (committed 7d0544ca)
# ============================================================

# Already have DC imported:
sed -i "s/private const TABLE = 'sources'/private const TABLE = DC::TABLE_SOURCES/" \
  database/migrations/2025_06_03_233249_create_sources_table.php
sed -i "s/private const TABLE = 'deal_discussions'/private const TABLE = DC::TABLE_DL_DSC/" \
  database/migrations/2025_06_03_233257_create_deal_discussions_table.php
sed -i "s/private const TABLE_NAME = 'messages'/private const TABLE_NAME = DC::TABLE_MSG/" \
  database/migrations/2025_06_03_233246_create_default_messages_table.php
sed -i "s/private const TABLE = 'client_deals'/private const TABLE = DC::TABLE_CLT_DLS/" \
  database/migrations/2025_06_03_233254_create_client_deals_table.php
sed -i "s/private const TABLE = 'user_deals'/private const TABLE = DC::TABLE_USR_DLS/" \
  database/migrations/2025_06_03_233253_create_user_deals_table.php

# Add alias + replace raw consts + update existing DatabaseConstants:: refs:
sed -i 's/use App\\Config\\Constants\\DatabaseConstants;/use App\\Config\\Constants\\DatabaseConstants as DC;/; s/DatabaseConstants::/DC::/g; s/private const TABLE = '\''webhook_settings'\''/private const TABLE = DC::TABLE_WEBHOOK_STG/' \
  database/migrations/2025_06_03_233617_create_webhook_settings_table.php
sed -i 's/use App\\Config\\Constants\\DatabaseConstants;/use App\\Config\\Constants\\DatabaseConstants as DC;/; s/DatabaseConstants::/DC::/g; s/private const TABLE_NAME = '\''generate_payslip_options'\''/private const TABLE_NAME = DC::TABLE_GEN_PSL_OPT/' \
  database/migrations/2025_06_03_233328_create_generate_payslip_options_table.php

# ============================================================
# 4. CRITICAL MODELS/CONTROLLERS — Alias collisions (committed f1ba23da)
# ============================================================

# BLC → BC
sed -i 's/\bas BLC\b/as BC/g; s/\bBLC::/BC::/g' app/Models/Companies/BankAccount.php

# CTC → CHTC
sed -i 's/\bas CTC\b/as CHTC/g; s/\bCTC::/CHTC::/g' app/Models/utils/Utility.php

# VCN → VC
sed -i 's/\bas VCN\b/as VC/g; s/\bVCN::/VC::/g' \
  app/Http/Controllers/Products/ProductServiceController.php \
  app/Http/Controllers/Products/ProductServiceCategoryController.php

# MC → MWC (LandingPage Routes)
sed -i 's/MiddlewaresConstants as MC\b/MiddlewaresConstants as MWC/; s/\bMC::/MWC::/g' \
  Modules/LandingPage/Routes/web.php

# CC → CPC (21 model/trait files)
sed -i 's/CompaniesConstants as CC\b/CompaniesConstants as CPC/g; s/\bCC::/CPC::/g' \
  app/Models/Individuals/User.php \
  app/Models/Traits/BranchConnected.php \
  app/Models/Traits/DefinesDates.php \
  app/Models/Traits/StoresPlanning.php \
  app/Models/Traits/DescribesCompanyBranch.php \
  app/Models/Shapes/Asset.php \
  app/Models/Companies/Warehouse.php \
  app/Models/Contact/ZoomMeeting.php \
  app/Models/Info/Announcement.php \
  app/Models/Info/Warning.php \
  app/Models/Bills/StockReport.php \
  app/Models/Bills/Coupon.php \
  app/Models/Bills/UserCoupon.php \
  app/Models/Activity/Complaint.php \
  app/Models/Activity/Meeting.php \
  app/Models/Activity/MeetingEmployee.php \
  app/Models/Activity/Event.php \
  app/Models/Activity/Pos.php \
  app/Models/Activity/Training.php \
  app/Models/Planning/Leave.php \
  app/Models/Planning/TimeTracker.php

# PC → PMC (PermissionsConstants)
sed -i 's/PermissionsConstants as PC\b/PermissionsConstants as PMC/g; s/\bPC::/PMC::/g' \
  app/Services/TaskRequestService.php \
  app/Config/Constants/SeedersTemplating.php \
  app/Enums/UserType.php \
  app/Models/Individuals/Customer.php \
  app/Models/Planning/TaskStage.php

# PC → PJC (ProjectsConstants)
sed -i 's/ProjectsConstants as PC\b/ProjectsConstants as PJC/g; s/\bPC::/PJC::/g' \
  app/Models/Contact/UserEmailTemplate.php \
  app/Models/Planning/ContractComment.php

# SC → SPC (SupportsConstants)
sed -i 's/SupportsConstants as SC\b/SupportsConstants as SPC/g; s/\bSC::/SPC::/g' \
  app/Services/SupportHelperService.php \
  app/Http/Controllers/Activity/SupportController.php \
  app/Models/Activity/Support.php

# LPC/LSC/LandingPageSettingsConstants → LPSC (LandingPage SettingsConstants)
sed -i 's/SettingsConstants as LPC\b/SettingsConstants as LPSC/g; s/\bLPC::/LPSC::/g' \
  Modules/LandingPage/Resources/views/landingpage/features/index.blade.php \
  Modules/LandingPage/Resources/views/landingpage/join_us.blade.php \
  Modules/LandingPage/Resources/views/landingpage/menubar/index.blade.php \
  Modules/LandingPage/Resources/views/landingpage/discover/index.blade.php \
  Modules/LandingPage/Resources/views/layouts/custompage.blade.php \
  Modules/LandingPage/Database/Seeders/LandingPageDataTableSeeder.php \
  Modules/LandingPage/Entities/LandingPageSetting.php

sed -i 's/SettingsConstants as LSC\b/SettingsConstants as LPSC/g; s/\bLSC::/LPSC::/g' \
  Modules/LandingPage/Resources/views/landingpage/topbar.blade.php \
  Modules/LandingPage/Resources/views/landingpage/menubar/edit.blade.php

sed -i 's/SettingsConstants as LandingPageSettingsConstants/SettingsConstants as LPSC/g; s/\bLandingPageSettingsConstants::/LPSC::/g' \
  Modules/LandingPage/Resources/views/landingpage/pricing_plan.blade.php \
  Modules/LandingPage/Resources/views/landingpage/menubar/create.blade.php \
  Modules/LandingPage/Resources/views/landingpage/testimonials/create.blade.php \
  Modules/LandingPage/Resources/views/landingpage/testimonials/edit.blade.php \
  Modules/LandingPage/Resources/views/landingpage/home_section.blade.php \
  Modules/LandingPage/Resources/views/layouts/landingpage.blade.php \
  Modules/LandingPage/Resources/views/layouts/buttons.blade.php

# ============================================================
# 5. HIGH MODELS/CONTROLLERS — Deprecated const refs + removal (committed 6a5a05f2)
# ============================================================

# Replace deprecated refs
sed -i 's/\bBC::COL_BIL_ID\b/BC::COL_BL_ID/g' app/Models/Bills/Bill.php
sed -i 's/\bUC::COL_MSG_CL\b/UC::COL_MC/g; s/\bUC::COL_DEL_STT\b/UC::COL_D_ST/g' \
  app/Models/Individuals/Client.php
sed -i 's/\bDC::TABLE_WHS\b/DC::TABLE_WRH/g' app/Models/Bills/StockReport.php
sed -i 's/\bDC::TABLE_PRODUCTS\b/DC::TABLE_PRD/g' \
  app/Http/Controllers/Bills/InvoiceController.php \
  app/Http/Controllers/Activity/DealController.php \
  app/Models/Traits/ExtendsProductServiceTable.php \
  app/Models/Bills/StockReport.php \
  app/Models/Activity/Purchase.php
sed -i 's/\bDC::TABLE_TEMPLATES\b/DC::TABLE_TMP/g' \
  app/Http/Controllers/Ssr/AiTemplateController.php

# Remove deprecated constants from Constants files
sed -i "/public const COL_BIL_ID = 'bill_id';/d" app/Config/Constants/BillsConstants.php
sed -i "/public const COL_OTHER_TX = 'other_taxes';/d" app/Config/Constants/BillsConstants.php
sed -i "/public const COL_TSK = 'task_id';/d" app/Config/Constants/ActivitiesConstants.php
sed -i "/public const COL_LT = 'log_type';/d" app/Config/Constants/ActivitiesConstants.php
sed -i "/public const COL_MSG_CL = 'messenger_color';/d" app/Config/Constants/UsersConstants.php
sed -i "/public const COL_DEL_STT = 'delete_status';/d" app/Config/Constants/UsersConstants.php
sed -i "/public const COL_RQ_PLN = 'requested_plan';/d" app/Config/Constants/UsersConstants.php
sed -i "/public const TABLE_FORM_FIELDS = 'form_fields';/d" app/Config/Constants/DatabaseConstants.php
sed -i "/public const TABLE_WHS = 'warehouses';/d" app/Config/Constants/DatabaseConstants.php
sed -i "/public const TABLE_PRODUCTS = 'products';/d" app/Config/Constants/DatabaseConstants.php
sed -i "/public const TABLE_TEMPLATES = 'templates';/d" app/Config/Constants/DatabaseConstants.php
sed -i "/public const TABLE_PSLP = 'payslips';/d" app/Config/Constants/DatabaseConstants.php

# ============================================================
# 6. MEDIUM MODELS/CONTROLLERS — Raw-string table refs (committed 0688d0bf)
# ============================================================

sed -i "s/'deal_discussions'/DC::TABLE_DL_DSC/g" app/Models/Bills/StockReport.php
sed -i "s/'client_deals'/DC::TABLE_CLT_DLS/g" \
  app/Http/Controllers/Activity/DealController.php \
  app/Models/Individuals/User.php \
  app/Models/Activity/Deal.php
sed -i "s/'user_deals'/DC::TABLE_USR_DLS/g" \
  app/Http/Controllers/Activity/DealController.php \
  app/Models/Individuals/User.php \
  app/Models/Activity/Deal.php

echo "=== All commands complete ==="
