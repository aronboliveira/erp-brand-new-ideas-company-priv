from __future__ import annotations

import pytest

from conftest import load_exporter_class


SIMPLE_EXPORTERS = [
    "account_statement_exporter",
    "customer_exporter",
    "employee_exporter",
    "payroll_exporter",
    "payslip_exporter",
    "product_service_exporter",
    "transaction_exporter",
    "vendor_exporter",
]


@pytest.mark.parametrize("module_name", SIMPLE_EXPORTERS)
def test_simple_exporters_return_expected_columns(module_name, exporter_payloads) -> None:
    exporter_cls = load_exporter_class(module_name)
    exporter = exporter_cls()
    exporter.data = exporter_payloads[module_name]()

    frame = exporter.process_data()

    assert list(frame.columns) == exporter.get_headings()
    assert len(frame) == len(exporter.data["rows"])


@pytest.mark.parametrize(
    "module_name",
    SIMPLE_EXPORTERS + ["balance_sheet_exporter", "sales_report_exporter", "trial_balance_exporter"],
)
def test_exporters_return_empty_frames_for_missing_rows(module_name, exporter_payloads) -> None:
    exporter_cls = load_exporter_class(module_name)
    exporter = exporter_cls()
    payload = exporter_payloads[module_name]()
    payload["rows"] = {} if module_name in {"balance_sheet_exporter", "trial_balance_exporter"} else []
    exporter.data = payload

    frame = exporter.process_data()

    assert frame.empty


def test_balance_sheet_exporter_flattens_hierarchy_and_tracks_totals(exporter_payloads) -> None:
    exporter_cls = load_exporter_class("balance_sheet_exporter")
    exporter = exporter_cls()
    exporter.data = exporter_payloads["balance_sheet_exporter"]()

    frame = exporter.process_data()
    accounts = frame["Account"].tolist()

    assert "Liabilities & Equity" in accounts
    assert "Total Assets" in accounts
    assert "Total Liabilities & Equity" in accounts
    assert exporter._total_assets == pytest.approx(5000.5)
    assert exporter._total_liabilities == pytest.approx(1000.0)
    assert exporter._total_equity == pytest.approx(4000.5)
    assert exporter._asset_breakdown == [("Current Assets", 2000.5), ("Fixed Assets", 3000.0)]


def test_trial_balance_exporter_builds_grouped_rows_and_marks_balanced(exporter_payloads) -> None:
    exporter_cls = load_exporter_class("trial_balance_exporter")
    exporter = exporter_cls()
    exporter.data = exporter_payloads["trial_balance_exporter"]()

    frame = exporter.process_data()

    assert frame.iloc[0]["_row_type"] == "spacer"
    assert "Subtotal - Assets" in frame["Account Name"].tolist()
    assert frame.iloc[-1]["Account Name"] == "TOTAL"
    assert exporter._total_debit == pytest.approx(1500.0)
    assert exporter._total_credit == pytest.approx(1500.0)
    assert exporter._is_balanced is True
    assert exporter._type_totals["Assets"] == (1500.0, 0.0)


def test_sales_report_item_mode_tracks_top_performers_and_metrics(exporter_payloads) -> None:
    exporter_cls = load_exporter_class("sales_report_exporter")
    exporter = exporter_cls()
    exporter.data = exporter_payloads["sales_report_exporter"]()

    frame = exporter.process_data()

    assert list(frame.columns) == [
        "Item Name",
        "Quantity Sold",
        "Amount",
        "Average Amount",
        "_raw_amount",
        "_raw_avg",
        "_row_type",
    ]
    assert exporter._record_count == 6
    assert exporter._total_sales == pytest.approx(5270.0)
    assert exporter._total_quantity == 19
    assert exporter._top_performers[0] == ("Integration", 1500.0)


def test_sales_report_customer_mode_calculates_tax_inclusive_sales(exporter_payloads) -> None:
    exporter_cls = load_exporter_class("sales_report_exporter")
    exporter = exporter_cls()
    exporter.data = {
        "company_name": "PrestTech",
        "start_date": "2026-01-01",
        "end_date": "2026-01-31",
        "report_name": "Customer",
        "currency_symbol": "$",
        "rows": [
            {"name": "Acme", "invoice_count": 2, "price": 100.0, "total_tax": 10.0},
            {"name": "Globex", "invoice_count": 1, "price": 200.0, "total_tax": 20.0},
        ],
    }

    frame = exporter.process_data()

    assert list(frame.columns) == [
        "Customer Name",
        "Invoice Count",
        "Sales",
        "Sales With Tax",
        "_raw_sales",
        "_raw_sales_tax",
        "_row_type",
    ]
    assert frame.iloc[0]["Sales With Tax"] == 110.0
    assert exporter._avg_sale == pytest.approx(150.0)

