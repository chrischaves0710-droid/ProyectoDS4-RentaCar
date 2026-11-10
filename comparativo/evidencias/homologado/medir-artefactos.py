"""Suma bytes de archivos regulares de los paquetes de despliegue, sin comprimir."""
import argparse
import csv
from collections import defaultdict
from pathlib import Path

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument("root", type=Path)
parser.add_argument("output", type=Path)
args = parser.parse_args()
totals = defaultdict(lambda: [0, 0])
for framework in ("laravel", "nestjs", "aspnet"):
    root = args.root / framework
    if not root.is_dir():
        raise FileNotFoundError(root)
    for path in root.rglob("*"):
        if not path.is_file() or path.is_symlink():
            continue
        rel = path.relative_to(root)
        first = rel.parts[0]
        if first in {"vendor", "node_modules", "runtimes"}:
            category = "dependencias"
        elif framework == "aspnet" and path.suffix in {".dll", ".exe", ".pdb"}:
            category = "generado propio" if path.stem == "Api" else "dependencias"
        elif framework == "nestjs" and first == "dist":
            category = "generado propio"
        elif framework == "laravel" and first in {"app", "routes", "resources", "database"}:
            category = "fuente propia"
        else:
            category = "configuracion y datos"
        entry = totals[framework, category]
        entry[0] += 1
        entry[1] += path.stat().st_size
with args.output.open("w", encoding="utf-8", newline="") as handle:
    writer = csv.writer(handle)
    writer.writerow(("framework", "categoria", "archivos", "bytes"))
    for key, value in sorted(totals.items()):
        writer.writerow((*key, *value))
    for framework in ("laravel", "nestjs", "aspnet"):
        rows = [row for (name, _), row in totals.items() if name == framework]
        writer.writerow((framework, "total", sum(row[0] for row in rows), sum(row[1] for row in rows)))
