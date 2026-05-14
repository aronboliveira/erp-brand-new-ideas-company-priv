"""Generate seaborn charts for the 2026-05-14 reliability+quarantine case study.

Run:
    python3 _inc/laravel/utils/scripts/py/... is overkill; this script lives
    next to the charts it produces.
    cd .history/case-study/claude-main-agent/20260514/charts/seaborn/
    python3 generate.py

Outputs PNGs in the same directory. Data is inlined — it captures the
post-session state of the reliability + quarantine subsystem and does not
re-query any artifacts at runtime, so the charts are reproducible from
source-controlled inputs only.
"""
from __future__ import annotations

import pathlib

import matplotlib.pyplot as plt
import pandas as pd
import seaborn as sns

OUT = pathlib.Path(__file__).resolve().parent
sns.set_theme(style="whitegrid", context="talk", palette="deep")


def chart_issues_by_category() -> None:
    """Stacked bar of issues per module x severity."""
    rows = [
        # (category, severity, count)
        ("Retry", "HIGH", 1),
        ("Retry", "MEDIUM", 2),
        ("Retry", "LOW", 3),
        ("Retry", "INFO", 1),
        ("CircuitBreaker", "HIGH", 1),
        ("CircuitBreaker", "MEDIUM", 3),
        ("CircuitBreaker", "LOW", 3),
        ("Commit-path", "HIGH", 2),
        ("Commit-path", "LOW", 1),
        ("Quarantine", "HIGH", 1),
        ("Quarantine", "MEDIUM", 2),
        ("Quarantine", "LOW", 3),
        ("Quarantine", "INFO", 1),
    ]
    df = pd.DataFrame(rows, columns=["Module", "Severity", "Count"])
    pivot = df.pivot_table(index="Module", columns="Severity", values="Count", fill_value=0)
    order = ["HIGH", "MEDIUM", "LOW", "INFO"]
    pivot = pivot[[c for c in order if c in pivot.columns]]
    palette = {"HIGH": "#c0392b", "MEDIUM": "#d35400", "LOW": "#27ae60", "INFO": "#7f8c8d"}

    fig, ax = plt.subplots(figsize=(11, 6))
    bottom = pd.Series(0, index=pivot.index)
    for sev in pivot.columns:
        ax.bar(pivot.index, pivot[sev], bottom=bottom, label=sev, color=palette[sev], edgecolor="white", linewidth=0.8)
        bottom = bottom + pivot[sev]

    for i, module in enumerate(pivot.index):
        total = int(pivot.loc[module].sum())
        ax.text(i, total + 0.15, str(total), ha="center", va="bottom", fontsize=12, fontweight="bold")

    ax.set_title("Issues closed by module and severity\n(2026-05-14 reliability + quarantine pass)", pad=12)
    ax.set_ylabel("Issues closed")
    ax.set_xlabel("")
    ax.legend(title="Severity", loc="upper right", frameon=True)
    ax.set_ylim(0, bottom.max() * 1.18)
    plt.tight_layout()
    plt.savefig(OUT / "issues-by-module-severity.png", dpi=140)
    plt.close(fig)


def chart_test_growth() -> None:
    """Line of reliability test counts across the session's milestones."""
    rows = [
        ("baseline (pre-session)", 8, 51),
        ("R-1+CB-1 (high prio)", 8, 51),  # tests same; behavior changed
        ("R-2/3/4 + CB-2/3/4 (medium)", 9, 66),
        ("CB-5 (low)", 9, 66),
        ("CB-6/CB-7/R-4 (low)", 9, 66),
        ("F1 (atomic ledger)", 16, 120),
        ("F1+F2", 16, 120),
        ("F1+F2+F3 (orphan sweep)", 17, 128),
        ("Q1+Q4+Q2", 19, 136),
        ("Q3+Q5+Q6", 20, 142),
        ("Q7 backfill", 21, 146),
        ("All reliability suite", 60, 362),
    ]
    df = pd.DataFrame(rows, columns=["Milestone", "Tests", "Assertions"])
    df["Index"] = range(len(df))

    fig, ax = plt.subplots(figsize=(13, 6.5))
    ax2 = ax.twinx()

    sns.lineplot(data=df, x="Index", y="Tests", marker="o", linewidth=2.6, ax=ax,
                 color="#2980b9", label="Tests")
    sns.lineplot(data=df, x="Index", y="Assertions", marker="s", linewidth=2.6, ax=ax2,
                 color="#16a085", label="Assertions")

    ax.set_xticks(df["Index"])
    ax.set_xticklabels(df["Milestone"], rotation=30, ha="right")
    ax.set_xlabel("")
    ax.set_ylabel("Tests", color="#2980b9")
    ax2.set_ylabel("Assertions", color="#16a085")
    ax.tick_params(axis="y", labelcolor="#2980b9")
    ax2.tick_params(axis="y", labelcolor="#16a085")
    ax2.grid(False)

    ax.set_title("Reliability test growth across session milestones", pad=10)

    # Unified legend
    lines1, labels1 = ax.get_legend_handles_labels()
    lines2, labels2 = ax2.get_legend_handles_labels()
    ax.legend(lines1 + lines2, labels1 + labels2, loc="upper left", frameon=True)
    ax2.get_legend().remove() if ax2.get_legend() else None

    plt.tight_layout()
    plt.savefig(OUT / "test-growth.png", dpi=140)
    plt.close(fig)


