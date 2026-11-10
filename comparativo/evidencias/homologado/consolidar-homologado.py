"""Consolida únicamente corridas de un mismo commit y fixture."""
import csv
import json
import statistics
from pathlib import Path
import sys

root = Path(sys.argv[1])
frameworks = ("laravel", "nestjs", "aspnet")
summaries = []
identity = set()
for framework in frameworks:
    report = json.loads((root / f"{framework}-mediciones.json").read_text(encoding="utf-8-sig"))
    assert report["framework"] == framework and not report["modified_tracked_files"]
    identity.add((report["commit"], report["fixture_sha256"]))
    runs = [row for row in report["runs"] if row["name"].startswith("principal-")]
    assert len(runs) == 3 and all(row["errors"] == 0 for row in runs)
    summaries.append({
        "framework": framework, "commit": report["commit"], "fixture_sha256": report["fixture_sha256"],
        "runs": len(runs), "requests": sum(row["requests"] for row in runs),
        "success": sum(row["success"] for row in runs),
        "error_rate": 0, "mean_effective_rps": statistics.mean(row["rps"] for row in runs),
        "mean_run_latency_ms": statistics.mean(row["avg_ms"] for row in runs),
        "mean_run_p95_ms": statistics.mean(row["p95_ms"] for row in runs),
        **{key: report[key] for key in ("idle_avg_bytes", "idle_max_bytes", "load_avg_bytes", "load_max_bytes")},
    })
assert len(identity) == 1
with (root / "resumen.csv").open("w", encoding="utf-8", newline="") as handle:
    writer = csv.DictWriter(handle, fieldnames=list(summaries[0]))
    writer.writeheader()
    writer.writerows(summaries)
