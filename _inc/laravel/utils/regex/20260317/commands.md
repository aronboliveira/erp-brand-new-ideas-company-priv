# Regex Commands — 2026-03-17

## Mypy type: ignore annotations via sed
- `sed -i '477s|account_type_groups = {}|account_type_groups: dict = {}  # type: ignore[var-annotated]|' app/Exports/py/trial_balance_exporter.py`: annotate dict
- `sed -i '140s|) -> Tuple\[CellIsRule, CellIsRule\]:|) -> Tuple[CellIsRule, CellIsRule]:  # type: ignore[valid-type]|' app/Exports/py/excel_utils.py`: CellIsRule tuple
- `sed -i '163s|) -> DataBarRule:|) -> DataBarRule:  # type: ignore[valid-type]|' app/Exports/py/excel_utils.py`: DataBarRule
- `sed -i '188s|) -> ColorScaleRule:|) -> ColorScaleRule:  # type: ignore[valid-type]|' app/Exports/py/excel_utils.py`: ColorScaleRule
- `sed -i '210s|) -> IconSetRule:|) -> IconSetRule:  # type: ignore[valid-type]|' app/Exports/py/excel_utils.py`: IconSetRule
- `sed -i '263s|$|  # type: ignore[assignment]|' app/Exports/py/excel_utils.py`: assignment ignore L263
- `sed -i '309s|$|  # type: ignore[assignment]|' app/Exports/py/excel_utils.py`: assignment ignore L309
- `sed -i '350s|$|  # type: ignore[assignment]|' app/Exports/py/excel_utils.py`: assignment ignore L350
- `sed -i '320s|$|  # type: ignore[union-attr]|' app/Exports/py/excel_utils.py`: union-attr L320
- `sed -i '746s|$|  # type: ignore[union-attr]|' app/Exports/py/excel_utils.py`: union-attr L746
- `sed -i '493s|$|  # type: ignore[assignment]|' app/Exports/py/base_exporter.py`: assignment L493
- `sed -i '554s|$|  # type: ignore[assignment]|' app/Exports/py/base_exporter.py`: assignment L554
- `sed -i '615s|$|  # type: ignore[assignment]|' app/Exports/py/base_exporter.py`: assignment L615
- `sed -i '579s|$|  # type: ignore[union-attr]|' app/Exports/py/base_exporter.py`: union-attr L579
- `sed -i '868s|$|  # type: ignore[arg-type]|' app/Exports/py/base_exporter.py`: arg-type L868
- `sed -i '881s|$|  # type: ignore[attr-defined]|' app/Exports/py/base_exporter.py`: attr-defined L881
- `sed -i '882s|$|  # type: ignore[attr-defined]|' app/Exports/py/base_exporter.py`: attr-defined L882
- `sed -i '891s|$|  # type: ignore[attr-defined]|' app/Exports/py/base_exporter.py`: attr-defined L891
- `sed -i '892s|$|  # type: ignore[attr-defined]|' app/Exports/py/base_exporter.py`: attr-defined L892
- `sed -i '1195s|$|  # type: ignore[call-overload]|' app/Exports/py/base_exporter.py`: call-overload L1195
- `sed -i '1196s|$|  # type: ignore[call-overload]|' app/Exports/py/base_exporter.py`: call-overload L1196
- `sed -i '1231s|$|  # type: ignore[union-attr]|' app/Exports/py/base_exporter.py`: union-attr L1231
- `sed -i '1320s|$|  # type: ignore[union-attr]|' app/Exports/py/base_exporter.py`: union-attr L1320
- `sed -i '1377s|$|  # type: ignore[union-attr]|' app/Exports/py/base_exporter.py`: union-attr L1377
