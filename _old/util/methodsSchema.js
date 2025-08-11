/** 
 @return void
 */
function main() {
    const wb = SpreadsheetApp.openById(
        "1vcKzkTJ7AvuwUV9ghDhNJI9wwf_jYW23dnAr7RC3kzA"
    );
    if (!wb) {
        Logger.log("Could not find Spreadsheet by ID");
        return;
    }
    const ws =
        wb.getSheetByName("methods") ||
        wb.getSheetByName("Página1") ||
        wb.getSheetByName("Page1");
    if (!ws) {
        Logger.log("Could not find Worksheet by name");
        return;
    }
    const headers = [
            "Library of Package",
            "Related Class",
            "Name",
            "Use Case",
            "Used Modules",
        ],
        pandasMethods = [
            // PANDAS
            [
                "pandas",
                "DataFrame",
                "constructor",
                "Build DataFrames from lists/dicts for tabular data manipulation",
                "MultiSheetExporter, BalanceSheetExport, BillExport, CustomerExport, EmployeeExport, InvoiceExport, LeaveReportExport, PayrollExport, PayslipExport, ProductServiceExport, ProductStockExport, ProfitLossExport, ProposalExport, SalesReportExport, TaskReportExport, TransactionExport, TrialBalanceExport, VendorExport",
            ],
            [
                "pandas",
                "null",
                "read_csv",
                "Load CSV into a DataFrame",
                "BillExport, CustomerExport, EmployeeExport",
            ],
            [
                "pandas",
                "null",
                "read_excel",
                "Load Excel sheets into DataFrames",
                "InvoiceExport, PayslipExport, etc.",
            ],
            [
                "pandas",
                "DataFrame",
                "replace",
                "Replace placeholder or missing values (e.g. NaN) with empty strings",
                "All modules that transform DataFrame for Excel export",
            ],
            [
                "pandas",
                "Timestamp",
                "now, shift",
                "Get current time, shift months (e.g. last month logic)",
                "PayslipExport (filtering by last month), etc.",
            ],
            [
                "pandas",
                "DataFrame",
                "iterrows",
                "Iterate row by row – e.g. scanning data for table detection, transformations",
                "BalanceSheetExport (detect_tables), plus any row-based scanning logic",
            ],
            [
                "pandas",
                "DataFrame",
                "columns, values",
                "Access DataFrame columns or raw arrays for dimension checks, merges, etc.",
                "BalanceSheetExport (detect_tables), or any code needing df.columns or df.values",
            ],
            [
                "pandas",
                "null",
                "read_csv",
                "Read CSV files into a DataFrame",
                "BalanceSheetExportAPIView post() logic for user uploads",
            ],
            [
                "pandas",
                "null",
                "ExcelFile",
                "Parse an Excel file into a Python object that can be read sheet by sheet",
                "BalanceSheetExportAPIView post() logic (raw_data from user uploads)",
            ],
            [
                "pandas",
                "null",
                "read_excel",
                "Load individual Excel sheets into DataFrames",
                "BalanceSheetExportAPIView post() logic for user uploads",
            ],
            [
                "pandas",
                "DataFrame",
                "to_dict(orient='records')",
                "Convert a DataFrame into a list of dicts (field -> value) for processing",
                "BalanceSheetExportAPIView post() logic (creating raw_data for exports)",
            ],
        ],
        reportLabMethods = [
            // REPORTLAB
            [
                "reportlab.lib.pagesizes",
                "null",
                "A4",
                "Standard page dimensions for PDF (width & height).",
                "MultiSheetExporter.export_to_pdf, BalanceSheetExport.export_to_pdf",
            ],
            [
                "reportlab.lib.units",
                "null",
                "inch",
                "Coordinate unit for placing images or text in PDF pages.",
                "MultiSheetExporter.export_to_pdf",
            ],
            [
                "reportlab.lib",
                "colors",
                "HexColor, whitesmoke, grey, white",
                "Define color constants for TableStyle backgrounds, text color.",
                "BalanceSheetExport.export_to_pdf",
            ],
            [
                "reportlab.lib.styles",
                "null",
                "getSampleStyleSheet",
                "Obtain default paragraph styles for PDF text/paragraphs.",
                "BalanceSheetExport.export_to_pdf",
            ],
            [
                "reportlab.pdfgen",
                "canvas",
                "Canvas, setFont, drawString, drawImage, showPage, save",
                "Low-level PDF drawing API to place text/images, paginate, finalize the PDF.",
                "MultiSheetExporter.export_to_pdf",
            ],
            [
                "reportlab.platypus",
                "SimpleDocTemplate",
                "build",
                "High-level PDF layout building – doc.build() to assemble Flowables (tables, paragraphs, images).",
                "BalanceSheetExport.export_to_pdf",
            ],
            [
                "reportlab.platypus",
                "Table, TableStyle, Paragraph, Spacer, Image, PageBreak",
                "Table(...).setStyle(...), Paragraph(...), Spacer(...), Image(...), PageBreak(...)",
                "Build structured PDF layout with text paragraphs, spacing, images, multi-page flows.",
                "BalanceSheetExport.export_to_pdf",
            ],
        ],
        openpyxlMethods = [
            [
                "openpyxl",
                "Workbook",
                "Workbook, create_sheet, active, save",
                "Create and manage Excel workbooks, optionally adding sheets and saving to disk",
                "MultiSheetExporter, BalanceSheetExport, BillExport, CustomerExport, EmployeeExport, InvoiceExport, LeaveReportExport, " +
                    "PayrollExport, PayslipExport, ProductServiceExport, ProductStockExport, ProfitLossExport, ProposalExport, " +
                    "SalesReportExport, TaskReportExport, TransactionExport, TrialBalanceExport, VendorExport",
            ],
            [
                "openpyxl",
                "Worksheet",
                "freeze_panes, merge_cells, append, iter_rows, iter_cols, columns, column_dimensions, title, max_row, max_column",
                "Manipulate sheet rows & columns, freeze headers, merge cells for titles, rename/style columns, track row/column boundaries, and dynamically set widths",
                "All modules generating Excel sheets",
            ],
            [
                "openpyxl.styles",
                "Font, PatternFill, Border, Side, Alignment",
                "this",
                "Set fonts (e.g., bold), fill backgrounds, define borders, align text horizontally or vertically",
                "All modules that format headers, data rows, merges",
            ],
            [
                "openpyxl.utils.dataframe",
                "null",
                "dataframe_to_rows",
                "Convert a pandas DataFrame into row arrays for easier appending to a Worksheet",
                "All modules that take a DataFrame and output to Excel",
            ],
        ],
        djangoMethods = [
            [
                "django.urls",
                "null",
                "path",
                "Define URL route patterns for each export endpoint",
                "All APIView classes with endpoints",
            ],
            [
                "django.http",
                "null",
                "HttpResponse",
                "Return binary/file data (e.g. Excel) in Django views",
                "All ExportAPIView modules returning files",
            ],
            [
                "rest_framework.views",
                "null",
                "APIView",
                "DRF class-based view for handling HTTP requests",
                "All *ExportAPIView classes",
            ],
            [
                "rest_framework.response",
                "null",
                "Response",
                "Construct a structured HTTP response (JSON, error message, etc.)",
                "All *ExportAPIView classes",
            ],
            [
                "rest_framework",
                "null",
                "status",
                "DRF HTTP status codes (e.g. HTTP_200_OK, HTTP_404_NOT_FOUND, etc.)",
                "All *ExportAPIView classes",
            ],
            [
                "django.conf",
                "settings",
                "DEBUG",
                "Check if debug mode is on for tracebacks",
                "ExceptionHandler",
            ],
            [
                "django.core.management",
                "null",
                "call_command",
                "Runs a Django management command programmatically",
                "Kernel",
            ],
            [
                "django.core.expections",
                "null",
                "ValidationError, PermissionDnied, Http404",
                "Handle specific exceptions in custom global handlers",
                "ExceptionHandler",
            ],
        ];
    methodsData = [
        ...pandasMethods,
        ...reportLabMethods,
        ...openpyxlMethods,
        ...djangoMethods,
        // APSCHEDULER
        [
            "apscheduler.schedulers.background",
            "BackgroundScheduler",
            "add_job, start, shutdown",
            "Initialize and manage scheduled jobs in the background (periodic or triggered tasks)",
            "Kernel (scheduler-based logic for e.g. call_command('inspire'))",
        ],
        // COLORAMA
        [
            "colorama",
            "Fore",
            "GREEN, CYAN",
            "Provides terminal text coloration for console feedback",
            "BalanceSheetExport (CLI console prints)",
        ],
        // JSON
        [
            "json",
            "null",
            "dump, load",
            "Serialize or deserialize data to JSON",
            "ask_to_save_json() in MultiSheetExporter, BalanceSheetExport, etc.",
        ],
        // ATEXIT
        [
            "atexit",
            "null",
            "register",
            "Registers a function to be called upon normal interpreter termination",
            "Kernel",
        ],
        // DATETIME
        [
            "datetime",
            "datetime",
            "now, strftime",
            "Get current date/time, format strings",
            "BalanceSheetExport, MultiSheetExporter (some date usage with pd.Timestamp or datetime now) ",
        ],
        // LOGGING
        [
            "logging",
            "null",
            "logger",
            "Log debug/error info in export processes",
            "All modules (error handling & debugging)",
        ],
        [
            "time",
            "null",
            "sleep",
            "Pause the main thread in a loop or waiting scenario",
            "Kernel (if run as a main script with time.sleep(1) in while True)",
        ],
    ];
    ws.clearContents().setColumnWidths(1, headers.length, 196);
    Logger.log("Cleared...");
    ws.getRange(1, 1, 1, headers.length)
        .setValues([headers])
        .setFontWeight("bold");
    Logger.log("Appended rows");
    for (const r of methodsData) {
        for (const c of r) if (!c) c = "null";
        ws.appendRow(r);
    }
    ws.autoResizeColumns(1, headers.length);
}
