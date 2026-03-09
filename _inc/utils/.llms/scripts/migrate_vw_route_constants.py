#!/usr/bin/env python3
"""Migrate raw route name strings in web.php to VW:: constants.

Usage:
    python3 migrate_vw_route_constants.py [--dry-run]
"""
import re
import sys
import os

LARAVEL_ROOT = os.path.join(
    os.path.dirname(__file__), '..', '..', '..', 'laravel'
)
WEB_PHP = os.path.join(LARAVEL_ROOT, 'routes', 'web.php')

# Mapping: raw route name string -> VW:: constant expression
# 'settings' already has VW::SET so it uses the existing constant.
ROUTE_MAP = {
    'chatify.download.safe': 'VW::CTF_DL_SF',
    'two-factor.secret-key.safe': 'VW::TFA_SK_SF',
    'hrm.dashboard': 'VW::HRM_DSB_R',
    'crm.dashboard': 'VW::CRM_DSB_R',
    'user.profile': 'VW::USR_PRF',
    'zoom.settings': 'VW::ZM_SET',
    'slack.settings': 'VW::SLK_SET',
    'telegram.settings': 'VW::TLG_SET',
    'twilio.setting': 'VW::TWL_SET',
    'print.setting': 'VW::PRT_SET',
    'settings': 'VW::SET',  # existing constant
    'business.setting': 'VW::BS_SET',
    'settings.recaptcha.store': 'VW::SET_RCP_STR',
    'cache.settings.store': 'VW::CC_SET_STR',
    'warehouse-empty-cart': 'VW::WRH_EMP_CRT',
    'credit.note': 'VW::CRD_NT_R',
    'debit.note': 'VW::DBT_NT_R',
    'transactions.index': 'VW::TST_IDX',
    'trial.balance.print': 'VW::TRL_BLC_PRT',
    'receivables.export': 'VW::RCV_EXP',
    'stages.order': 'VW::STG_ORD',
    'stages.json': 'VW::STG_JSN',
    'lead_stages.order': 'VW::LD_STG_ORD',
    'notifications.seen': 'VW::NTF_SEEN',
    'last_login': 'VW::LST_LGN',
    'filter.user.view': 'VW::FLT_USR_VW',
    'update.profile': 'VW::UPD_PRF',
    'search.json': 'VW::SRCH_JSN',
    'filter.project.view': 'VW::FLT_PRJ_VW',
    'remove.user.from.project': 'VW::RM_USR_PRJ',
    'change.mode': 'VW::CHG_MD',
    'dashboard.view': 'VW::DSB_VW',
    'client.dashboard.view': 'VW::CL_DSB_VW',
    'benefit.callback': 'VW::BNF_CB',
    'cashfree.payment.success': 'VW::CSF_PAY_SCS',
    'attendance.file.import': 'VW::ATD_FL_IMP',
    'attendance.import': 'VW::ATD_IMP',
    'stop.tracker': 'VW::STP_TRK',
    'time.tracker': 'VW::TM_TRK',
    'name.search.products': 'VW::NM_SRCH_PRD',
    'search.products': 'VW::SRCH_PRD',
    'offer_letter.update': 'VW::OFR_LTR_UPD',
    'offer_letter.download.pdf': 'VW::OFR_LTR_DL_PDF',
    'offer_letter.download.doc': 'VW::OFR_LTR_DL_DOC',
    'joining_letter.update': 'VW::JN_LTR_UPD',
    'joining_letter.download.pdf': 'VW::JN_LTR_DL_PDF',
    'joining_letter.download.doc': 'VW::JN_LTR_DL_DOC',
    'experience_certificate.update': 'VW::EXCRT_UPD',
    'exp.download.pdf': 'VW::EXP_DL_PDF',
    'exp.download.doc': 'VW::EXP_DL_DOC',
    'noc.update': 'VW::NOC_UPD',
    'noc.download.pdf': 'VW::NOC_DL_PDF',
    'noc.download.doc': 'VW::NOC_DL_DOC',
    'share.project': 'VW::SHR_PRJ',
    'generate': 'VW::GEN',
    'generate.keywords': 'VW::GEN_KW',
    'generate.response': 'VW::GEN_RSP',
    'grammar': 'VW::GRM',
    'grammar.response': 'VW::GRM_RSP',
    'language.disable': 'VW::LNG_DSB',
    'cookie-consent': 'VW::CK_CNSNT',
    'stripe': 'VW::STRP',
    'stripe.post': 'VW::STRP_PST',
}

# Sort longest-first to avoid partial matches
SORTED_KEYS = sorted(ROUTE_MAP.keys(), key=len, reverse=True)


def migrate(dry_run: bool = False) -> int:
    with open(WEB_PHP, 'r') as f:
        lines = f.readlines()

    total = 0
    new_lines = []

    for i, line in enumerate(lines):
        # Skip commented lines
        stripped = line.lstrip()
        if stripped.startswith('//') or stripped.startswith('#') or stripped.startswith('/*'):
            new_lines.append(line)
            continue

        new_line = line
        for key in SORTED_KEYS:
            # Match ->name('key') with single quotes
            pattern_sq = re.compile(
                r'(->name\()' + "'" + re.escape(key) + "'" + r'(\))'
            )
            # Match ->name("key") with double quotes
            pattern_dq = re.compile(
                r'(->name\()' + '"' + re.escape(key) + '"' + r'(\))'
            )
            for pat in [pattern_sq, pattern_dq]:
                if pat.search(new_line):
                    replacement = ROUTE_MAP[key]
                    new_line = pat.sub(r'\g<1>' + replacement + r'\g<2>', new_line)
                    total += 1
                    ln = i + 1
                    if dry_run:
                        print(f"  L{ln}: '{key}' -> {replacement}")

        new_lines.append(new_line)

    if not dry_run:
        with open(WEB_PHP, 'w') as f:
            f.writelines(new_lines)

    return total


def main():
    dry_run = '--dry-run' in sys.argv
    mode = 'DRY RUN' if dry_run else 'APPLYING'
    print(f"[{mode}] Migrating raw route names in web.php to VW:: constants...")
    print(f"  Map entries: {len(ROUTE_MAP)}")

    count = migrate(dry_run)
    print(f"\nTotal replacements: {count}")

    if not dry_run and count > 0:
        print("web.php updated successfully.")
    elif dry_run:
        print("(No changes written — dry run)")


if __name__ == '__main__':
    main()
