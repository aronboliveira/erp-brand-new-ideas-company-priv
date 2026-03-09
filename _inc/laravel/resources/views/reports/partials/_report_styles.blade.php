{{-- Report-specific CSS: pseudo-classes, accessibility, KPI aggregation cards --}}
<style>
    /* ── KPI Summary Cards ── */
    .rpt-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .rpt-kpi-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: .5rem;
        padding: 1rem 1.25rem;
        transition: box-shadow .2s ease, transform .15s ease;
    }
    .rpt-kpi-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
        transform: translateY(-2px);
    }
    .rpt-kpi-card:focus-within {
        outline: 2px solid #6366f1;
        outline-offset: 2px;
    }
    .rpt-kpi-label {
        font-size: .75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
        margin: 0 0 .25rem;
    }
    .rpt-kpi-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .rpt-kpi-value--positive { color: #16a34a; }
    .rpt-kpi-value--negative { color: #dc2626; }
    .rpt-kpi-value--neutral  { color: #2563eb; }

    /* ── Table Pseudo-classes ── */
    .rpt-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    .rpt-table tbody tr:nth-child(odd) {
        background-color: #fff;
    }
    .rpt-table tbody tr:hover {
        background-color: #eef2ff;
        transition: background-color .15s ease;
    }
    .rpt-table tbody tr:focus-within {
        outline: 2px solid #6366f1;
        outline-offset: -2px;
    }
    .rpt-table th {
        background-color: #f1f5f9;
        font-weight: 600;
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #475569;
        padding: .625rem .75rem;
        border-bottom: 2px solid #cbd5e1;
        white-space: nowrap;
    }
    .rpt-table td {
        padding: .5rem .75rem;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
    }
    .rpt-table caption {
        caption-side: top;
        font-weight: 600;
        font-size: .9rem;
        color: #334155;
        padding: .5rem 0;
        text-align: left;
    }

    /* ── Total / Summary rows ── */
    .rpt-table tr.rpt-total-row {
        background-color: #f0fdf4 !important;
        font-weight: 700;
        border-top: 2px solid #86efac;
    }
    .rpt-table tr.rpt-total-row:hover {
        background-color: #dcfce7 !important;
    }
    .rpt-table tr.rpt-section-header td {
        background-color: #f1f5f9;
        font-weight: 600;
        color: #1e293b;
        border-top: 1px solid #cbd5e1;
    }

    /* ── Focus-visible for keyboard nav ── */
    .rpt-table a:focus-visible,
    .rpt-kpi-card a:focus-visible {
        outline: 2px solid #6366f1;
        outline-offset: 2px;
        border-radius: 2px;
    }
    .rpt-table th:focus-visible,
    .rpt-table td:focus-visible {
        outline: 2px solid #6366f1;
        outline-offset: -2px;
    }

    /* ── Screen-reader only ── */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0,0,0,0);
        white-space: nowrap;
        border: 0;
    }

    /* ── Print styles ── */
    @media print {
        .rpt-kpi-grid {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: .5rem;
        }
        .rpt-kpi-card {
            border: 1px solid #94a3b8;
            box-shadow: none;
            transform: none;
        }
        .rpt-table tbody tr:hover {
            background-color: inherit;
        }
    }
</style>