def chart_quarantine_schema() -> None:
    """Categorical before/after of the operation_quarantines.domain enum."""
    rows_before = [("finance", 1), ("warehouse", 1), ("crm", 1), ("general", 1)]
    rows_after = [
        ("finance", 1), ("warehouse", 1), ("crm", 1), ("hrm", 1),
        ("planning", 1), ("heavy_io", 1), ("general", 1),
    ]
    df_b = pd.DataFrame(rows_before, columns=["Domain", "Coverage"])
    df_a = pd.DataFrame(rows_after, columns=["Domain", "Coverage"])
    df_b["State"] = "Before Q1 (silent-truncation bug)"
    df_a["State"] = "After Q1 (fixed)"

    df = pd.concat([df_b, df_a], ignore_index=True)
    # Mark which domains are production-active to highlight the gap
    active = {"finance", "warehouse", "crm", "hrm", "planning", "heavy_io"}
    df["Status"] = df["Domain"].apply(
        lambda d: "production-active" if d in active else "unused placeholder"
    )

    fig, axes = plt.subplots(1, 2, figsize=(13, 5.5), sharey=True)
    palette = {"production-active": "#27ae60", "unused placeholder": "#bdc3c7"}

    for ax, state, frame in zip(axes, [df_b["State"].iloc[0], df_a["State"].iloc[0]],
                                [df_b.assign(Status=df_b["Domain"].apply(
                                    lambda d: "production-active" if d in active else "unused placeholder")),
                                 df_a.assign(Status=df_a["Domain"].apply(
                                    lambda d: "production-active" if d in active else "unused placeholder"))]):
        sns.barplot(data=frame, x="Domain", y="Coverage", hue="Status",
                    palette=palette, ax=ax, dodge=False, hue_order=list(palette.keys()))
        ax.set_title(state, fontsize=14)
        ax.set_xlabel("")
        ax.set_ylim(0, 1.2)
        ax.set_yticks([])
        ax.set_ylabel("")
        ax.tick_params(axis="x", rotation=30)
        for lbl in ax.get_xticklabels():
            lbl.set_horizontalalignment("right")
        if ax.get_legend():
            ax.get_legend().remove()

    # Annotate missing entries on the "Before" panel
    missing = {"hrm", "planning", "heavy_io"}
    axes[0].text(0.5, 0.85,
                 "Missing from enum:\n" + ", ".join(sorted(missing)),
                 transform=axes[0].transAxes, ha="center", va="top",
                 fontsize=13, color="#c0392b", fontweight="bold",
                 bbox=dict(boxstyle="round,pad=0.4", fc="#fdecea", ec="#c0392b"))

    # Shared legend
    handles = [plt.Rectangle((0, 0), 1, 1, fc=palette[k]) for k in palette]
    fig.legend(handles, list(palette.keys()), loc="lower center", ncol=2,
               frameon=True, bbox_to_anchor=(0.5, -0.02))

    plt.suptitle("operation_quarantines.domain enum — Q1 schema fix", y=1.02, fontsize=16)
    plt.tight_layout()
    plt.savefig(OUT / "quarantine-domain-enum-fix.png", dpi=140, bbox_inches="tight")
    plt.close(fig)


def chart_commit_path_window() -> None:
    """Conceptual timeline of the F1 atomic window fix."""
    rows = [
        # (Phase, Before-state-of-ledger, After-state-of-ledger, Step)
        ("createLedger", "started", "started", 1),
        ("recordStep(begin)", "started", "started", 2),
        ("operation.started event", "started", "started", 3),
        ("recordStep(db_transaction, RUNNING)", "started", "started", 4),
        ("BEGIN TX", "started", "started", 5),
        ("user callback / DML", "started", "started", 6),
        ("recordOutbox()", "started", "started", 7),
        ("succeedStep(db_transaction)", "started → committed (before F1: outside tx)", "still inside tx (after F1)", 8),
        ("ledger.status = committed", "started → committed (before F1: outside tx)", "still inside tx (after F1)", 9),
        ("operation.committed event", "started → committed (before F1: outside tx)", "still inside tx (after F1)", 10),
        ("COMMIT TX", "committed", "committed", 11),
    ]
    df = pd.DataFrame(rows, columns=["Phase", "Before", "After", "Step"])

    fig, ax = plt.subplots(figsize=(13, 6.5))

    # Highlight the windows where data is committed but ledger lags (before F1)
    risk_before_window = [(11.0, 11.5, "Crash window\n(before F1)")]  # symbolic; data+outbox committed, ledger status flip pending

    # Position bars per step
    ax.barh(df["Phase"], [1] * len(df), left=df["Step"] - 0.5, color="#3498db",
            edgecolor="white", linewidth=1.3, alpha=0.85)

    # Mark inside-tx phases
    inside_tx_steps = list(range(6, 11))  # steps 6..10 — F1 pulls 8,9,10 inside
    for s in inside_tx_steps:
        ax.axvspan(s - 0.5, s + 0.5, color="#27ae60", alpha=0.07, zorder=0)

    # Crash window shading (steps 8-10 represent the pre-F1 "outside" writes)
    ax.axvspan(7.5, 10.5, color="#c0392b", alpha=0.12, zorder=0,
               label="Pre-F1 crash window:\ndata + outbox committed, ledger status pending")

    ax.set_xlim(0, 12)
    ax.set_xticks(range(1, 12))
    ax.set_xlabel("Sequence within CriticalOperationService::run()")
    ax.set_title("F1 — moving the ledger-status flip INSIDE DB::transaction\n"
                 "(green tint = inside tx; red tint = pre-F1 gap, now closed)", pad=10)
    ax.invert_yaxis()
    ax.legend(loc="lower right", frameon=True)
    plt.tight_layout()
    plt.savefig(OUT / "f1-commit-path-window.png", dpi=140)
    plt.close(fig)


if __name__ == "__main__":
    chart_issues_by_category()
    chart_test_growth()
    chart_quarantine_schema()
    chart_commit_path_window()
    print("Charts written to:", OUT)
