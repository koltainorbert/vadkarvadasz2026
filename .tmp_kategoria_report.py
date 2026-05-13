import json
import subprocess
import sys
from pathlib import Path

py = sys.executable
try:
    import openpyxl
except Exception:
    subprocess.check_call([py, "-m", "pip", "install", "openpyxl", "-q"])
    import openpyxl

root = Path.cwd()
kat_dir = root / "Kategoria"
files = sorted(kat_dir.glob("*.xlsx"))
report = []

for fp in files:
    wb = openpyxl.load_workbook(fp, data_only=True, read_only=True)
    file_info = {
        "file": str(fp.relative_to(root)),
        "sheets": []
    }
    for ws in wb.worksheets:
        rows = ws.iter_rows(values_only=True)
        try:
            header_row = next(rows)
        except StopIteration:
            header_row = tuple()
        headers = [h for h in header_row]

        non_empty_count = 0
        samples = []
        for r in rows:
            if any(c is not None and str(c).strip() != "" for c in r):
                non_empty_count += 1
                if len(samples) < 5:
                    samples.append(list(r))

        file_info["sheets"].append({
            "sheet": ws.title,
            "headers": headers,
            "non_empty_rows": non_empty_count,
            "sample_rows": samples
        })
    wb.close()
    report.append(file_info)

print(json.dumps(report, ensure_ascii=False, indent=2, default=str))
