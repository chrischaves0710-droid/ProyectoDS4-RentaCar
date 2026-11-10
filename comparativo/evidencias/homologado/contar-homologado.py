"""Cuenta líneas físicas y no vacías de la porción vertical, sin dependencias."""
import argparse
import csv
from pathlib import Path

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument("repo", type=Path)
parser.add_argument("output", type=Path)
args = parser.parse_args()
repo = args.repo.resolve()
nest = repo / "comparativo/nestjs"
asp = repo / "comparativo/aspnet"
sources = {
    "Laravel": [
        repo / "app/Http/Controllers/Api/ComparativoController.php",
        repo / "config/comparativo.php",
        repo / "routes/api.php",
        repo / "comparativo/evidencias/homologado/preparar-sqlite.py",
    ],
    "NestJS": sorted(p for p in (nest / "src").rglob("*.ts")
                     if not p.name.endswith(".spec.ts") and p.name not in {"app.controller.ts", "app.service.ts"}),
    "ASP.NET Core": sorted(p for p in (asp / "Api").rglob("*.cs")
                            if not {"Migrations", "bin", "obj"}.intersection(p.relative_to(asp).parts)),
}
tests = {
    "Laravel": [repo / "tests/Feature/VehiculoApiTest.php", repo / "tests/Feature/VehiculoServiceTest.php"],
    "NestJS": sorted((nest / "src").rglob("*.spec.ts")) + sorted((nest / "test").glob("*.e2e-spec.ts")),
    "ASP.NET Core": [asp / "Tests/ContractTests.cs"],
    "Común": [repo / "comparativo/evidencias/contrato-http.py"],
}
with args.output.open("w", encoding="utf-8", newline="") as handle:
    writer = csv.writer(handle)
    writer.writerow(("framework", "scope", "path", "physical", "nonblank"))
    for scope, paths in (("source", sources), ("test", tests)):
        for framework, files in paths.items():
            for path in files:
                lines = path.read_text(encoding="utf-8-sig").splitlines()
                if path == repo / "routes/api.php":
                    text = "\n".join(lines)
                    start = text.index("if (app()->environment('benchmark')) {")
                    end = text.index("\n}\n", start)
                    lines = text[start:end + 2].splitlines()
                writer.writerow((framework, scope, path.relative_to(repo).as_posix(),
                                 len(lines), sum(bool(line.strip()) for line in lines)))
